<?php
// Author by Lab | zefry. No real email is sent; exercises the CVE-2026-45070 fix.
declare(strict_types=1);
require __DIR__.'/../vendor/autoload.php';
use Symfony\Component\Mime\Header\{ParameterizedHeader,Headers};
use Symfony\Component\Mime\Exception\RfcComplianceException;
$n=0;
foreach(["file\r\nX-Injected: true",'file name','file:name','file;name',"file\0name",'file(name)']as$name){
 foreach(['constructor','single','bulk','collection']as$entry){
  try {
   if($entry==='constructor')new ParameterizedHeader('Content-Disposition','attachment',[$name=>'synthetic.pdf']);
   elseif($entry==='collection')(new Headers())->addParameterizedHeader('Content-Disposition','attachment',[$name=>'synthetic.pdf']);
   else{$header=new ParameterizedHeader('Content-Disposition','attachment',['filename'=>'synthetic.pdf']);if($entry==='single')$header->setParameter($name,'synthetic');else$header->setParameters([$name=>'synthetic']);}
  }catch(RfcComplianceException){$n++;continue;}
  throw new RuntimeException('MIME_PARAMETER_NAME_INJECTION_ACCEPTED');
 }
}
$valid=new ParameterizedHeader('Content-Disposition','attachment',['filename'=>'receipt-123.pdf']);
if($valid->getParameter('filename')!=='receipt-123.pdf')throw new RuntimeException('VALID_FILENAME_REJECTED');$n++;
$email=(new Symfony\Component\Mime\Email())->from('sender@example.test')->to('recipient@example.test')->subject('Synthetic receipt')->text('Synthetic report')->attach('Synthetic PDF bytes','receipt.pdf','application/pdf');
$encoded=$email->toString();
if(!str_contains($encoded,'application/pdf')||!str_contains($encoded,'receipt.pdf'))throw new RuntimeException('VALID_ATTACHMENT_REGRESSION');$n++;
echo 'MIME_HEADER_SECURITY_PASS assertions='.$n.PHP_EOL;
