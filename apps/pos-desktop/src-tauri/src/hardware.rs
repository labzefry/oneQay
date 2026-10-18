// Author by Lab | zefry. Windows spooler handles installed USB and paired Bluetooth queues.
use oneqay_pos_core::PrinterSettings;
use std::io::Write;
use std::net::{TcpStream,ToSocketAddrs};
use std::time::Duration;

pub fn send(settings:&PrinterSettings,bytes:&[u8])->Result<(),String>{
 if settings.transport=="network" {
  let addresses=(settings.host.as_str(),settings.port).to_socket_addrs().map_err(|_|"PRINT_ADDRESS_INVALID")?;
  let mut stream=None;for address in addresses.take(4){if let Ok(s)=TcpStream::connect_timeout(&address,Duration::from_secs(3)){stream=Some(s);break;}}
  let mut s=stream.ok_or("PRINT_NETWORK_UNAVAILABLE")?;s.set_write_timeout(Some(Duration::from_secs(5))).map_err(|_|"PRINT_NETWORK_UNAVAILABLE")?;s.write_all(bytes).map_err(|_|"PRINT_NETWORK_WRITE_FAILED".into())
 }else if settings.transport=="windows" {windows::spool(&settings.queue,bytes)}else{Err("PRINT_TRANSPORT_INVALID".into())}
}
pub fn printers()->Result<Vec<String>,String>{windows::printers()}
pub fn protect(bytes:&[u8])->Result<Vec<u8>,String>{windows::protect(bytes,false)}
pub fn unprotect(bytes:&[u8])->Result<Vec<u8>,String>{windows::protect(bytes,true)}

#[cfg(windows)]
mod windows {
 use std::{ffi::c_void,ptr};
 type Handle=*mut c_void;
 #[repr(C)] struct DocInfo {name:*const u16,output:*const u16,datatype:*const u16}
 #[repr(C)] struct PrinterInfo {name:*const u16,server:*const u16,attributes:u32}
 #[repr(C)] struct Blob {len:u32,data:*mut u8}
 #[link(name="winspool.drv")]
 extern "system" {
  fn OpenPrinterW(name:*const u16,handle:*mut Handle,defaults:*const c_void)->i32;
  fn ClosePrinter(handle:Handle)->i32;
  fn StartDocPrinterW(handle:Handle,level:u32,info:*const u8)->u32;
  fn EndDocPrinter(handle:Handle)->i32;
  fn StartPagePrinter(handle:Handle)->i32;
  fn EndPagePrinter(handle:Handle)->i32;
  fn WritePrinter(handle:Handle,buffer:*const c_void,count:u32,written:*mut u32)->i32;
  fn EnumPrintersW(flags:u32,name:*const u16,level:u32,buffer:*mut u8,size:u32,needed:*mut u32,returned:*mut u32)->i32;
 }
 #[link(name="crypt32")]
 extern "system" {
  fn CryptProtectData(input:*const Blob,description:*const u16,entropy:*const Blob,reserved:*const c_void,prompt:*const c_void,flags:u32,output:*mut Blob)->i32;
  fn CryptUnprotectData(input:*const Blob,description:*mut *mut u16,entropy:*const Blob,reserved:*const c_void,prompt:*const c_void,flags:u32,output:*mut Blob)->i32;
 }
 #[link(name="kernel32")]
 extern "system" {fn LocalFree(memory:Handle)->Handle;}
 fn wide(s:&str)->Vec<u16>{s.encode_utf16().chain(Some(0)).collect()}
 struct OwnedPrinter(Handle);impl Drop for OwnedPrinter{fn drop(&mut self){unsafe{ClosePrinter(self.0);}}}
 pub fn spool(queue:&str,bytes:&[u8])->Result<(),String>{
  if queue.is_empty()||queue.contains('\0')||bytes.len()>1024*1024{return Err("PRINT_QUEUE_INVALID".into())}
  let name=wide(queue);let mut handle=ptr::null_mut();
  unsafe{
   if OpenPrinterW(name.as_ptr(),&mut handle,ptr::null())==0{return Err("PRINT_QUEUE_UNAVAILABLE".into())}
   let owned=OwnedPrinter(handle);let title=wide("oneQay POS receipt");let datatype=wide("RAW");let doc=DocInfo{name:title.as_ptr(),output:ptr::null(),datatype:datatype.as_ptr()};
   if StartDocPrinterW(owned.0,1,&doc as*const _ as*const u8)==0{return Err("PRINT_SPOOL_REJECTED".into())}
   let page=StartPagePrinter(owned.0)!=0;let mut offset=0;
   if page {while offset<bytes.len(){let mut written=0;let remaining=&bytes[offset..];if WritePrinter(owned.0,remaining.as_ptr()as*const c_void,remaining.len()as u32,&mut written)==0||written==0{break}offset+=written as usize;}EndPagePrinter(owned.0);}
   let ended=EndDocPrinter(owned.0)!=0;
   if !page||offset!=bytes.len()||!ended{return Err("PRINT_SPOOL_UNCERTAIN_CHECK_PRINTER".into())}
  }Ok(())
 }
 pub fn printers()->Result<Vec<String>,String>{unsafe{
  let mut needed=0;let mut returned=0;EnumPrintersW(6,ptr::null(),4,ptr::null_mut(),0,&mut needed,&mut returned);if needed==0{return Ok(Vec::new())}if needed>4*1024*1024{return Err("PRINT_LIST_TOO_LARGE".into())}
  let mut buffer=vec![0u64;(needed as usize+7)/8];if EnumPrintersW(6,ptr::null(),4,buffer.as_mut_ptr()as*mut u8,needed,&mut needed,&mut returned)==0{return Err("PRINT_LIST_UNAVAILABLE".into())}
  let items=std::slice::from_raw_parts(buffer.as_ptr()as*const PrinterInfo,returned as usize);let mut result=Vec::new();for p in items {if p.name.is_null(){continue}let mut len=0;while len<32768&&*p.name.add(len)!=0{len+=1}result.push(String::from_utf16_lossy(std::slice::from_raw_parts(p.name,len)));}result.sort();Ok(result)
 }}
 pub fn protect(bytes:&[u8],decrypt:bool)->Result<Vec<u8>,String>{unsafe{
  if bytes.len()>1024*1024{return Err("POS_CREDENTIAL_INVALID".into())}let input=Blob{len:bytes.len()as u32,data:bytes.as_ptr()as*mut u8};let entropy_bytes=b"oneQay POS device credential v1";let entropy=Blob{len:entropy_bytes.len()as u32,data:entropy_bytes.as_ptr()as*mut u8};let mut output=Blob{len:0,data:ptr::null_mut()};
  let ok=if decrypt{CryptUnprotectData(&input,ptr::null_mut(),&entropy,ptr::null(),ptr::null(),1,&mut output)}else{CryptProtectData(&input,ptr::null(),&entropy,ptr::null(),ptr::null(),1,&mut output)};
  if ok==0{return Err("POS_WINDOWS_CREDENTIAL_PROTECTION_FAILED".into())}let result=std::slice::from_raw_parts(output.data,output.len as usize).to_vec();LocalFree(output.data as Handle);Ok(result)
 }}
}
#[cfg(not(windows))]
mod windows {
 pub fn spool(_: &str,_:&[u8])->Result<(),String>{Err("PRINT_REQUIRES_WINDOWS".into())}
 pub fn printers()->Result<Vec<String>,String>{Ok(Vec::new())}
 pub fn protect(_: &[u8],_:bool)->Result<Vec<u8>,String>{Err("POS_CREDENTIALS_REQUIRE_WINDOWS_DPAPI".into())}
}
