<?php
declare(strict_types=1);
namespace App\Infrastructure\Business;
use App\Application\Business\BusinessViolation;
// Author by Lab | zefry. Real OOXML; text is never interpreted as an Excel formula.
final class SpreadsheetDocument {
    public const IMPORT_HEADERS=['sku','name','category','brand','variant','barcode','price','cost','wholesale','opening_quantity','active'];
    private static function xml(string $v):string {return htmlspecialchars($v,ENT_XML1|ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
    public static function xlsx(array $headers,array $rows,string $title='oneQay'):string {
        $data=[$headers,...$rows];$sheet='<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols><col min="1" max="'.count($headers).'" width="23" customWidth="1"/></cols><sheetData>';
        foreach($data as$i=>$row){$sheet.='<row r="'.($i+1).'">';foreach(array_values($row)as$j=>$cell){$column='';$n=$j+1;while($n){$n--;$column=chr(65+$n%26).$column;$n=intdiv($n,26);}$address=$column.($i+1);
            $number=is_array($cell)&&($cell['number']??false);$value=is_array($cell)?(string)$cell['value']:(string)$cell;
            // Excel has 15 significant digits. Keep larger exact amounts as text.
            $number=$number&&preg_match('/\A-?(?:0|[1-9][0-9]*)(?:\.[0-9]+)?\z/D',$value)&&strlen(ltrim(str_replace(['.','-'],'',$value),'0'))<=15;
            $sheet.=$number?'<c r="'.$address.'"><v>'.$value.'</v></c>':'<c r="'.$address.'" t="inlineStr" s="'.($i===0?'1':'0').'"><is><t xml:space="preserve">'.self::xml($value).'</t></is></c>';
        }$sheet.='</row>';}$sheet.='</sheetData><autoFilter ref="A1:'.($column??'A').count($data).'"/></worksheet>';
        return self::zip([
            '[Content_Types].xml'=>'<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
            '_rels/.rels'=>'<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml'=>'<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="'.self::xml(mb_substr($title,0,31)).'" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels'=>'<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml'=>'<?xml version="1.0"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF087CC2"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border/></borders><cellStyleXfs count="1"><xf/></cellStyleXfs><cellXfs count="2"><xf fontId="0" fillId="0" borderId="0" xfId="0"/><xf fontId="1" fillId="2" borderId="0" xfId="0" applyFill="1" applyFont="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>',
            'xl/worksheets/sheet1.xml'=>$sheet]);
    }
    public static function zip(array $files):string {
        $body='';$directory='';foreach($files as$name=>$bytes){$offset=strlen($body);$crc=crc32($bytes);$len=strlen($bytes);$n=strlen($name);
            $body.=pack('VvvvvvVVVvv',0x04034b50,20,0x0800,0,0,0,$crc,$len,$len,$n,0).$name.$bytes;
            $directory.=pack('VvvvvvvVVVvvvvvVV',0x02014b50,20,20,0x0800,0,0,0,$crc,$len,$len,$n,0,0,0,0,0,$offset).$name;
        }return $body.$directory.pack('VvvvvVVv',0x06054b50,0,0,count($files),count($files),strlen($directory),strlen($body),0);
    }
    public static function unzip(string $raw):array {
        $end=strrpos($raw,pack('V',0x06054b50));if($end===false||strlen($raw)-$end<22)throw new BusinessViolation('INVALID_SPREADSHEET');
        $e=unpack('vdisk/vstart/vdisk_count/vcount/Vsize/Voffset/vcomment',substr($raw,$end+4,18));
        if($e['disk']!==0||$e['start']!==0||$e['count']<1||$e['count']>128||$e['disk_count']!==$e['count']||$e['offset']+$e['size']>$end)throw new BusinessViolation('INVALID_SPREADSHEET');
        $at=$e['offset'];$files=[];$total=0;for($i=0;$i<$e['count'];$i++){
            if(substr($raw,$at,4)!==pack('V',0x02014b50)||$at+46>strlen($raw))throw new BusinessViolation('INVALID_SPREADSHEET');
            $h=unpack('vflags/vmethod/vtime/vdate/Vcrc/Vcompressed/Vsize/vname/vextra/vcomment/vdisk/vinternal/Vexternal/Voffset',substr($raw,$at+8,38));$name=substr($raw,$at+46,$h['name']);$at+=46+$h['name']+$h['extra']+$h['comment'];$total+=$h['size'];
            if($h['flags']&1||!in_array($h['method'],[0,8],true)||$h['size']>4000000||$total>12000000||$h['size']>max(1,$h['compressed'])*200||isset($files[$name])||str_contains($name,'..')||str_starts_with($name,'/')||str_contains($name,'\\'))throw new BusinessViolation('UNSAFE_SPREADSHEET');
            $o=$h['offset'];if(substr($raw,$o,4)!==pack('V',0x04034b50)||$o+30>strlen($raw))throw new BusinessViolation('INVALID_SPREADSHEET');
            $l=unpack('vname/vextra',substr($raw,$o+26,4));$start=$o+30+$l['name']+$l['extra'];if($start+$h['compressed']>$e['offset'])throw new BusinessViolation('INVALID_SPREADSHEET');$bytes=substr($raw,$start,$h['compressed']);
            if($h['method']===8){if(!function_exists('gzinflate'))throw new BusinessViolation('SPREADSHEET_EXTENSION_REQUIRED',503);$bytes=@gzinflate($bytes,$h['size']+1);}
            if(!is_string($bytes)||strlen($bytes)!==$h['size']||sprintf('%u',crc32($bytes))!==sprintf('%u',$h['crc']))throw new BusinessViolation('INVALID_SPREADSHEET');$files[$name]=$bytes;
        }return $files;
    }
    private static function document(string $xml):\DOMDocument {
        if(!class_exists(\DOMDocument::class))throw new BusinessViolation('SPREADSHEET_EXTENSION_REQUIRED',503);
        if(preg_match('/<!DOCTYPE|<!ENTITY/i',$xml))throw new BusinessViolation('UNSAFE_SPREADSHEET');
        $prior=libxml_use_internal_errors(true);try{$d=new \DOMDocument();if(!$d->loadXML($xml,LIBXML_NONET|LIBXML_NOBLANKS))throw new BusinessViolation('INVALID_SPREADSHEET');return $d;}finally{libxml_clear_errors();libxml_use_internal_errors($prior);}
    }
    public static function import(string $raw,string $extension):array {
        if(strlen($raw)>2000000)throw new BusinessViolation('IMPORT_FILE_TOO_LARGE',413);$table=[];
        if($extension==='csv'){
            if(preg_match('//u',$raw)!==1)throw new BusinessViolation('IMPORT_UTF8_REQUIRED');$raw=preg_replace('/\A\xEF\xBB\xBF/','',$raw);$handle=fopen('php://temp','w+');fwrite($handle,$raw);rewind($handle);
            $first=strtok($raw,"\r\n")?:'';$delimiter=substr_count($first,';')>substr_count($first,',')?';':',';
            while(($row=fgetcsv($handle,0,$delimiter,'"',''))!==false){if($row===[null])continue;$table[]=$row;if(count($table)>501)throw new BusinessViolation('IMPORT_ROW_LIMIT',413);}fclose($handle);
        }elseif($extension==='xlsx'){
            $files=self::unzip($raw);if(isset($files['xl/vbaProject.bin']))throw new BusinessViolation('UNSAFE_SPREADSHEET');
            $work=self::document($files['xl/workbook.xml']??'');$xp=new \DOMXPath($work);$sheets=$xp->query('//*[local-name()="sheets"]/*[local-name()="sheet"]');if(!$sheets||$sheets->length!==1)throw new BusinessViolation('IMPORT_SINGLE_SHEET_REQUIRED');
            $id=$sheets->item(0)->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships','id');$rels=self::document($files['xl/_rels/workbook.xml.rels']??'');$path=null;
            foreach($rels->getElementsByTagName('Relationship')as$r){if($r->getAttribute('TargetMode')==='External')throw new BusinessViolation('UNSAFE_SPREADSHEET');if($r->getAttribute('Id')===$id){$target=$r->getAttribute('Target');if(str_contains($target,'..')||str_contains($target,'\\'))throw new BusinessViolation('UNSAFE_SPREADSHEET');$path=str_starts_with($target,'/')?ltrim($target,'/'):'xl/'.$target;}}
            if(!$path||!isset($files[$path]))throw new BusinessViolation('INVALID_SPREADSHEET');$shared=[];
            if(isset($files['xl/sharedStrings.xml'])){foreach(self::document($files['xl/sharedStrings.xml'])->getElementsByTagName('si')as$si){$v='';foreach($si->getElementsByTagName('t')as$t)$v.=$t->textContent;$shared[]=$v;}}
            $sheet=self::document($files[$path]);if($sheet->getElementsByTagName('f')->length>0)throw new BusinessViolation('IMPORT_FORMULAS_NOT_ALLOWED');
            foreach($sheet->getElementsByTagName('row')as$r){$row=array_fill(0,count(self::IMPORT_HEADERS),'');foreach($r->getElementsByTagName('c')as$c){if(!preg_match('/\A([A-Z]{1,2})[1-9][0-9]*\z/D',$c->getAttribute('r'),$m))throw new BusinessViolation('INVALID_SPREADSHEET');$col=0;foreach(str_split($m[1])as$letter)$col=$col*26+ord($letter)-64;$col--;if($col>=count(self::IMPORT_HEADERS))throw new BusinessViolation('IMPORT_COLUMNS_INVALID');$value='';if($c->getAttribute('t')==='inlineStr'){foreach($c->getElementsByTagName('t')as$t)$value.=$t->textContent;}else{$value=$c->getElementsByTagName('v')->item(0)?->textContent??'';if($c->getAttribute('t')==='s'){$index=filter_var($value,FILTER_VALIDATE_INT);if($index===false||!isset($shared[$index]))throw new BusinessViolation('INVALID_SPREADSHEET');$value=$shared[$index];}}$row[$col]=$value;}$table[]=$row;if(count($table)>501)throw new BusinessViolation('IMPORT_ROW_LIMIT',413);}
        }else throw new BusinessViolation('IMPORT_FORMAT_NOT_SUPPORTED');
        $headers=array_shift($table)??[];$headers=array_map(static fn($v)=>strtolower(trim((string)$v)),$headers);
        if(count($headers)!==count(self::IMPORT_HEADERS)||array_diff(self::IMPORT_HEADERS,$headers)!==[]||count(array_unique($headers))!==count($headers))throw new BusinessViolation('IMPORT_COLUMNS_INVALID');
        $rows=[];foreach($table as$r){if(count($r)!==count($headers))throw new BusinessViolation('IMPORT_COLUMNS_INVALID');$v=array_combine($headers,array_map(static fn($v)=>trim((string)$v),$r));
            $a=strtolower($v['active']);$v['active']=in_array($a,['1','true','yes','aktif'],true)?true:(in_array($a,['0','false','no','nonaktif'],true)?false:$v['active']);$rows[]=$v;
        }return $rows;
    }
}
