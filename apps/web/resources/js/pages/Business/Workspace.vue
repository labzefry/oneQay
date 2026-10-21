<script setup lang="ts">
// Author by Lab | zefry. Business data stays server-authorized; no browser tenant overrides.
import { computed, nextTick, onBeforeUnmount, reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { locale, t } from '../../i18n';
import WorkspaceIcon from '../../components/WorkspaceIcon.vue';
import NumericInput from '../../components/NumericInput.vue';
import OperationFeedback from '../../components/OperationFeedback.vue';
import type { MutationStatus } from '../../use-business-mutation';
import { formatMoney, currencyPreferences } from '../../business-money';
type Row = { id: string; version: number; [key: string]: any };
type Product = { product_id: string; display_name: string; currency: string; currency_scale: number };
type Page = { data: Row[]; page: number; has_more: boolean };
type Field = { key: string; label: string; type?: string; required?: boolean; options?: { value: string; label: string }[] };
const props = defineProps<{ module: string; title: string; records: Page; actions: string[]; modules: {key: string; title: string; url: string}[]; search: string; products?: {data:Product[];page:number;has_more:boolean}; suppliers?: Row[]; partners?: Row[]; receipts?: Page; interactions?: Page; journals?: Page; attendance?: Page; leave?: Page; balances?: Row[]; accounts?: Record<string, [string,string]>; from?: string; to?: string; jobs?: {name:string;label_id:string;label_en:string;description:string}[]; staff_accounts?: Record<string,{login:string;enabled:boolean;version:number}>; correlation_id: string }>();
const tr=(id:string,en:string)=>locale.value==='id'?id:en;
const posAccess=ref(false);const posAccount=reactive({login:'',password:'',pin:'',version:0,enabled:true});
const search = ref(props.search); const reportFrom = ref(props.from ?? ''); const reportTo = ref(props.to ?? '');
const selected = ref<Row | null>(null); const dialog = ref<HTMLDialogElement | null>(null); const form = reactive<Record<string, any>>({});
const mode = ref(''); const fields = ref<Field[]>([]); const lines = ref<Record<string, any>[]>([]);
const busy = ref(false); const uncertain = ref(false); const error = ref(''); const saved = ref(false);
const pending = ref<{ operation_id: string; data: Record<string, any> } | null>(null);
const operationProgress = ref<MutationStatus>('idle');
const operationId = computed(() => pending.value?.operation_id ?? null);
let writeController: AbortController | null = null;
onBeforeUnmount(() => writeController?.abort());
const optionQuery = ref(''); const optionPage = ref(1); const optionMore = ref(false); const optionLoading = ref(false);
const products = ref<Product[]>(props.products?.data ?? []); const productQuery = ref(''); const productPage = ref(1); const productMore = ref(props.products?.has_more ?? false); const productLoading = ref(false);
const choices = ref<Row[]>(props.suppliers ?? props.partners ?? []);
const can = (action: string): boolean => props.actions.includes(action);
const today = () => new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Jakarta', year: 'numeric', month: '2-digit', day: '2-digit' }).format(new Date());
const recordName = (row: Row): string => String(row.name ?? row.reference ?? row.description ?? row.action ?? row.id);
function money(atomic: unknown, scale = 0, currency = 'IDR'): string {
    return formatMoney(atomic, currency, scale);
}
function decimal(atomic: unknown, scale = 0): string { const v = String(atomic ?? '0').padStart(scale + 1, '0'); return scale ? `${v.slice(0,-scale)}.${v.slice(-scale)}` : v; }
function atomic(value: unknown, scale: number): string {
    const s = String(value).trim().replace(',', '.');
    if (!/^(0|[1-9]\d*)(\.\d+)?$/.test(s)) throw new Error(t('Enter an amount without thousands separators.'));
    const [whole, fraction = ''] = s.split('.'); if (fraction.length > scale) throw new Error(t('Too many decimal places for this currency.'));
    const result = BigInt(whole + fraction.padEnd(scale, '0'));
    if (result > 9000000000000000n) throw new Error(t('Amount is too large.'));
    return result.toString();
}
function navigate(page = 1): void { router.get(`/pos/business/${props.module}`, { q: search.value, page, ...(props.module === 'finance' ? { from: reportFrom.value, to: reportTo.value } : {}) }, { preserveScroll: true }); }
const exportUrl = computed(() => `/pos/business/${props.module}/export?${new URLSearchParams({q: props.search, page: String(props.records.page), ...(props.module === 'finance' ? { from: props.from ?? '', to: props.to ?? '' } : {}) })}`);
const columns = computed(() => ({ suppliers: ['code','name','email','phone','active'], customers: ['code','name','email','marketing_consent','active'], purchases: ['reference','status','total_atomic'], finance: ['reference','kind','due_date','total_atomic','paid_atomic','status'], people: ['code','name','department','job_title','active'], audit: ['action','created_by','created_at_unix','correlation_id'] }[props.module] ?? ['id']));
const columnLabels: Record<string,string> = { code:'Code', name:'Name', email:'Email', phone:'Phone', active:'Active', marketing_consent:'Marketing consent', reference:'Reference', status:'Status', total_atomic:'Total', paid_atomic:'Paid', kind:'Type', due_date:'Due date', department:'Department', job_title:'Job title', action:'Activity', created_by:'Recorded by', created_at_unix:'Recorded at', correlation_id:'Support reference' };
function cell(row: Row, key: string): string {
    if (key.endsWith('_atomic')) return money(row[key], row.scale, row.currency);
    if (typeof row[key] === 'boolean') return t(row[key] ? 'Yes' : 'No');
    if (key.endsWith('_unix')) return new Intl.DateTimeFormat(locale.value === 'id' ? 'id-ID' : 'en-GB', {dateStyle:'medium',timeStyle:'short',timeZone:'Asia/Jakarta'}).format(new Date(row[key] * 1000));
    return t(String(row[key] ?? '—'));
}
const input = (key: string, label: string, type = 'text', required = true): Field => ({ key, label, type: key === 'amount' ? 'numeric' : type, required });
const select = (key: string, label: string, options: {value:string;label:string}[]): Field => ({key,label,type:'select',options,required:true});
const selectValues = (values: string[]) => values.map(value => ({value,label:value}));
function open(action: string, row?: Row): void {
    if (busy.value || uncertain.value || !can(action)) return; mode.value = action; selected.value = row ?? null; error.value = ''; saved.value = false; uncertain.value = false; pending.value = null;
    Object.keys(form).forEach(k => delete form[k]); fields.value = []; lines.value = []; optionQuery.value = ''; optionPage.value = 1;
    if (action === 'supplier.save' || action === 'customer.save') {
        fields.value = [input('code','Code'),input('name','Name'),input('email','Email','email',false),input('phone','Phone','tel',false),input('address','Address','textarea',false),input('active','Active','checkbox')];
        if (action === 'customer.save') fields.value.push(input('marketing_consent','Marketing consent','checkbox'),input('consent_source','Consent source','text',false));
        Object.assign(form,{code:'',name:'',email:'',phone:'',address:'',active:true,marketing_consent:false,consent_source:''});
        if (row) for (const key of Object.keys(form)) form[key] = row[key];
    } else if (action === 'purchase.create') {
        fields.value = [select('supplier_id','Supplier',choices.value.filter(p=>p.kind==='SUPPLIER').map(p=>({value:p.id,label:`${p.code} · ${p.name}`}))),input('reference','Order reference'),input('currency','Currency'),select('scale','Decimal places',selectValues(['0','2','3','4','5','6'])),input('note','Notes','textarea',false)];
        Object.assign(form,{supplier_id:'',reference:'',currency:currencyPreferences.value.currency,scale:String(currencyPreferences.value.scale),note:''}); lines.value=[{product_id:'',quantity:'1',unit_cost:'0'}];
    } else if (action === 'purchase.receive' && row) {
        fields.value = [input('reference','Delivery reference'),input('received_date','Received date','date')];
        Object.assign(form,{reference:'',received_date:today()}); lines.value=row.lines.filter((l:any)=>l.quantity>l.received).map((l:any)=>({product_id:l.product_id,name:l.name,quantity:String(l.quantity-l.received),remaining:l.quantity-l.received}));
    } else if (action === 'customer.interact' && row) {
        fields.value=[select('channel','Channel',selectValues(['IN_PERSON','PHONE','EMAIL','CHAT','NOTE'])),input('note','Conversation notes','textarea'),input('follow_up_date','Follow-up date','date',false)]; Object.assign(form,{channel:'NOTE',note:'',follow_up_date:''});
    } else if (action === 'invoice.create') {
        fields.value=[select('kind','Invoice type',selectValues(['AP','AR'])),select('partner_id','Supplier or customer',choices.value.map(p=>({value:p.id,label:`${p.code} · ${p.name}`}))),input('receipt_id','Goods receipt ID (payables)','text',false),input('reference','Invoice number'),input('issue_date','Issue date','date'),input('due_date','Due date','date'),input('currency','Currency'),select('scale','Decimal places',selectValues(['0','2','3','4','5','6'])),input('amount','Invoice amount'),input('description','Description','textarea')];
        Object.assign(form,{kind:'AR',partner_id:'',receipt_id:'',reference:'',issue_date:today(),due_date:today(),currency:currencyPreferences.value.currency,scale:String(currencyPreferences.value.scale),amount:'0',description:''});
    } else if (action === 'invoice.settle' && row) {
        fields.value=[input('amount','Amount received or paid'),input('paid_date','Payment date','date'),input('reference','Payment evidence reference'),select('cash_account','Payment account',[{value:'1000',label:'Cash'},{value:'1010',label:'Bank'}])];
        Object.assign(form,{amount:decimal(BigInt(row.total_atomic)-BigInt(row.paid_atomic),row.scale),paid_date:today(),reference:'',cash_account:'1000'});
    } else if (action === 'journal.post') {
        fields.value=[input('posting_date','Posting date','date'),input('description','Description'),input('currency','Currency'),select('scale','Decimal places',selectValues(['0','2','3','4','5','6']))];
        Object.assign(form,{posting_date:today(),description:'',currency:currencyPreferences.value.currency,scale:String(currencyPreferences.value.scale)}); lines.value=[{account:'1000',debit:'0',credit:'0'},{account:'3000',debit:'0',credit:'0'}];
    } else if (action === 'journal.reverse') {
        fields.value=[input('posting_date','Reversal date','date'),input('reason','Reason','textarea')]; Object.assign(form,{posting_date:today(),reason:''});
    } else if(action==='job.save'){fields.value=[input('name',tr('Nama jabatan','Job title')),input('description',tr('Tugas dan tanggung jawab','Responsibilities'),'textarea')];Object.assign(form,{name:'',description:''});
    } else if (action === 'employee.save') {
        fields.value=[input('code','Employee code'),input('name','Name'),input('department','Department'),select('job_title','Job title',[...(props.jobs??[]).map(j=>({value:j.name,label:locale.value==='id'?j.label_id:j.label_en})),...(row?.job_title&&!(props.jobs??[]).some(j=>j.name===row.job_title)?[{value:row.job_title,label:row.job_title}]:[])]),input('start_date','Start date','date'),input('active','Active','checkbox')]; Object.assign(form,{code:'',name:'',department:'',job_title:'Cashier',start_date:today(),active:true});
        const account=row?props.staff_accounts?.[row.id]:undefined;posAccess.value=account?.enabled??false;Object.assign(posAccount,{login:account?.login??'',version:account?.version??0,password:'',pin:'',enabled:true});
        if(row) for(const key of Object.keys(form)) form[key]=row[key];
    } else if (action === 'attendance.record' && row) {
        fields.value=[input('work_date','Work date','date'),input('clock_in','Clock in (WIB)','datetime-local'),input('clock_out','Clock out (WIB)','datetime-local',false),input('note','Attendance notes','textarea')]; Object.assign(form,{work_date:today(),clock_in:`${today()}T08:00`,clock_out:'',note:''});
        if(row.employee_id) Object.assign(form,{work_date:row.work_date,clock_in:localTime(row.clock_in),clock_out:row.clock_out ? localTime(row.clock_out):'',note:row.note});
    } else if (action === 'leave.request' && row) {
        fields.value=[input('start_date','First day','date'),input('end_date','Last day','date'),input('reason','Reason','textarea')]; Object.assign(form,{start_date:today(),end_date:today(),reason:''});
    }
    nextTick(()=>dialog.value?.showModal());
}
function localTime(epoch: number): string { return new Date(epoch*1000+7*3600000).toISOString().slice(0,16); }
function close(): void { if(busy.value||uncertain.value)return; dialog.value?.close(); mode.value=''; }
const titles: Record<string,string> = {'supplier.save':'Save supplier','customer.save':'Save customer','purchase.create':'Create purchase order','purchase.submit':'Submit for approval','purchase.approve':'Approve purchase order','purchase.reject':'Reject purchase order','purchase.cancel':'Cancel purchase order','purchase.receive':'Receive goods','customer.interact':'Record interaction','invoice.create':'Create invoice','invoice.settle':'Record payment','journal.post':'Post journal','journal.reverse':'Reverse journal','job.save':'Tambah jabatan / Add job title','employee.save':'Save employee','attendance.record':'Record attendance','leave.request':'Request leave','leave.approve':'Approve leave','leave.reject':'Reject leave'};
function payload(): Record<string,any> {
    const data: Record<string,any> = { ...form };if(mode.value==='employee.save'&&can('job.save')&&(posAccess.value||posAccount.version>0))data.pos_account={...posAccount,enabled:posAccess.value}; const row=selected.value; const scale=Number(form.scale ?? row?.scale ?? 0);
    if(['supplier.save','customer.save','employee.save'].includes(mode.value) && row) Object.assign(data,{id:row.id,version:row.version});
    if(mode.value==='purchase.create') { data.scale=scale; data.lines=lines.value.map(l=>({product_id:l.product_id,quantity:l.quantity,unit_cost_atomic:atomic(l.unit_cost,scale)})); }
    else if(mode.value==='purchase.receive'&&row) Object.assign(data,{id:row.id,version:row.version,lines:lines.value.filter(l=>String(l.quantity)!=='0').map(l=>({product_id:l.product_id,quantity:l.quantity}))});
    else if(mode.value==='customer.interact'&&row) data.customer_id=row.id;
    else if(mode.value==='invoice.create') {data.scale=scale;data.total_atomic=atomic(data.amount,scale);delete data.amount;}
    else if(mode.value==='invoice.settle'&&row) {Object.assign(data,{id:row.id,version:row.version,amount_atomic:atomic(data.amount,scale)});delete data.amount;}
    else if(mode.value==='journal.post') {data.scale=scale;data.lines=lines.value.map(l=>({account:l.account,debit_atomic:atomic(l.debit,scale),credit_atomic:atomic(l.credit,scale)}));}
    else if(mode.value==='journal.reverse'&&row) data.id=row.id;
    else if(mode.value==='attendance.record'&&row) {
        data.employee_id=row.employee_id??row.id;data.clock_in=Math.floor(new Date(`${data.clock_in}:00+07:00`).getTime()/1000);data.clock_out=data.clock_out?Math.floor(new Date(`${data.clock_out}:00+07:00`).getTime()/1000):'';
        if(row.employee_id)Object.assign(data,{id:row.id,version:row.version});
    } else if(mode.value==='leave.request'&&row)data.employee_id=row.id;
    else if((mode.value.startsWith('purchase.')||mode.value.startsWith('leave.'))&&row)Object.assign(data,{id:row.id,version:row.version});
    return data;
}
const errorMessages: Record<string,string>={BUSINESS_PERMISSION_DENIED:'Your role does not allow this action.',RECORD_CHANGED:'This record changed. Refresh and review the latest version.',OPERATION_CONFLICT:'This request identifier was already used with different data.',PRODUCT_NOT_READY:'The product must be active and have opening stock configured.',CURRENCY_MISMATCH:'The purchase currency must match the selected products.',QUANTITY_EXCEEDS_ORDER:'The received quantity exceeds the outstanding order.',PURCHASE_NOT_APPROVED:'Approve this purchase order before receiving goods.',INVALID_PURCHASE_STATE:'This action is not available for the current order status.',DUPLICATE_OR_INVALID_REFERENCE:'The code or invoice reference already exists, or its related record is invalid.',INVOICE_RECEIPT_MISMATCH:'The supplier, amount and currency must match the goods receipt.',PAYMENT_EXCEEDS_BALANCE:'The payment exceeds the unpaid invoice balance.',UNBALANCED_JOURNAL:'Journal debits and credits must be equal.',LEAVE_OVERLAP:'This employee already has leave in the selected period.',INVALID_ATTENDANCE_TIME:'Check the work date and attendance times.',INVALID_DUE_DATE:'The due date must be on or after the invoice date.',STOCK_RECEIPT_REJECTED:'Stock could not be received. Check the product and opening stock.',BUSINESS_STORAGE_UNAVAILABLE:'Business storage is not available. No success has been confirmed.'};
async function submit(): Promise<void> {
    if (busy.value) return;
    error.value = '';
    try { if (!pending.value) pending.value = { operation_id: `business:${crypto.randomUUID()}`, data: payload() }; }
    catch (e) { operationProgress.value = 'failed'; error.value = e instanceof Error ? e.message : t('Check the entered values.'); return; }
    busy.value = true; operationProgress.value = 'running';
    const controller = new AbortController(); writeController = controller;
    const timer = window.setTimeout(() => controller.abort(), 30000);
    try {
        const csrf = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
        if (!csrf) throw new Error('SESSION_UNVERIFIED');
        if (!navigator.onLine) throw new Error('OFFLINE_UNCONFIRMED');
        const response = await fetch(`/pos/business/commands/${mode.value}`, {
            method: 'POST', credentials: 'same-origin', redirect: 'error', signal: controller.signal,
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(pending.value),
        });
        if (controller.signal.aborted) throw new Error('TIMEOUT_UNCONFIRMED');
        if (response.status === 401 || response.status === 419) {
            operationProgress.value = 'session-expired';
            uncertain.value = true;
            window.location.assign('/business-login?reason=session-expired');
            return;
        }
        if (response.status === 202) {
            uncertain.value = true; operationProgress.value = 'queued';
            error.value = t('Business command accepted into a queue; completion is not confirmed. Verify server state before retrying.');
            return;
        }
        if (response.status >= 500) throw new Error('WRITE_UNCONFIRMED');
        const result = await response.json();
        if (controller.signal.aborted) throw new Error('LATE_ACKNOWLEDGEMENT');
        if (!response.ok) {
            uncertain.value = false; pending.value = null; operationProgress.value = 'failed';
            const code = result?.error?.code ?? '';
            error.value = t(errorMessages[code] ?? 'Check the entered values and your access, then try again.');
            return;
        }
        if (result?.status === 'queued') {
            uncertain.value = true; operationProgress.value = 'queued';
            error.value = t('Business command is queued; completion is not confirmed. Verify server status.');
            return;
        }
        uncertain.value = false; pending.value = null; saved.value = true; operationProgress.value = 'succeeded';
        dialog.value?.close(); mode.value = ''; router.reload();
    } catch {
        uncertain.value = true; operationProgress.value = 'unknown';
        error.value = t('The result is not confirmed. Verify server records before manually retrying the same request.');
    } finally {
        window.clearTimeout(timer); if (writeController === controller) writeController = null;
        busy.value = false;
    }
}
async function findChoices(next=false): Promise<void> {
    const kind=props.module==='purchases'?'suppliers':'partners';optionLoading.value=true;
    try {const p=next?optionPage.value+1:1;const response=await fetch(`/pos/business/options/${kind}?${new URLSearchParams({q:optionQuery.value,page:String(p)})}`,{credentials:'same-origin',headers:{Accept:'application/json'},cache:'no-store',redirect:'error'});if(!response.ok)throw new Error();const result=await response.json();choices.value=result.data;optionPage.value=p;optionMore.value=result.has_more;const field=fields.value.find(f=>f.key==='supplier_id'||f.key==='partner_id');if(field)field.options=choices.value.map(r=>({value:r.id,label:`${r.code} · ${r.name}`}));}
    catch{error.value=t('Could not load records. Try again.');}finally{optionLoading.value=false;}
}
async function findProducts(next=false): Promise<void> {
    productLoading.value=true;
    try {
        const page=next?productPage.value+1:1;
        const response=await fetch(`/pos/business/options/products?${new URLSearchParams({q:productQuery.value,page:String(page)})}`,{credentials:'same-origin',headers:{Accept:'application/json'},cache:'no-store',redirect:'error'});
        if(!response.ok)throw new Error();const result=await response.json();
        const selectedProducts=products.value.filter(product=>lines.value.some(line=>line.product_id===product.product_id));
        products.value=Array.from(new Map([...selectedProducts,...result.data].map(product=>[product.product_id,product])).values());
        productPage.value=page;productMore.value=result.has_more;
    } catch{error.value=t('Could not load records. Try again.');}finally{productLoading.value=false;}
}
function beforeUnload(e:BeforeUnloadEvent):void {if(mode.value){e.preventDefault();e.returnValue='';}}
window.addEventListener('beforeunload',beforeUnload);onBeforeUnmount(()=>window.removeEventListener('beforeunload',beforeUnload));
</script>

<template>
  <main class="biz" :aria-label="t(title)"><OperationFeedback :status="operationProgress" :message="error" :operation-id="operationId"/>
    <header class="biz-heading"><div><p class="eyebrow">oneQay · {{ t('Business management') }}</p><h1>{{ t(title) }}</h1><p>{{ t('Connected records for the business you run every day.') }}</p></div><a class="secondary" href="/pos"><WorkspaceIcon name="layout-dashboard" :size="18" />{{ t('Business Command Center') }}</a></header>
    <nav class="biz-tabs" :aria-label="t('Business modules')"><a v-for="item in modules" :key="item.key" :href="item.url" :aria-current="item.key === `business_${module}` ? 'page' : undefined">{{ t(item.title) }}</a></nav>
    <p v-if="saved" role="status" class="success">{{ t('Saved successfully.') }}</p>
    <section class="biz-panel">
      <div class="toolbar"><form class="search" @submit.prevent="navigate()"><label class="sr-only" for="business-search">{{ t('Search records') }}</label><input id="business-search" v-model="search" :placeholder="t('Search records')" maxlength="120" /><button class="secondary">{{ t('Search') }}</button></form>
        <div class="toolbar-actions"><a class="secondary" :href="exportUrl+'&format=pdf'">{{ t('Download PDF') }}</a><a class="secondary" :href="exportUrl+'&format=xlsx'">{{ t('Download Excel') }}</a><a class="secondary" :href="exportUrl">{{ t('Export this page') }}</a>
          <button v-if="module==='suppliers'&&can('supplier.save')" @click="open('supplier.save')">+ {{ t('Add supplier') }}</button>
          <button v-if="module==='customers'&&can('customer.save')" @click="open('customer.save')">+ {{ t('Add customer') }}</button>
          <button v-if="module==='purchases'&&can('purchase.create')" @click="open('purchase.create')">+ {{ t('Purchase order') }}</button>
          <button v-if="module==='finance'&&can('invoice.create')" @click="open('invoice.create')">+ {{ t('Invoice') }}</button>
          <button v-if="module==='finance'&&can('journal.post')" class="secondary" @click="open('journal.post')">{{ t('Post journal') }}</button>
          <button v-if="module==='people'&&can('job.save')" class="secondary" @click="open('job.save')"><WorkspaceIcon name="users"/>{{tr('Tambah jabatan','Add job title')}}</button><a v-if="module==='people'&&can('job.save')" class="secondary" href="/pos/business/desktop">{{tr('Konfigurasi POS','POS settings')}}</a>
          <button v-if="module==='people'&&can('employee.save')" @click="open('employee.save')">+ {{ t('Add employee') }}</button>
        </div>
      </div>
      <div v-if="records.data.length===0" class="empty"><WorkspaceIcon name="clipboard-list" :size="36"/><h2>{{ t('No records yet') }}</h2><p>{{ t('Create your first record, or change the search to see existing records.') }}</p></div>
      <div v-else class="table-wrap"><table><thead><tr><th v-for="col in columns" :key="col">{{ t(columnLabels[col]??col) }}</th><th v-if="module!=='audit'">{{ t('Actions') }}</th></tr></thead><tbody><tr v-for="row in records.data" :key="row.id"><td v-for="col in columns" :key="col"><span :class="{badge:col==='status',amount:col.endsWith('_atomic')}" :title="cell(row,col)">{{ cell(row,col) }}</span></td><td v-if="module!=='audit'"><div class="row-actions">
        <template v-if="module==='suppliers'"><button v-if="can('supplier.save')" class="text-button" @click="open('supplier.save',row)">{{ t('Edit') }}</button></template>
        <template v-if="module==='customers'"><button v-if="can('customer.save')" class="text-button" @click="open('customer.save',row)">{{ t('Edit') }}</button><button v-if="can('customer.interact')&&row.active" class="text-button" @click="open('customer.interact',row)">{{ t('Record interaction') }}</button></template>
        <template v-if="module==='purchases'"><button class="text-button" @click="selected=selected?.id===row.id?null:row">{{ t('Details') }}</button><button v-if="row.status==='DRAFT'&&can('purchase.submit')" class="text-button" @click="open('purchase.submit',row)">{{ t('Submit for approval') }}</button><button v-if="row.status==='SUBMITTED'&&can('purchase.approve')" class="text-button" @click="open('purchase.approve',row)">{{ t('Approve') }}</button><button v-if="row.status==='SUBMITTED'&&can('purchase.reject')" class="text-button danger" @click="open('purchase.reject',row)">{{ t('Reject') }}</button><button v-if="['APPROVED','PARTIAL'].includes(row.status)&&can('purchase.receive')" class="text-button" @click="open('purchase.receive',row)">{{ t('Receive goods') }}</button><button v-if="['DRAFT','SUBMITTED','APPROVED'].includes(row.status)&&can('purchase.cancel')" class="text-button danger" @click="open('purchase.cancel',row)">{{ t('Cancel order') }}</button></template>
        <template v-if="module==='finance'"><button v-if="row.status!=='PAID'&&can('invoice.settle')" class="text-button" @click="open('invoice.settle',row)">{{ t('Record payment') }}</button><span v-else>{{ t(row.status) }}</span></template>
        <template v-if="module==='people'"><button v-if="can('employee.save')" class="text-button" @click="open('employee.save',row)">{{ t('Edit') }}</button><button v-if="can('attendance.record')&&row.active" class="text-button" @click="open('attendance.record',row)">{{ t('Attendance') }}</button><button v-if="can('leave.request')&&row.active" class="text-button" @click="open('leave.request',row)">{{ t('Leave') }}</button></template>
      </div></td></tr></tbody></table></div>
      <footer class="pagination"><span>{{ t('Page') }} {{ records.page }} · {{ t('Up to 50 records per page') }}</span><div><button class="secondary" :disabled="records.page<=1" @click="navigate(records.page-1)">{{ t('Previous') }}</button><button class="secondary" :disabled="!records.has_more" @click="navigate(records.page+1)">{{ t('Next') }}</button></div></footer>
    </section>
    <section v-if="module==='purchases'&&selected?.lines&&!mode" class="biz-panel detail"><h2>{{ selected.reference }}</h2><p>{{ t(selected.status) }} · {{ money(selected.total_atomic,selected.scale,selected.currency) }}</p><div class="table-wrap"><table><thead><tr><th>{{ t('Product') }}</th><th>{{ t('Ordered') }}</th><th>{{ t('Received') }}</th><th>{{ t('Unit cost') }}</th></tr></thead><tbody><tr v-for="line in selected.lines" :key="line.product_id"><td>{{ line.name }} <small>{{ line.product_id }}</small></td><td>{{ line.quantity }}</td><td>{{ line.received }}</td><td>{{ money(line.unit_cost_atomic,selected.scale,selected.currency) }}</td></tr></tbody></table></div><p>{{ selected.note }}</p></section>
    <section v-if="module==='finance'" class="biz-panel detail"><div class="section-title"><div><h2>{{ t('Trial balance') }}</h2><p>{{ t('Posted business journals in the selected period. POS transactions are not automatically included.') }}</p></div><form class="period" @submit.prevent="navigate()"><label>{{ t('From') }}<input v-model="reportFrom" type="date" required/></label><label>{{ t('To') }}<input v-model="reportTo" type="date" required/></label><button>{{ t('Apply') }}</button></form></div><div class="table-wrap"><table><thead><tr><th>{{ t('Account') }}</th><th>{{ t('Debit') }}</th><th>{{ t('Credit') }}</th></tr></thead><tbody><tr v-for="row in balances" :key="`${row.currency}-${row.scale}-${row.account}`"><td>{{ row.account }} · {{ t(accounts?.[row.account]?.[0]??'') }}</td><td class="amount">{{ money(row.debit_atomic,row.scale,row.currency) }}</td><td class="amount">{{ money(row.credit_atomic,row.scale,row.currency) }}</td></tr><tr v-if="!balances?.length"><td colspan="3">{{ t('No posted journals in this period.') }}</td></tr></tbody></table></div></section>
    <section v-if="journals" class="biz-panel detail"><h2>{{ t('Journal history') }}</h2><div v-for="row in journals.data" :key="row.id" class="history-row"><div><strong>{{ row.description }}</strong><small>{{ row.posting_date }} · {{ money(row.total_atomic,row.scale,row.currency) }}</small><details><summary>{{ t('Journal lines') }}</summary><p v-for="(line,index) in row.lines" :key="index">{{ line.account }} · {{ t('Debit') }} {{ money(line.debit_atomic,row.scale,row.currency) }} · {{ t('Credit') }} {{ money(line.credit_atomic,row.scale,row.currency) }}</p></details></div><button v-if="row.source.startsWith('manual:')&&can('journal.reverse')" class="secondary" @click="open('journal.reverse',row)">{{ t('Reverse journal') }}</button></div><p v-if="!journals.data.length">{{ t('No records yet') }}</p></section>
    <section v-if="receipts" class="biz-panel detail"><h2>{{ t('Goods receipts') }}</h2><div v-for="row in receipts.data" :key="row.id" class="history-row"><div><strong>{{ row.reference }}</strong><small>{{ t('Receipt ID') }}: {{ row.id }}</small><small>{{ t('Order ID') }}: {{ row.purchase_id }}</small></div><strong class="amount">{{ money(row.total_atomic,row.scale,row.currency) }}</strong></div><p v-if="!receipts.data.length">{{ t('No records yet') }}</p></section>
    <section v-if="interactions" class="biz-panel detail"><h2>{{ t('Customer interactions') }}</h2><div v-for="row in interactions.data" :key="row.id" class="history-row"><div><strong>{{ records.data.find(c=>c.id===row.customer_id)?.name??row.customer_id }}</strong><p>{{ row.note }}</p><small>{{ t(row.channel) }} · {{ row.follow_up_date ? `${t('Follow-up date')}: ${row.follow_up_date}` : cell(row,'created_at_unix') }}</small></div></div><p v-if="!interactions.data.length">{{ t('No records yet') }}</p></section>
    <section v-if="attendance" class="biz-panel detail"><h2>{{ t('Attendance') }} · WIB</h2><div v-for="row in attendance.data" :key="row.id" class="history-row"><div><strong>{{ records.data.find(e=>e.id===row.employee_id)?.name??row.employee_id }}</strong><small>{{ row.work_date }} · {{ localTime(row.clock_in).slice(11) }} – {{ row.clock_out?localTime(row.clock_out).slice(11):t('Still working') }}</small><p>{{ row.note }}</p></div><button v-if="can('attendance.record')" class="secondary" @click="open('attendance.record',row)">{{ t('Edit') }}</button></div><p v-if="!attendance.data.length">{{ t('No records yet') }}</p></section>
    <section v-if="leave" class="biz-panel detail"><h2>{{ t('Leave requests') }}</h2><div v-for="row in leave.data" :key="row.id" class="history-row"><div><strong>{{ records.data.find(e=>e.id===row.employee_id)?.name??row.employee_id }}</strong><small>{{ row.start_date }} – {{ row.end_date }} · {{ t(row.status) }}</small><p>{{ row.reason }}</p></div><div class="row-actions"><button v-if="row.status==='PENDING'&&can('leave.approve')" class="secondary" @click="open('leave.approve',row)">{{ t('Approve') }}</button><button v-if="row.status==='PENDING'&&can('leave.reject')" class="secondary" @click="open('leave.reject',row)">{{ t('Reject') }}</button></div></div><p v-if="!leave.data.length">{{ t('No records yet') }}</p></section>
    <dialog ref="dialog" class="biz-dialog" @cancel.prevent="close"><form @submit.prevent="submit"><header><div><p class="eyebrow">{{ t(title) }}</p><h2>{{ t(titles[mode]??'Review action') }}</h2><p v-if="selected">{{ recordName(selected) }}</p></div><button type="button" class="text-button" :disabled="busy||uncertain" :aria-label="t('Close')" @click="close">✕</button></header>
      <p v-if="mode==='invoice.settle'" class="notice">{{ t('Record an actual payment that has already occurred. This does not transfer money.') }}</p>
      <p v-if="mode==='customer.save'" class="notice">{{ t('Marketing consent must come from the customer. Leave it off when consent has not been obtained.') }}</p>
      <p v-if="mode==='purchase.create'" class="notice">{{ t('Use products from your POS catalog with opening stock configured. Costs are entered per unit.') }}</p>
      <div v-if="['purchase.create','invoice.create'].includes(mode)" class="option-search"><label>{{ t('Find supplier or customer') }}<input v-model="optionQuery" :disabled="busy||uncertain"/></label><button type="button" class="secondary" :disabled="optionLoading||busy||uncertain" @click="findChoices()">{{ t('Search') }}</button><button v-if="optionMore" type="button" class="secondary" :disabled="optionLoading||busy||uncertain" @click="findChoices(true)">{{ t('Next') }}</button></div>
      <div v-if="mode==='purchase.create'" class="option-search"><label>{{ t('Find product by name or code') }}<input v-model="productQuery" :disabled="busy||uncertain" /></label><button type="button" class="secondary" :disabled="productLoading||busy||uncertain" @click="findProducts()">{{ t('Search products') }}</button><button v-if="productMore" type="button" class="secondary" :disabled="productLoading||busy||uncertain" @click="findProducts(true)">{{ t('Next') }}</button></div>
      <fieldset :disabled="busy||uncertain"><div class="form-grid"><label v-for="field in fields" :key="field.key" :class="{'full-width':field.type==='textarea','check':field.type==='checkbox'}"><span>{{ t(field.label) }}</span><select v-if="field.type==='select'" v-model="form[field.key]" :required="field.required"><option value="" disabled>{{ t('Select an option') }}</option><option v-for="opt in field.options" :key="opt.value" :value="opt.value">{{ t(opt.label) }}</option></select><textarea v-else-if="field.type==='textarea'" v-model="form[field.key]" :required="field.required" maxlength="2000" rows="3"/><input v-else-if="field.type==='checkbox'" v-model="form[field.key]" type="checkbox"/><NumericInput v-else-if="field.type==='numeric'" v-model="form[field.key]" :decimals="selected?.scale??Number(form.scale??0)"/><input v-else v-model="form[field.key]" :type="field.type??'text'" :required="field.required" maxlength="254"/></label></div>
      <section v-if="mode==='employee.save'&&can('job.save')" class="staff-account"><h3><WorkspaceIcon name="shield-check"/> {{tr('Akun kasir untuk pegawai ini','Cashier account for this employee')}}</h3><p class="notice">{{tr('Jabatan menjelaskan tugas. Akses kasir memberi izin POS tanpa membuka administrasi bisnis. Menonaktifkan pegawai juga menonaktifkan akun kasir terkait.','Job titles describe responsibilities. Cashier access grants POS permissions without business administration. Deactivating an employee also disables their linked cashier account.')}}</p><label class="check"><input v-model="posAccess" type="checkbox"/> {{tr('Aktifkan akses kasir POS','Enable POS cashier access')}}</label><div v-if="posAccess||posAccount.version>0" class="form-grid"><label>Email login<input v-model="posAccount.login" type="email" :readonly="posAccount.version>0" required autocomplete="off" maxlength="190"/></label><label v-if="posAccount.version===0">{{tr('Kata sandi awal · minimal 12 karakter','Initial password · at least 12 characters')}}<input v-model="posAccount.password" type="password" minlength="12" maxlength="72" required autocomplete="new-password"/></label><label>{{tr('PIN kunci POS · 6 angka','POS lock PIN · 6 digits')}}<input v-model="posAccount.pin" type="password" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" :required="posAccount.version===0" autocomplete="new-password" :placeholder="posAccount.version>0?tr('Kosongkan untuk mempertahankan','Leave blank to keep'):''"/></label></div></section>
      <div v-if="lines.length" class="line-editor"><h3>{{ t(mode==='journal.post'?'Journal lines':'Order lines') }}</h3><div v-for="(line,index) in lines" :key="index" class="line-entry">
        <template v-if="mode==='journal.post'"><label>{{ t('Account') }}<select v-model="line.account"><option v-for="(account,code) in accounts" :key="code" :value="code">{{ code }} · {{ t(account[0]) }}</option></select></label><label>{{ t('Debit') }}<NumericInput :decimals="Number(form.scale??0)" v-model="line.debit" inputmode="decimal" required/></label><label>{{ t('Credit') }}<NumericInput :decimals="Number(form.scale??0)" v-model="line.credit" inputmode="decimal" required/></label></template>
        <template v-else><label>{{ t('Product') }}<select v-if="mode==='purchase.create'" v-model="line.product_id" required><option value="" disabled>{{ t('Select an option') }}</option><option v-for="product in products" :key="product.product_id" :value="product.product_id">{{ product.display_name }} · {{ product.product_id }} · {{ product.currency }}</option></select><input v-else v-model="line.product_id" readonly required/><small v-if="line.name">{{ line.name }}</small></label><label>{{ t('Quantity') }}<NumericInput v-model="line.quantity" :min="mode==='purchase.receive'?0:1" :max="line.remaining??1000000" step="1" required/></label><label v-if="mode==='purchase.create'">{{ t('Unit cost') }}<NumericInput :decimals="Number(form.scale??0)" v-model="line.unit_cost" inputmode="decimal" required/></label></template>
        <button v-if="mode!=='purchase.receive'&&lines.length>1" type="button" class="text-button danger" @click="lines.splice(index,1)">{{ t('Remove') }}</button>
      </div><button v-if="mode!=='purchase.receive'&&lines.length<100" type="button" class="secondary" @click="lines.push(mode==='journal.post'?{account:'6000',debit:'0',credit:'0'}:{product_id:'',quantity:'1',unit_cost:'0'})">+ {{ t('Add line') }}</button></div>
      <p v-if="fields.length===0" class="notice">{{ t('Review this record before confirming the action.') }} <strong v-if="selected?.total_atomic">{{ money(selected.total_atomic,selected.scale,selected.currency) }}</strong></p>
      </fieldset><p v-if="error" class="error" role="alert">{{ error }}</p><p v-if="uncertain" class="notice">{{ t('The result is not confirmed. Retry the same request to avoid duplicate records.') }}</p><footer><button type="button" class="secondary" :disabled="busy||uncertain" @click="close">{{ t('Cancel') }}</button><button :disabled="busy">{{ t(busy?'Saving…':uncertain?'Retry same request':'Confirm and save') }}</button></footer></form></dialog>
  </main>
</template>

<style scoped>
.biz{max-width:1500px;margin:auto;padding:30px;color:#1B1B39}.biz-heading,.section-title,.toolbar,.toolbar-actions,.pagination,.pagination>div,.history-row,.row-actions{display:flex;align-items:center;gap:12px}.biz-heading,.section-title,.toolbar,.pagination,.history-row{justify-content:space-between}.biz-heading{margin-bottom:24px}.biz h1{font-size:32px;line-height:1.2;letter-spacing:-.035em;margin:6px 0 10px}.biz h2{font-size:20px;margin:0 0 8px}.biz p{line-height:1.6}.biz-heading p,.detail>p,.section-title p{color:#3965FA;margin:4px 0}.eyebrow{text-transform:uppercase;letter-spacing:.12em;font-size:14px;font-weight:750;color:#3965FA!important}.biz-tabs{display:flex;gap:6px;overflow:auto;border-bottom:1px solid #EDEDED;margin-bottom:24px}.biz-tabs a{white-space:nowrap;padding:12px 18px;color:#3965FA;text-decoration:none;border-bottom:3px solid transparent}.biz-tabs a[aria-current=page]{border-color:#3965FA;color:#1B1B39;font-weight:700}.biz-panel{background:#FFFFFF;border:1px solid #E9EBFF;border-radius:16px;margin-bottom:22px;box-shadow:0 2px 6px #1B1B3905;overflow:hidden}.toolbar,.detail{padding:22px}.toolbar,.toolbar-actions,.row-actions{flex-wrap:wrap}.search,.period,.option-search{display:flex;align-items:end;gap:8px}.biz button,.biz a.secondary{border:1px solid transparent;border-radius:9px;padding:10px 15px;background:#1B1B39;color:var(--oq-white);font-size:14px;font-weight:650;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px}.biz button.secondary,.biz a.secondary{background:var(--oq-white);color:#1B1B39;border-color:#99B7FC}.biz button:disabled{opacity:.45;cursor:not-allowed}.biz button.text-button{background:transparent;color:#1B1B39;padding:6px 8px;min-height:36px;white-space:nowrap}.biz button.danger{color:#3965FA}.biz input,.biz select,.biz textarea{background:var(--oq-white);border:1px solid #99B7FC;border-radius:8px;color:#1B1B39;font:inherit;font-size:14px;padding:10px 11px;min-height:42px;min-width:0;max-width:100%;width:100%}.biz input:focus,.biz select:focus,.biz textarea:focus,.biz button:focus-visible,.biz a:focus-visible{outline:3px solid #3965FA;outline-offset:2px}.table-wrap{overflow:auto;max-width:100%}.biz table{border-collapse:collapse;width:100%;text-align:left;font-size:14px}.biz th{background:#FFFFFF;font-weight:650;color:#3965FA;font-size:14px;text-transform:uppercase;letter-spacing:.035em;white-space:nowrap}.biz td,.biz th{padding:15px 20px;border-bottom:1px solid #E9EBFF}.biz td{max-width:280px;overflow-wrap:anywhere}.biz tbody tr:last-child td{border-bottom:0}.biz tbody tr:hover{background:#FFFFFF}.amount{font-variant-numeric:tabular-nums;white-space:nowrap}.badge{font-size:14px;padding:5px 8px;background:#E9EBFF;color:#1B1B39;border-radius:6px;font-weight:750}.pagination{border-top:1px solid #E9EBFF;padding:16px 22px;font-size:14px;color:#3965FA}.empty{padding:56px 20px;text-align:center;color:#3965FA}.empty h2{color:#1B1B39;margin-top:14px}.empty p{max-width:380px;margin:auto}.history-row{padding:16px 0;border-bottom:1px solid #E9EBFF;align-items:flex-start}.history-row:last-child{border-bottom:0}.history-row p{white-space:pre-wrap;margin:6px 0;font-size:14px}.biz small{display:block;color:#3965FA;font-size:14px;margin-top:5px}.biz details{font-size:14px;margin-top:8px}.biz summary{cursor:pointer;color:#1B1B39}.period label,.option-search label{font-size:14px;color:#3965FA}.period input{display:block;margin-top:4px}.biz-dialog{width:min(780px,calc(100vw - 32px));max-height:90dvh;border:1px solid #EDEDED;border-radius:18px;padding:0;box-shadow:0 24px 90px #1B1B3940;color:#1B1B39}.biz-dialog::backdrop{background:#1B1B3988;backdrop-filter:blur(3px)}.biz-dialog form{padding:26px}.biz-dialog header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:18px}.biz-dialog header p{margin:4px 0;color:#3965FA}.biz-dialog fieldset{border:0;margin:0;padding:0;min-width:0}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:17px}.form-grid label,.line-entry label{display:flex;flex-direction:column;gap:7px;font-size:14px;font-weight:600;color:#3965FA}.form-grid label.full-width{grid-column:1/-1}.form-grid label.check{flex-direction:row;align-items:center;justify-content:space-between}.check input{width:19px;height:19px;min-height:0;accent-color:#1B1B39}.line-editor{margin-top:24px;border-top:1px solid #E9EBFF;padding-top:10px}.line-editor h3{font-size:15px}.line-entry{display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:9px;align-items:start;margin:12px 0}.line-entry .text-button{margin-top:23px}.notice{background:#FFFFFF;color:#3965FA;font-size:14px;padding:12px 14px;border-radius:9px;margin:14px 0}.error{color:#3965FA;background:#EDEDED;padding:13px;border-radius:8px}.success{background:#E9EBFF;color:#1B1B39;padding:14px;border-radius:9px}.biz-dialog footer{display:flex;gap:10px;justify-content:flex-end;margin-top:24px;border-top:1px solid #E9EBFF;padding-top:20px}.option-search{margin:14px 0}.option-search label{flex:1}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
@media(max-width:900px){.biz{padding:20px}.biz-heading,.section-title{align-items:flex-start;flex-direction:column}.toolbar-actions{width:100%}.biz h1{font-size:27px}.period{flex-wrap:wrap}.line-entry{grid-template-columns:1.4fr 1fr 1fr}.line-entry .text-button{grid-column:1/-1;margin:0;justify-self:end}}@media(max-width:580px){.biz{padding:15px}.biz-heading>a{display:none}.toolbar,.detail{padding:16px}.biz-tabs a{padding:10px 12px}.search{width:100%}.search input{flex:1}.form-grid{grid-template-columns:1fr}.biz-dialog form{padding:18px}.pagination{align-items:flex-start;gap:14px;flex-direction:column}.history-row{gap:12px;flex-direction:column}.line-entry{grid-template-columns:1fr 1fr}.line-entry label:first-child{grid-column:1/-1}.biz td,.biz th{padding:12px 14px}.biz table{min-width:520px}}@media print{.biz-heading>a,.biz-tabs,.toolbar,.pagination,.row-actions,dialog{display:none!important}.biz-panel{border:1px solid #EDEDED;box-shadow:none;break-inside:avoid}.biz{padding:0}.table-wrap{overflow:visible}}
.staff-account{border:1px solid #99B7FC;background:#FFFFFF;border-radius:14px;padding:20px;margin-top:20px}.staff-account h3{display:flex;align-items:center;gap:10px}.staff-account .check{display:flex;align-items:center;gap:10px;margin-bottom:20px}
.biz{color:var(--oq-dark);font-size:1rem;line-height:1.55}
.biz h1,.biz h2{color:var(--oq-navy)}
.biz-panel{background:var(--oq-white);border-color:var(--oq-light);box-shadow:0 8px 24px #1B1B3914}
.biz-heading p,.section-title p,.detail>p,.eyebrow{color:var(--oq-navy)!important;font-size:.875rem}
.biz button,.biz a.secondary{background:var(--oq-primary);color:var(--oq-white);font-size:.875rem;min-height:44px}
.biz button.secondary,.biz a.secondary{background:var(--oq-white);color:var(--oq-navy);border:1px solid var(--oq-soft)}
.biz button.text-button,.biz button.danger{background:var(--oq-pale);color:var(--oq-navy)}
.biz-tabs a[aria-current=page]{border-color:var(--oq-primary);color:var(--oq-navy);background:var(--oq-pale)}
.biz input,.biz select,.biz textarea{background:var(--oq-white);color:var(--oq-dark);font-size:1rem;min-height:44px;border:1px solid var(--oq-soft)}
.biz button:focus-visible,.biz a:focus-visible,.biz input:focus-visible,.biz select:focus-visible{outline:3px solid var(--oq-primary);outline-offset:3px}

</style>
