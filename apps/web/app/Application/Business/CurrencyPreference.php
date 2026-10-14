<?php
declare(strict_types=1);
namespace App\Application\Business;
// Author by Lab | zefry. Fixed scales; monetary values use integers, never floats.
final class CurrencyPreference
{
    public const OPTIONS = ['IDR'=>['Rp',0], 'USD'=>['$',2], 'EUR'=>['€',2], 'GBP'=>['£',2], 'JPY'=>['¥',0], 'SGD'=>['S$',2], 'MYR'=>['RM',2], 'AUD'=>['A$',2], 'CAD'=>['C$',2], 'CHF'=>['CHF',2], 'CNY'=>['CN¥',2], 'HKD'=>['HK$',2], 'INR'=>['INR',2], 'THB'=>['THB',2], 'PHP'=>['₱',2], 'KRW'=>['KRW',0], 'VND'=>['VND',0], 'AED'=>['AED',2], 'SAR'=>['SAR',2], 'KWD'=>['KWD',3]];
    public static function validate(array $data): array
    {
        BusinessInput::fields($data,['currency','symbol','business_name']);
        $code=BusinessInput::currency($data['currency']);
        if (!isset(self::OPTIONS[$code]) || !in_array($data['symbol'],[self::OPTIONS[$code][0],$code],true)) throw new BusinessViolation('INVALID_CURRENCY');
        return ['currency'=>$code,'symbol'=>$data['symbol'],'scale'=>self::OPTIONS[$code][1],'business_name'=>BusinessInput::text($data['business_name'],160)];
    }
    public static function atomic(mixed $value,int $scale): string
    {
        if (!is_string($value) || strlen($value)>24 || preg_match('/\A(?:0|[1-9][0-9]*)(?:\.([0-9]+))?\z/D',$value,$match)!==1 || strlen($match[1]??'')>$scale) throw new BusinessViolation('INVALID_NUMBER');
        $pieces=explode('.',$value);$n=ltrim($pieces[0].str_pad($pieces[1]??'',$scale,'0'),'0');
        return (string)BusinessInput::integer($n===''?'0':$n);
    }
    public static function decimal(string $value,int $scale): string
    {
        $v=str_pad((string)BusinessInput::integer($value),$scale+1,'0',STR_PAD_LEFT);
        return $scale?substr($v,0,-$scale).'.'.substr($v,-$scale):$v;
    }
}
