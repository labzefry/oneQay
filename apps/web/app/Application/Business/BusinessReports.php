<?php
declare(strict_types=1);
namespace App\Application\Business;
// Author by Lab | zefry. Complete filtered exports; never silently stop at the UI page.
final readonly class BusinessReports {
    public function __construct(private BusinessAccess $access,private BusinessManagement $business,private Merchandising $products,private CommerceReportingRepository $sales){}
    public function available():array {
        $result=[];foreach(['sales'=>['Sales','pos.reporting.sales-summary.view'],'products'=>['Products','pos.catalog.prepare'],'trial-balance'=>['Trial balance','business.finance.view'],...BusinessAccess::MODULES]as$key=>[$title,$permission]){
            if(in_array($key,['access','settings','reports'],true))continue;if($this->access->allows($permission))$result[]=['key'=>$key,'title'=>$title];
        }return $result;
    }
    public function trend(array $filters):array {
        $scope=$this->access->require('pos.reporting.sales-summary.view');$from=BusinessInput::date($filters['from']??gmdate('Y-m-d',time()-6*86400));$to=BusinessInput::date($filters['to']??gmdate('Y-m-d'));
        if($from>$to||(new \DateTimeImmutable($from))->diff(new \DateTimeImmutable($to))->days>366)throw new BusinessViolation('INVALID_REPORT_PERIOD');
        $rows=$this->sales->sales($scope,$from,$to);$groups=[];
        foreach($rows as$r){$key=$r['currency'].':'.$r['currency_scale'];$groups[$key]??=['currency'=>$r['currency'],'scale'=>(int)$r['currency_scale'],'gross_atomic'=>0,'active_atomic'=>0,'refunded_atomic'=>0,'transactions'=>0,'days'=>[]];$g=&$groups[$key];$day=substr($r['date'],0,10);$g['days'][$day]??=['date'=>$day,'gross_atomic'=>0,'active_atomic'=>0,'transactions'=>0];
            foreach(['gross_atomic'=>'total_atomic','active_atomic'=>'active_atomic','refunded_atomic'=>'refunded_atomic']as$target=>$source)$g[$target]=BusinessInput::add($g[$target],BusinessInput::integer($r[$source]));
            $g['transactions']++;$g['days'][$day]['transactions']++;$g['days'][$day]['gross_atomic']=BusinessInput::add($g['days'][$day]['gross_atomic'],BusinessInput::integer($r['total_atomic']));$g['days'][$day]['active_atomic']=BusinessInput::add($g['days'][$day]['active_atomic'],BusinessInput::integer($r['active_atomic']));unset($g);
        }
        foreach($groups as&$g){foreach(['gross_atomic','active_atomic','refunded_atomic']as$k)$g[$k]=(string)$g[$k];foreach($g['days']as&$d){$d['gross_atomic']=(string)$d['gross_atomic'];$d['active_atomic']=(string)$d['active_atomic'];}unset($d);$g['days']=array_values($g['days']);}unset($g);
        return ['from'=>$from,'to'=>$to,'currencies'=>array_values($groups),'transactions'=>count($rows)];
    }
    public function read(string $report,array $filters):array {
        $from=BusinessInput::date($filters['from']??gmdate('Y-01-01'));$to=BusinessInput::date($filters['to']??gmdate('Y-m-d'));
        if($from>$to||(new \DateTimeImmutable($from))->diff(new \DateTimeImmutable($to))->days>3660)throw new BusinessViolation('INVALID_REPORT_PERIOD');
        $q=BusinessInput::text($filters['q']??'',120,true);
        if($report==='sales'){$s=$this->access->require('pos.reporting.sales-summary.view');$rows=$this->sales->sales($s,$from,$to);$headers=['sale_id','date','tender_category','status','currency','currency_scale','total_atomic','active_atomic','refunded_atomic'];}
        elseif($report==='products'){$rows=[];for($page=1;$page<=200;$page++){$v=$this->products->products($filters,$page);$rows=[...$rows,...$v['data']];if(!$v['has_more'])break;if($page===200)throw new BusinessViolation('REPORT_ROW_LIMIT_NARROW_FILTER',413);}$headers=['sku','display_name','category','brand','variant','barcode','available_quantity','currency','currency_scale','unit_price_atomic','cost_atomic','wholesale_atomic','active'];}
        else {
            $module=$report==='trial-balance'?'finance':$report;if(!isset(BusinessAccess::MODULES[$module])||in_array($module,['access','settings','reports'],true))throw new BusinessViolation('UNKNOWN_REPORT',404);
            $rows=[];for($page=1;$page<=200;$page++){$v=$this->business->workspace($module,$page,$q,$from,$to);if($report==='trial-balance'){$rows=$v['balances'];break;}$rows=[...$rows,...$v['records']['data']];if(!$v['records']['has_more'])break;if($page===200)throw new BusinessViolation('REPORT_ROW_LIMIT_NARROW_FILTER',413);}
            $headers=match($report){'suppliers','customers'=>['code','name','email','phone','active','marketing_consent'],'purchases'=>['reference','status','currency','scale','total_atomic'],'finance'=>['reference','kind','issue_date','due_date','currency','scale','total_atomic','paid_atomic','status'],'trial-balance'=>['account','currency','scale','debit_atomic','credit_atomic'],'people'=>['code','name','department','job_title','start_date','active'],'audit'=>['action','resource_id','created_by','created_at_unix','correlation_id'],default=>throw new BusinessViolation('UNKNOWN_REPORT',404)};
        }
        $names=['sku'=>'SKU','display_name'=>'Nama produk','category'=>'Kategori','brand'=>'Merek','variant'=>'Varian','barcode'=>'Barcode','available_quantity'=>'Stok','currency'=>'Mata uang','currency_scale'=>'Desimal','scale'=>'Desimal','unit_price'=>'Harga jual','cost'=>'Harga beli','wholesale'=>'Harga grosir','active'=>'Aktif','sale_id'=>'No. penjualan','date'=>'Tanggal','tender_category'=>'Pembayaran','status'=>'Status','total'=>'Total','paid'=>'Dibayar','active_amount'=>'Penjualan aktif','refunded'=>'Pengembalian','name'=>'Nama','code'=>'Kode','email'=>'Email','phone'=>'Telepon','marketing_consent'=>'Persetujuan marketing','reference'=>'Referensi','issue_date'=>'Tanggal terbit','due_date'=>'Jatuh tempo','kind'=>'Jenis','debit'=>'Debit','credit'=>'Kredit','account'=>'Akun','department'=>'Departemen','job_title'=>'Jabatan','start_date'=>'Tanggal mulai','action'=>'Aktivitas','resource_id'=>'Referensi data','created_by'=>'Dicatat oleh','created_at_unix'=>'Waktu Unix','correlation_id'=>'Referensi dukungan'];
        $cells=[];$labels=[];foreach($headers as$h){$k=str_ends_with($h,'_atomic')?substr($h,0,-7):$h;$labels[]=($filters['locale']??'id')==='en'?ucwords(str_replace('_',' ',$k)):($names[$k]??ucwords(str_replace('_',' ',$k)));}
        foreach($rows as$row){$cells[] = array_map(static function($h)use($row):array {$value=$row[$h]??'';$numeric=in_array($h,['available_quantity','scale','currency_scale'],true)||str_ends_with($h,'_atomic');if(str_ends_with($h,'_atomic'))$value=CurrencyPreference::decimal((string)$value,(int)($row['scale']??$row['currency_scale']??0));if(is_bool($value))$value=$value?'Ya / Yes':'Tidak / No';return ['value'=>(string)$value,'number'=>$numeric];},$headers);}
        $titles=['products'=>'Produk','sales'=>'Penjualan','suppliers'=>'Pemasok','customers'=>'Pelanggan','purchases'=>'Pembelian','finance'=>'Keuangan','trial-balance'=>'Neraca saldo','people'=>'Karyawan','audit'=>'Aktivitas'];
        $doc=['title'=>(($filters['locale']??'id')==='en'?'Report: '.$report:'Laporan '.($titles[$report]??$report)),'headers'=>$labels,'rows'=>$cells,'count'=>count($rows),'meta'=>['Bisnis'=>$this->products->preferences()['business_name'],'Outlet'=>$this->access->context()->outletId()?->value()??'',in_array($report,['sales','trial-balance'],true)?'Periode':'Data'=>in_array($report,['sales','trial-balance'],true)?$from.' — '.$to:'Terkini','Filter'=>$q]];
        if($report==='products'){
            $prefs=$this->products->preferences();$doc['pdf_headers']=['SKU','Nama produk','Kategori','Merek / varian','Barcode','Stok','Harga beli','Harga jual','Grosir','Aktif'];$doc['pdf_rows']=[];
            foreach($rows as$r){$symbol=$r['currency']===$prefs['currency']?$prefs['symbol']:$r['currency'];$amount=static fn($key)=>$symbol.' '.CurrencyPreference::decimal((string)$r[$key],$r['currency_scale']);$doc['pdf_rows'][]=[$r['sku'],$r['display_name'],$r['category'],$r['brand'].($r['variant']!==''?"\n".$r['variant']:''),$r['barcode'],$r['available_quantity'],$amount('cost_atomic'),$amount('unit_price_atomic'),$amount('wholesale_atomic'),$r['active']?'Ya':'Tidak'];}
        }return $doc;
    }
}
