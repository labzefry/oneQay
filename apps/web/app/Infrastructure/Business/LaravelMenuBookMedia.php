<?php
// Author by Lab | zefry. Original upload bytes/names/metadata are never persisted or served.
declare(strict_types=1);
namespace App\Infrastructure\Business;

use App\Application\Business\{BusinessAccess,BusinessInput,BusinessRepository,BusinessViolation};
use Illuminate\Database\Connection;

final readonly class LaravelMenuBookMedia
{
    public const MAX_BYTES = 3 * 1024 * 1024;
    public const QUOTA_BYTES = 20 * 1024 * 1024;
    public const MAX_IMAGES = 100;

    public function __construct(private Connection $db, private BusinessAccess $access, private BusinessRepository $operations) {}

    private function managerScope(): array
    {
        $s = $this->access->require('pos.catalog.prepare');
        return ['tenant_id'=>$s->tenantId()->value(), 'organization_id'=>$s->organizationId()->value(), 'outlet_id'=>$s->outletId()->value()];
    }

    public function store(string $input, string $correlation): array
    {
        $s = $this->access->require('pos.catalog.prepare');
        $scope = $this->managerScope();
        if (!$this->db->getSchemaBuilder()->hasTable('oneqay_menu_book_media')) throw new BusinessViolation('MENU_BOOK_SCHEMA_REQUIRED',503);
        if (strlen($input) < 12 || strlen($input) > self::MAX_BYTES) throw new BusinessViolation('MENU_IMAGE_SIZE_INVALID',422);
        $info = @getimagesizefromstring($input);
        if (!$info || !in_array($info['mime']??'', ['image/jpeg','image/png','image/webp'],true)
            || $info[0] < 1 || $info[1] < 1 || $info[0] > 4000 || $info[1] > 4000 || $info[0]*$info[1] > 8000000) {
            throw new BusinessViolation('MENU_IMAGE_INVALID',422);
        }
        if (!function_exists('imagecreatefromstring') || !function_exists('imagepng') || !function_exists('imagejpeg')) throw new BusinessViolation('MENU_IMAGE_PROCESSING_UNAVAILABLE',503);
        $image = @imagecreatefromstring($input);
        if ($image === false) throw new BusinessViolation('MENU_IMAGE_INVALID',422);
        // Re-encoding strips EXIF, embedded text, appended scripts and user filenames.
        // PNG preserves alpha; WebP is normalized to PNG so transparency is not lost.
        $mime = $info['mime'] === 'image/jpeg' ? 'image/jpeg' : 'image/png';
        ob_start();
        try {
            if ($mime === 'image/png') imagesavealpha($image,true);
            $ok = $mime === 'image/png' ? imagepng($image,null,6) : imagejpeg($image,null,85);
            $bytes = (string)ob_get_contents();
        } finally { ob_end_clean(); imagedestroy($image); }
        if (!$ok || strlen($bytes) > self::MAX_BYTES) throw new BusinessViolation('MENU_IMAGE_SIZE_INVALID',422);
        $hash = hash('sha256',$bytes);$id = substr($hash,0,32);
        return $this->db->transaction(function()use($s,$scope,$hash,$id,$mime,$bytes,$correlation):array {
            // Serialize quota checks across concurrent uploads within this organization.
            $this->db->table('oneqay_organizations')->where('tenant_id',$scope['tenant_id'])->where('id',$scope['organization_id'])->lockForUpdate()->firstOrFail();
            $q = $this->db->table('oneqay_menu_book_media')->where($scope);
            $old = (clone $q)->where('id',$id)->first(['sha256']);
            if ($old && !hash_equals($old->sha256,$hash)) throw new BusinessViolation('MENU_IMAGE_CONFLICT',409);
            $record=['id'=>$id,'mime'=>$mime,'bytes'=>strlen($bytes)];
            if ($old) return $record;
            return $this->operations->once($s,'menu-media:'.bin2hex(random_bytes(16)),'menu.media.upload',['sha256'=>$hash],$correlation,function()use($scope,$q,$id,$hash,$mime,$bytes,$record):array {
                if ((clone $q)->count() >= self::MAX_IMAGES) throw new BusinessViolation('MENU_IMAGE_COUNT_EXCEEDED',409);
                if ((int)(clone $q)->sum('bytes') + strlen($bytes) > self::QUOTA_BYTES) throw new BusinessViolation('MENU_IMAGE_QUOTA_EXCEEDED',409);
                $this->db->table('oneqay_menu_book_media')->insert($scope+['id'=>$id,'sha256'=>$hash,'mime'=>$mime,'bytes'=>strlen($bytes),'payload'=>base64_encode($bytes),'created_at'=>time()]);
                return $record;
            });
        },3);
    }

    public function privateImage(string $id): ?array { return $this->readScoped($this->managerScope(),$id); }

    private function references(array $scope): array
    {
        $row=$this->db->table('oneqay_menu_book_publication')->where($scope)->first(['cover_id','published_snapshot']);
        if (!$row) return [];
        try { $snapshot=$row->published_snapshot===null?[]:json_decode($row->published_snapshot,true,32,JSON_THROW_ON_ERROR); }
        catch (\JsonException) { throw new BusinessViolation('MENU_SNAPSHOT_INVALID',503); }
        if (!is_array($snapshot)) throw new BusinessViolation('MENU_SNAPSHOT_INVALID',503);
        return array_values(array_filter([$row->cover_id,$snapshot['cover_id']??null],fn($v)=>is_string($v)));
    }

    public function listing(): array
    {
        $scope=$this->managerScope();$refs=$this->references($scope);
        $rows=$this->db->table('oneqay_menu_book_media')->where($scope)->orderByDesc('created_at')->orderBy('id')->limit(self::MAX_IMAGES)->get(['id','bytes','created_at']);
        return ['items'=>$rows->map(fn($r)=>['id'=>$r->id,'bytes'=>(int)$r->bytes,'in_use'=>in_array($r->id,$refs,true)])->all(),
            'used_bytes'=>(int)$this->db->table('oneqay_menu_book_media')->where($scope)->sum('bytes'),'quota_bytes'=>self::QUOTA_BYTES];
    }

    public function remove(string $operation,string $id,string $correlation): array
    {
        $s=$this->access->require('pos.catalog.prepare');$scope=$this->managerScope();
        if (!preg_match('/\A[a-f0-9]{32}\z/D',$id)) throw new BusinessViolation('MENU_IMAGE_INVALID',422);
        return $this->operations->once($s,BusinessInput::text($operation,80),'menu.media.remove',['id'=>$id],$correlation,function()use($scope,$id):array {
            $this->db->table('oneqay_organizations')->where('tenant_id',$scope['tenant_id'])->where('id',$scope['organization_id'])->lockForUpdate()->firstOrFail();
            if (in_array($id,$this->references($scope),true)) throw new BusinessViolation('MENU_IMAGE_IN_USE',409);
            $q=$this->db->table('oneqay_menu_book_media')->where($scope)->where('id',$id);
            if (!$q->exists()) throw new BusinessViolation('MENU_IMAGE_NOT_FOUND',404);
            $q->delete();return ['id'=>$id,'deleted'=>true];
        });
    }

    /** Internal only: callers must obtain scope from a verified session/host and enforce publication. */
    public function readScoped(array $scope, string $id): ?array
    {
        if (array_keys($scope)!==['tenant_id','organization_id','outlet_id'] || !preg_match('/\A[a-f0-9]{32}\z/D',$id)) return null;
        if (!$this->db->getSchemaBuilder()->hasTable('oneqay_menu_book_media')) return null;
        $row=$this->db->table('oneqay_menu_book_media')->where($scope)->where('id',$id)->first(['mime','bytes','sha256','payload']);
        if (!$row || !in_array($row->mime,['image/png','image/jpeg'],true) || (int)$row->bytes > self::MAX_BYTES) return null;
        $bytes=base64_decode($row->payload,true);
        if ($bytes===false || strlen($bytes)!==(int)$row->bytes || !hash_equals($row->sha256,hash('sha256',$bytes))) return null;
        return ['bytes'=>$bytes,'mime'=>$row->mime];
    }
}
