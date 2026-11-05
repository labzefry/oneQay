const {chromium}=require('playwright');
const http=require('http'),fs=require('fs'),path=require('path');
const base=path.resolve(__dirname,'../dist');const artifacts=path.resolve(__dirname,'../test-results');fs.mkdirSync(artifacts,{recursive:true});
const host=http.createServer((req,res)=>{let filename=path.resolve(base,'.'+new URL(req.url,'http://localhost').pathname);if(filename===base)filename=path.join(base,'index.html');if(!filename.startsWith(base+path.sep)){res.writeHead(403).end();return}try{const data=fs.readFileSync(filename);res.setHeader('Content-Type',filename.endsWith('.js')?'application/javascript':filename.endsWith('.css')?'text/css':'text/html');res.end(data)}catch{res.writeHead(404).end()}});
(async()=>{await new Promise(resolve=>host.listen(4173,'127.0.0.1',resolve));
 const browser=await chromium.launch({headless:true});const page=await browser.newPage({viewport:{width:1440,height:1000},deviceScaleFactor:1});let errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.addInitScript(()=>{
  const names=['Kopi susu gula aren','Americano','Matcha latte','Roti cokelat','Croissant butter','Air mineral','Es cokelat','Donat klasik','Teh melati'];const now=Math.floor(Date.now()/1000);
  let signedIn=false,locked=true,autoLock=300;
  const state={shift:null,kitchen_printers:{},kitchen_jobs:[],metrics:{gross:0,count:0,accepted:0,waiting:0,buckets:Array(12).fill(0)},snapshot:{protocol:2,cashier_name:'Maya Putri',settings:{menu_guest_ordering_enabled:true,cashier_membership_enabled:true,auto_lock_seconds:300,show_logo:false,logo:null,footer:'Terima kasih. Sampai jumpa!',kitchens:[{id:'bar',name:'Bar minuman',categories:['Kopi & minuman']}]},scope:{tenant_id:'fixture',identity_id:'cashier',organization_id:'store',outlet_id:'outlet',device_id:'device'},shift_id:'shift',expires_at:now+28000,server_time:now,issued_at:now,business_name:'Kedai Nusantara',currency_symbol:'Rp',can_count:true,catalog_revision:'fixture'},products:names.map((name,i)=>({id:'p'+i,name,price:[22000,18000,26000,12000,18000,6000,24000,10000,8000][i],currency:'IDR',scale:0,stock:20-i,barcode:'89900'+i,category:i<3?'Kopi & minuman':i<6?'Roti & snack':'Minuman'})),operations:[],settings:{transport:'windows',queue:'POS-80 USB',host:'',port:9100,paper_mm:80,drawer_enabled:false,auto_print:false},last_server_at:now,pending:0,review:0,authorized:true,lease_remaining:28000};
  window.__QR_PAYMENT_CALLS=[];
  window.__TAURI_INTERNALS__={invoke:async(cmd,args)=>{
   args=args?JSON.parse(JSON.stringify(args)):args;
   if(cmd==='app_version')return '0.2.3';
   if(cmd==='security_status')return {signed_in:signedIn,locked,login:'maya@example.test',offline:true,updating:false,auto_lock_seconds:autoLock};
   if(cmd==='resume_session')return structuredClone(state);
   if(cmd==='save_security_settings'){autoLock=args.seconds;window.__SAVED_AUTO_LOCK=autoLock;return {signed_in:signedIn,locked,login:'maya@example.test',offline:true,updating:false,auto_lock_seconds:autoLock}}
   if(cmd==='login'){if(args.login!=='maya@example.test'||args.password!=='synthetic-password')throw 'POS_LOGIN_INVALID';signedIn=true;locked=false;return structuredClone(state)}
   if(cmd==='lock_pos'){locked=true;if(args.logout)signedIn=false;return structuredClone(state)}
   if(cmd==='unlock_pos'){if(args.pin!=='123456')throw 'POS_PIN_INVALID';locked=false;return structuredClone(state)}
   if(cmd==='activity')return;
   if(cmd==='check_update')return {available:true,current:'0.2.3',version:'0.2.4',notes:'Synthetic update fixture'};
   if(cmd==='shift_event'){const receipt={opening:state.shift?.opening??args.cash,expected:(state.shift?.opening??0)+(state.metrics.gross??0),counted:args.cash,sales:state.metrics.gross??0,cashier:'Maya Putri',business:'Kedai Nusantara',lines:[],currency:'IDR',scale:0,note:args.note};receipt.variance=args.cash-receipt.expected;state.operations.unshift({operation:{operation_id:args.id,kind:args.opening?'shift_open':'shift_close',created_at:now,lines:[],tendered:args.cash,shift_id:state.shift?.id??args.id},state:'pending',total:0,receipt});state.shift=args.opening?{id:args.id,opening:args.cash,opened_at:now,cashier:'Maya Putri'}:null;state.pending++;return structuredClone(state)}
   if(cmd==='save_kitchen_printers'){state.kitchen_printers=args.settings;return structuredClone(state)}
   if(cmd==='printers')return ['POS-80 USB','Bluetooth Receipt'];
   if(cmd==='synchronize'){if(window.__POS_TEST_NETWORK==='server-error')throw 'POS_SERVER_REQUEST_FAILED · synthetic-correlation';if(window.__POS_TEST_NETWORK!=='online')throw 'POS_NETWORK_UNAVAILABLE';for(const o of state.operations)o.state='accepted';state.pending=0;return structuredClone(state)}
   if(cmd==='menu_order'){
    const order={order_id:'qr-order-1',status:'pending',customer_name:'Synthetic guest',table_label:null,total_atomic:'22000',currency:'IDR',currency_scale:0,created_at:now,lines:[{product_id:'p0',product_name:names[0],quantity:1,unit_price_atomic:'22000',line_total_atomic:'22000'}]};
    if(args.action==='lookup')return {order};
    if(args.action==='quote')return {quote:{...order,previous_total_atomic:'22000'}};
    if(args.action==='member-lookup')return new Promise(resolve=>{window.__FINISH_QR_MEMBER_LOOKUP=()=>resolve({member:{id:'fixture-member',name:'Synthetic member',status:'cashier_enrolled'}})});
    if(args.action==='settle'){
     const calls=window.__QR_PAYMENT_CALLS;calls.push(args.input);
     if(calls.length===1)throw 'POS_NETWORK_RESPONSE_INTERRUPTED';
     if(calls.length===2)throw 'POS_MENU_SHIFT_SYNC_REQUIRED';
     return {settlement:{sale_id:'synthetic-qr-receipt'}};
    }
   }
   if(cmd==='workspace')return structuredClone(state);
   if(cmd==='save_printer'){state.settings=args.settings;return structuredClone(state)}
   if(cmd==='commit'){const lines=args.lines.map(l=>({...l,...state.products.find(p=>p.id===l.product_id)}));const total=lines.reduce((n,l)=>n+l.price*l.quantity,0);state.operations.unshift({operation:{operation_id:args.id,kind:args.kind,created_at:now,lease_id:'lease',lines:args.lines,tendered:args.tendered},state:'pending',total,receipt:{business:'Kedai Nusantara',cashier:'Maya Putri',table:args.table,order:args.order,footer:state.snapshot.settings.footer,lines:lines.map(l=>({name:l.name,quantity:l.quantity,price:l.price})),total,tendered:args.tendered,change:args.tendered-total,currency:'IDR',scale:0}});state.pending++;state.metrics.gross+=total;state.metrics.count++;state.metrics.waiting+=total;state.metrics.buckets[4]+=total;return structuredClone(state)}
   if(cmd==='print_receipt'||cmd==='test_printer')return;
   throw 'UNEXPECTED_COMMAND';
  }};
 });
 await page.goto('http://127.0.0.1:4173');await page.getByRole('heading',{name:'Selamat datang kembali.'}).waitFor();if(await page.locator('input[type=url]').count())throw Error('Server URL input exposed');await page.screenshot({path:path.join(artifacts,'pos-login.png'),fullPage:true});await page.getByLabel('Email kasir').fill('maya@example.test');await page.getByLabel('Password',{exact:true}).fill('synthetic-password');await page.getByRole('button',{name:'Masuk ke kasir'}).click();await page.getByRole('heading',{name:'Berapa kas awal hari ini?'}).waitFor();await page.getByLabel('Kas pembukaan').fill('1.5');if(await page.getByRole('button',{name:'Buka kas & mulai melayani'}).isEnabled())throw Error('Fractional opening cash accepted');await page.getByLabel('Kas pembukaan').fill('100000');await page.screenshot({path:path.join(artifacts,'pos-open-shift.png')});await page.getByRole('button',{name:'Buka kas & mulai melayani'}).click();await page.getByRole('button',{name:/Kopi susu gula aren/}).click();await page.getByRole('button',{name:/Croissant butter/}).click();await page.getByLabel('Nomor meja').fill('04');await page.getByLabel('Nomor order').fill('A-001');await page.screenshot({path:path.join(artifacts,'pos-checkout.png'),fullPage:true});
 await page.getByRole('button',{name:'Bayar tunai'}).click();await page.locator('.cash-input').fill('1.5');if(await page.getByRole('button',{name:'Konfirmasi & simpan'}).isEnabled())throw Error('Fractional IDR accepted');await page.locator('.cash-input').fill('50000');await page.getByRole('button',{name:'Konfirmasi & simpan'}).click();await page.getByRole('heading',{name:'Transaksi tersimpan'}).waitFor();await page.screenshot({path:path.join(artifacts,'pos-receipt.png')});await page.getByText('Maya Putri',{exact:true}).last().waitFor();await page.getByText('Terima kasih. Sampai jumpa!',{exact:true}).waitFor();await page.locator('.receipt-modal .modal-close').click();await page.getByRole('button',{name:'Kunci POS'}).click();await page.getByRole('heading',{name:'Kasir sedang dikunci.'}).waitFor();await page.getByLabel('Masukkan PIN 6 angka').fill('123456');await page.getByRole('button',{name:'Buka kunci',exact:true}).click();await page.locator('.lock-modal').waitFor({state:'hidden'});
 await page.getByRole('button',{name:'Perangkat',exact:true}).click();await page.locator('select').first().selectOption('network');await page.getByPlaceholder('192.168.1.100').fill('192.168.1.200');await page.getByRole('button',{name:'Simpan pengaturan'}).click();await page.getByText('Pengaturan perangkat tersimpan.',{exact:true}).waitFor();if(await page.locator('[role=alert]').count())throw Error('Unexpected UI error');await page.screenshot({path:path.join(artifacts,'pos-devices.png'),fullPage:true});
 await page.getByRole('button',{name:'Kunci & akun',exact:true}).click();
 await page.getByRole('heading',{name:'Akun tetap masuk. Kendali tetap di Anda.'}).waitFor();
 await page.getByLabel('Kunci setelah tidak ada aktivitas').selectOption('0');
 await page.getByRole('button',{name:'Simpan kunci otomatis',exact:true}).click();
 await page.getByText('Pengaturan tersimpan.',{exact:true}).waitFor();
 if(await page.evaluate(()=>window.__SAVED_AUTO_LOCK)!==0)throw Error('Auto lock off was not saved');
 await page.getByRole('button',{name:'Kunci sekarang',exact:true}).click();
 await page.getByRole('heading',{name:'Kasir sedang dikunci.'}).waitFor();
 await page.getByLabel('Masukkan PIN 6 angka').fill('123456');await page.getByRole('button',{name:'Buka kunci',exact:true}).click();
 await page.locator('.lock-modal').waitFor({state:'hidden'});
 await page.getByRole('button',{name:'Ringkasan',exact:true}).click();await page.getByRole('heading',{name:'Setiap transaksi, lebih berarti.'}).waitFor();await page.screenshot({path:path.join(artifacts,'pos-overview.png'),fullPage:true});await page.getByRole('button',{name:'Dapur',exact:true}).click();await page.getByRole('heading',{name:'Setiap dapur punya alurnya.'}).waitFor();await page.screenshot({path:path.join(artifacts,'pos-kitchens.png'),fullPage:true});await page.getByRole('button',{name:'Pembaruan',exact:false}).first().click();await page.getByRole('heading',{name:'Pembaruan siap untuk Anda.'}).waitFor();if(!await page.getByRole('button',{name:'Pasang & mulai ulang'}).isEnabled())throw Error('Repair update blocked by durable shift/queue');await page.getByRole('button',{name:'Perangkat',exact:true}).click();await page.getByRole('button',{name:'Change language'}).click();await page.getByRole('heading',{name:'A checkout ready to serve.'}).waitFor();
 await page.setViewportSize({width:1024,height:768});await page.getByRole('button',{name:'Checkout',exact:true}).click();const overflow=await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth);if(overflow)throw Error('Minimum window horizontal overflow');
 await page.getByRole('button',{name:'Lock POS',exact:true}).click();await page.getByRole('button',{name:'Use another account',exact:true}).click();await page.getByRole('heading',{name:'Welcome back.'}).waitFor();
 if(await page.getByLabel('Cashier email').inputValue()!=='')throw Error('Previous email not cleared on switch');
 await page.getByLabel('Cashier email').fill('another@example.test');if(await page.getByLabel('Cashier email').inputValue()!=='another@example.test')throw Error('Another account email cannot be edited');
 await page.getByLabel('Cashier email').fill('maya@example.test');await page.getByLabel('Password',{exact:true}).fill('synthetic-password');await page.getByRole('button',{name:'Sign in to checkout',exact:true}).click();await page.locator('.login-screen').waitFor({state:'hidden'});
 await page.getByRole('button',{name:'Transactions',exact:true}).click();await page.getByText('Waiting to sync',{exact:true}).first().waitFor();
 await page.evaluate(()=>{window.__POS_TEST_NETWORK='server-error';window.dispatchEvent(new Event('online'))});await page.getByText(/POS_SERVER_REQUEST_FAILED/).first().waitFor();await page.locator('.connection.connected').waitFor();
 await page.evaluate(()=>{window.__POS_TEST_NETWORK='online';window.dispatchEvent(new Event('online'))});await page.getByText('Synced',{exact:true}).first().waitFor();if(await page.getByText(/POS_SERVER_REQUEST_FAILED/).count())throw Error('Stale sync error after accepted reconnect');
 await page.getByRole('button',{name:'QR orders',exact:true}).click();
 const qr=page.locator('.qr-inbox');
 await qr.getByLabel('Scanned QR code').fill('a'.repeat(64));
 await qr.getByLabel('Scanned QR code').press('Enter');
 await qr.getByRole('heading',{name:'Synthetic guest'}).waitFor();
 await qr.getByRole('button',{name:'Recalculate current price and stock'}).click();
 const confirm=qr.getByRole('checkbox');
 const pay=qr.getByRole('button',{name:'Record payment',exact:true});
 await confirm.check();
 if(!await pay.isEnabled())throw Error('Confirmed QR payment must be available before lookup');
 await qr.getByLabel('Member mobile number').fill('+628123456789');
 await qr.locator('.member-panel').getByRole('button',{name:'Search',exact:true}).click();
 await qr.getByText('Wait for the member check before taking payment.',{exact:true}).waitFor();
 if(await pay.isEnabled()||await confirm.isEnabled())throw Error('Member lookup must prevent settlement');
 if(await page.evaluate(()=>window.__QR_PAYMENT_CALLS.length))throw Error('Payment started before member lookup completed');
 await page.evaluate(()=>window.__FINISH_QR_MEMBER_LOOKUP());
 await qr.getByText('Synthetic member',{exact:true}).waitFor();
 if(await confirm.isChecked())throw Error('New member selection must require reconfirmation');
 await confirm.check();await pay.click();
 const retry=qr.getByRole('button',{name:'Retry payment confirmation',exact:true});
 await retry.waitFor();await retry.click();
 await qr.getByRole('alert').filter({hasText:'POS_MENU_SHIFT_SYNC_REQUIRED'}).waitFor();
 if(!await retry.isEnabled())throw Error('A local retry rejection discarded the unresolved payment');
 await retry.click();
 await qr.getByText(/synthetic-qr-receipt/).waitFor();
 const calls=await page.evaluate(()=>window.__QR_PAYMENT_CALLS);
 if(calls.length!==3||calls[0].member_id!=='fixture-member'||!calls.every(c=>JSON.stringify(c)===JSON.stringify(calls[0])))throw Error('Retry mutated member, operation ID or payment payload');
 if(errors.length)throw Error(errors.join('\n'));console.log('DESKTOP_UI_PASS: editable account switch, QR member lookup gate, exact payment replay, preserved pending receipt, reconnect sync, server errors, cashier login, mandatory opening, PIN lock, dashboards, kitchen routing and updater gate; cart, cash integer validation, provisional receipt, network printer settings, ID/ENG and 1024px viewport');await browser.close();host.close();
})().catch(e=>{console.error(e);process.exit(1)});
