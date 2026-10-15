<?php
declare(strict_types=1);
namespace App\Application\Website;
// Author by Lab | zefry
interface WebsiteStore {
    public function draft(string $slug):array;
    public function published(string $slug):?array;
    public function listing():array;
    public function save(string $slug,int $version,array $document):array;
    public function publish(string $slug,int $version,string $digest,string $actor):array;
    public function revisions(string $slug):array;
    public function restore(string $slug,int $version,string $revision):array;
    public function putMedia(string $id,string $mime,string $bytes,string $actor):void;
    public function media(string $id,bool $preview):?array;
    public function hasMedia(string $id):bool;
    public function registration(array $input):void;
    public function registrationDetails(array $ids):array;
}
