<?php
declare(strict_types=1);
namespace App\Infrastructure\Business;
// Author by Lab | zefry. Unicode TrueType font, repeated table headers and page numbers.
final class ReportPdf {
    private string $font;private array $tables=[];private array $characters=[];private int $units;private int $metrics;private int $cmap;private int $format;
    private function u16(int $p):int {return unpack('n',substr($this->font,$p,2))[1];}
    private function u32(int $p):int {return unpack('N',substr($this->font,$p,4))[1];}
    private function __construct() {
        $this->font=(string)file_get_contents(__DIR__.'/../../../resources/fonts/DejaVuSans.ttf');$count=$this->u16(4);
        for($i=0;$i<$count;$i++){$p=12+16*$i;$this->tables[substr($this->font,$p,4)]=$this->u32($p+8);}
        $this->units=$this->u16($this->tables['head']+18);$this->metrics=$this->u16($this->tables['hhea']+34);$base=$this->tables['cmap'];$this->cmap=0;$this->format=0;
        for($i=0;$i<$this->u16($base+2);$i++){$p=$base+4+8*$i;$off=$base+$this->u32($p+4);$f=$this->u16($off);if($f===12||($f===4&&$this->format!==12)){$this->cmap=$off;$this->format=$f;}}
    }
    private function glyph(int $cp):int {
        $p=$this->cmap;if($this->format===12){$n=$this->u32($p+12);for($i=0;$i<$n;$i++){$a=$p+16+12*$i;$start=$this->u32($a);$end=$this->u32($a+4);if($cp>=$start&&$cp<=$end)return $this->u32($a+8)+$cp-$start;}return 0;}
        $n=intdiv($this->u16($p+6),2);$end=$p+14;$start=$end+2*$n+2;$delta=$start+2*$n;$range=$delta+2*$n;
        for($i=0;$i<$n;$i++){if($cp<$this->u16($start+2*$i)||$cp>$this->u16($end+2*$i))continue;$d=$this->u16($delta+2*$i);$r=$this->u16($range+2*$i);if(!$r)return($cp+$d)&65535;$g=$this->u16($range+2*$i+$r+2*($cp-$this->u16($start+2*$i)));return $g?($g+$d)&65535:0;}return 0;
    }
    private function width(string $char,float $size):float {$g=$this->glyph(mb_ord($char,'UTF-8'));$advance=$this->u16($this->tables['hmtx']+4*min($g,$this->metrics-1));return $advance/$this->units*$size;}
    private function text(float $x,float $y,string $v,float $size=8,string $color='0.12 0.19 0.26'):string {
        $hex='';foreach(mb_str_split($v)as$char){$cp=mb_ord($char,'UTF-8');if(!isset($this->characters[$cp]))$this->characters[$cp]=['cid'=>count($this->characters)+1,'glyph'=>$this->glyph($cp),'char'=>$char];$hex.=sprintf('%04X',$this->characters[$cp]['cid']);}
        return "$color rg BT /F1 $size Tf 1 0 0 1 $x $y Tm <$hex> Tj ET\n";
    }
    private function wrap(string $v,float $width,float $size):array {
        $out=[];$line='';$length=0;foreach(mb_str_split(str_replace(["\r","\t"],['',' '],$v))as$char){$w=$this->width($char,$size);if($char==="\n"||$length+$w>$width){$out[]=$line;$line='';$length=0;if($char==="\n")continue;}$line.=$char;$length+=$w;}$out[]=$line;return $out;
    }
    public static function render(string $title,array $headers,array $rows,array $meta=[]):string {
        $p=new self();$pages=[];$stream='';$y=0;$width=770/max(1,count($headers));$date=(new \DateTimeImmutable('now',new \DateTimeZone('Asia/Jakarta')))->format('d-m-Y H:i').' WIB';
        $page=static function()use(&$pages,&$stream,&$y,$p,$title,$headers,$width,$date,$meta):void {
            if($stream!=='')$pages[]=$stream;$stream="1 1 1 rg 0 0 842 595 re f\n".$p->text(36,560,'oneQay  |  '.$title,17).$p->text(36,540,$date.'  ·  '.count($headers).' kolom',8);
            $details=implode('  ·  ',array_map(static fn($k,$v)=>$k.': '.$v,array_keys($meta),array_values($meta)));foreach(array_slice($p->wrap($details,770,7),0,2)as$i=>$line)$stream.=$p->text(36,525-10*$i,$line,7);$y=490;
            $stream.="0.03 0.48 0.76 rg 36 465 770 31 re f\n";foreach($headers as$i=>$header)foreach(array_slice($p->wrap((string)$header,$width-10,7),0,2)as$j=>$line)$stream.=$p->text(41+$width*$i,483-10*$j,$line,7,'1 1 1');$y=460;
        };$page();
        foreach($rows as$index=>$row){$cells=[];$height=24;foreach(array_values($row)as$value){$v=is_array($value)?(string)$value['value']:(string)$value;$lines=$p->wrap($v,$width-10,7);$cells[]=$lines;$height=max($height,count($lines)*10+12);}
            if($y-$height<45)$page();if($index%2===0)$stream.="0.95 0.97 0.99 rg 36 ".($y-$height)." 770 $height re f\n";
            foreach($cells as$i=>$lines)foreach($lines as$j=>$line)$stream.=$p->text(41+$width*$i,$y-12-10*$j,$line,7);$y-=$height;$stream.="0.88 0.91 0.94 RG 0.3 w 36 $y m 806 $y l S\n";
        }if(!$rows)$stream.=$p->text(42,442,'Tidak ada data untuk filter yang dipilih.',9);$pages[]=$stream;
        $objects=[null,'<< /Type /Catalog /Pages 2 0 R >>',''];$add=static function(string $s)use(&$objects):int{$objects[]=$s;return count($objects)-1;};$fontId=$add('');$children=[];
        foreach($pages as$i=>$content){$content.=$p->text(36,24,'Copyright oneQay',7).$p->text(700,24,'Halaman '.($i+1).' / '.count($pages),7);$s=$add('<< /Length '.strlen($content).">>\nstream\n".$content."endstream");$children[]=$add('<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 '.$fontId.' 0 R >> >> /Contents '.$s.' 0 R >>');}
        $fontStream=$add('<< /Length '.strlen($p->font).' /Length1 '.strlen($p->font).">>\nstream\n".$p->font."\nendstream");$descriptor=$add('<< /Type /FontDescriptor /FontName /DejaVuSans /Flags 32 /FontBBox [-1021 -463 1793 1232] /ItalicAngle 0 /Ascent 928 /Descent -236 /CapHeight 729 /StemV 80 /FontFile2 '.$fontStream.' 0 R >>');
        $map="\x00\x00";$widths=[];$unicode=[];foreach($p->characters as$c){$map.=pack('n',$c['glyph']);$widths[]=round($p->width($c['char'],1000));$unicode[]=sprintf('<%04X> <%s>',$c['cid'],strtoupper(bin2hex(mb_convert_encoding($c['char'],'UTF-16BE','UTF-8'))));}
        $mapId=$add('<< /Length '.strlen($map).">>\nstream\n".$map."\nendstream");$cid=$add('<< /Type /Font /Subtype /CIDFontType2 /BaseFont /DejaVuSans /CIDSystemInfo << /Registry (Adobe) /Ordering (Identity) /Supplement 0 >> /FontDescriptor '.$descriptor.' 0 R /CIDToGIDMap '.$mapId.' 0 R /W [1 ['.implode(' ',$widths).']] >>');
        $cm="/CIDInit /ProcSet findresource begin\n12 dict begin\nbegincmap\n/CIDSystemInfo << /Registry (Adobe) /Ordering (UCS) /Supplement 0 >> def\n/CMapName /Adobe-Identity-UCS def\n/CMapType 2 def\n1 begincodespacerange\n<0000> <FFFF>\nendcodespacerange\n";foreach(array_chunk($unicode,100)as$chunk)$cm.=count($chunk)." beginbfchar\n".implode("\n",$chunk)."\nendbfchar\n";$cm.="endcmap\nCMapName currentdict /CMap defineresource pop\nend\nend\n";$unicodeId=$add('<< /Length '.strlen($cm).">>\nstream\n".$cm.'endstream');
        $objects[$fontId]='<< /Type /Font /Subtype /Type0 /BaseFont /DejaVuSans /Encoding /Identity-H /DescendantFonts ['.$cid.' 0 R] /ToUnicode '.$unicodeId.' 0 R >>';$objects[2]='<< /Type /Pages /Count '.count($children).' /Kids ['.implode(' ',array_map(static fn($id)=>$id.' 0 R',$children)).'] >>';
        $pdf="%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";$offsets=[0];foreach(array_slice($objects,1,null,true)as$id=>$o){$offsets[] = strlen($pdf);$pdf.="$id 0 obj\n$o\nendobj\n";}$xref=strlen($pdf);$pdf.='xref'."\n0 ".count($objects)."\n0000000000 65535 f \n";foreach(array_slice($offsets,1)as$o)$pdf.=sprintf('%010d 00000 n ',$o)."\n";return $pdf.'trailer << /Size '.count($objects).' /Root 1 0 R >>'."\nstartxref\n$xref\n%%EOF\n";
    }
}
