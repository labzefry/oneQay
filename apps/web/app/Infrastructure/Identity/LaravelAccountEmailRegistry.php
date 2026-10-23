<?php
declare(strict_types=1);
namespace App\Infrastructure\Identity;
use App\Application\Platform\PlatformViolation;
use Illuminate\Database\Connection;
// Author by Lab | zefry. Serialize owner and cashier email claims in one database transaction.
final readonly class LaravelAccountEmailRegistry
{
 public function __construct(private Connection $db) {}
 public function lock():void {
  $this->db->table('oneqay_mail_settings')->where('id',1)->lockForUpdate()->firstOrFail();
 }
 public function owner(string $email):?object {return $this->db->table('oneqay_platform_businesses')->whereRaw('LOWER(email) = ?',[strtolower(trim($email))])->lockForUpdate()->first();}
 public function requireUnused(string $email):void {
  $this->lock();$email=strtolower(trim($email));
  if($this->owner($email)||($this->db->getSchemaBuilder()->hasTable('oneqay_desktop_cashiers')&&$this->db->table('oneqay_desktop_cashiers')->where('login',$email)->lockForUpdate()->first()!==null))throw new PlatformViolation('EMAIL_ALREADY_REGISTERED',409);
 }
}
