<?php
declare(strict_types=1);
namespace App\Infrastructure\Business;
use App\Application\Business\{BusinessDocuments,BusinessViolation};
// Author by Lab | zefry.
final class BusinessDocumentCodec implements BusinessDocuments {
    public function importProducts(string $raw,string $extension):array {return SpreadsheetDocument::import($raw,$extension);}
    public function productTemplate():string {return SpreadsheetDocument::xlsx(SpreadsheetDocument::IMPORT_HEADERS,[],'Produk');}
    public function report(array $v,string $format):string {return match($format){'pdf'=>ReportPdf::render($v['title'],$v['pdf_headers']??$v['headers'],$v['pdf_rows']??$v['rows'],$v['meta']),'xlsx'=>SpreadsheetDocument::xlsx($v['headers'],$v['rows'],'oneQay'),default=>throw new BusinessViolation('INVALID_EXPORT_FORMAT')};}
}
