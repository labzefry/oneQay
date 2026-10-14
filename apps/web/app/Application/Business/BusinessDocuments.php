<?php
declare(strict_types=1);
namespace App\Application\Business;
// Author by Lab | zefry. File engines remain behind an application port.
interface BusinessDocuments {
    public function importProducts(string $raw,string $extension):array;
    public function productTemplate():string;
    public function report(array $document,string $format):string;
}
