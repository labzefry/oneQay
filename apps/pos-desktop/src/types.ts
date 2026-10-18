export type Product={id:string;name:string;price:number;currency:string;scale:number;stock:number;barcode:string;category:string}
export type Line={product_id:string;quantity:number}
export type Operation={created_at:number;kind:'sale'|'count';lease_id:string;lines:Line[];operation_id:string;tendered:number}
export type Receipt={operation:Operation;state:string;total:number;result?:{code:string;sale_id?:string;correlation_id?:string};receipt:{lines:{name:string;price:number;quantity:number}[];total:number;change:number;tendered:number;currency:string;scale:number}}
export type Printer={transport:'windows'|'network';queue:string;host:string;port:number;paper_mm:number;drawer_enabled:boolean;auto_print:boolean}
export type Workspace={snapshot:null|{scope:{tenant_id:string;identity_id:string;organization_id:string;outlet_id:string;device_id:string};shift_id:string;expires_at:number;server_time:number;issued_at:number;business_name?:string;currency_symbol?:string};products:Product[];operations:Receipt[];settings:Printer;last_server_at:number;pending:number;review:number;authorized:boolean}
