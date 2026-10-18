const {chromium}=require('playwright');
const http=require('http'),fs=require('fs'),path=require('path');
const base=path.resolve(__dirname,'../dist');const artifacts=path.resolve(__dirname,'../test-results');fs.mkdirSync(artifacts,{recursive:true});
const host=http.createServer((req,res)=>{let filename=path.resolve(base,'.'+new URL(req.url,'http://localhost').pathname);if(filename===base)filename=path.join(base,'index.html');if(!filename.startsWith(base+path.sep)){res.writeHead(403).end();return}try{const data=fs.readFileSync(filename);res.setHeader('Content-Type',filename.endsWith('.js')?'application/javascript':filename.endsWith('.css')?'text/css':'text/html');res.end(data)}catch{res.writeHead(404).end()}});
(async()=>{await new Promise(resolve=>host.listen(4173,'127.0.0.1',resolve));
 const browser=await chromium.launch({headless:true});const page=await browser.newPage({viewport:{width:1440,height:1000},deviceScaleFactor:1});let errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.addInitScript(()=>{
  const names=['Kopi susu gula aren','Americano','Matcha latte','Roti cokelat','Croissant butter','Air mineral','Es cokelat','Donat klasik','Teh melati'];const now=Math.floor(Date.now()/1000);
  const state={snapshot:{scope:{tenant_id:'fixture',identity_id:'cashier',organization_id:'store',outlet_id:'outlet',device_id:'device'},shift_id:'shift',expires_at:now+28000,server_time:now,issued_at:now,business_name:'Kedai Nusantara',currency_symbol:'Rp',can_count:true,catalog_revision:'fixture'},products:names.map((name,i)=>({id:'p'+i,name,price:[22000,18000,26000,12000,18000,6000,24000,10000,8000][i],currency:'IDR',scale:0,stock:20-i,barcode:'89900'+i,category:i<3?'Kopi & minuman':i<6?'Roti & snack':'Minuman'})),operations:[],settings:{transport:'windows',queue:'POS-80 USB',host:'',port:9100,paper_mm:80,drawer_enabled:false,auto_print:false},last_server_at:now,pending:0,review:0,authorized:true,lease_remaining:28000};
  window.__TAURI_INTERNALS__={invoke:async(cmd,args)=>{
   args=args?JSON.parse(JSON.stringify(args)):args;
   if(cmd==='printers')return ['POS-80 USB','Bluetooth Receipt'];
   if(cmd==='synchronize')throw 'POS_NETWORK_UNAVAILABLE';
   if(cmd==='workspace')return structuredClone(state);
   if(cmd==='save_printer'){state.settings=args.settings;return structuredClone(state)}
   if(cmd==='commit'){const lines=args.lines.map(l=>({...l,...state.products.find(p=>p.id===l.product_id)}));const total=lines.reduce((n,l)=>n+l.price*l.quantity,0);state.operations.unshift({operation:{operation_id:args.id,kind:args.kind,created_at:now,lease_id:'lease',lines:args.lines,tendered:args.tendered},state:'pending',total,receipt:{lines:lines.map(l=>({name:l.name,quantity:l.quantity,price:l.price})),total,tendered:args.tendered,change:args.tendered-total,currency:'IDR',scale:0}});state.pending++;return structuredClone(state)}
   if(cmd==='print_receipt'||cmd==='test_printer')return;
   throw 'UNEXPECTED_COMMAND';
  }};
 });
 await page.goto('http://127.0.0.1:4173');await page.getByRole('button',{name:/Kopi susu gula aren/}).click();await page.getByRole('button',{name:/Croissant butter/}).click();await page.screenshot({path:path.join(artifacts,'pos-checkout.png'),fullPage:true});
 await page.getByRole('button',{name:'Bayar tunai'}).click();await page.locator('.cash-input').fill('1.5');if(await page.getByRole('button',{name:'Konfirmasi & simpan'}).isEnabled())throw Error('Fractional IDR accepted');await page.locator('.cash-input').fill('50000');await page.getByRole('button',{name:'Konfirmasi & simpan'}).click();await page.getByRole('heading',{name:'Transaksi tersimpan'}).waitFor();await page.screenshot({path:path.join(artifacts,'pos-receipt.png')});await page.locator('.receipt-modal .modal-close').click();
 await page.getByRole('button',{name:'Perangkat',exact:true}).click();await page.locator('select').first().selectOption('network');await page.getByPlaceholder('192.168.1.100').fill('192.168.1.200');await page.getByRole('button',{name:'Simpan pengaturan'}).click();await page.getByText('Pengaturan perangkat tersimpan.',{exact:true}).waitFor();if(await page.locator('[role=alert]').count())throw Error('Unexpected UI error');await page.screenshot({path:path.join(artifacts,'pos-devices.png'),fullPage:true});
 await page.getByRole('button',{name:'Change language'}).click();await page.getByRole('heading',{name:'A checkout ready to serve.'}).waitFor();
 await page.setViewportSize({width:1024,height:768});await page.getByRole('button',{name:'Checkout',exact:true}).click();const overflow=await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth);if(overflow)throw Error('Minimum window horizontal overflow');
 if(errors.length)throw Error(errors.join('\n'));console.log('DESKTOP_UI_PASS: cart, cash integer validation, provisional receipt, network printer settings, ID/ENG and 1024px viewport');await browser.close();host.close();
})().catch(e=>{console.error(e);process.exit(1)});
