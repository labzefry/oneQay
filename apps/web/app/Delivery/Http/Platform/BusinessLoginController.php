<?php
declare(strict_types=1);
namespace App\Delivery\Http\Platform;
use App\Application\Platform\{PlatformStore,BusinessActivation};
use App\Delivery\Http\Identity\{FirstPartySessionController,FirstPartySessionKeys};
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\Request;
use Inertia\Inertia;
// Author by Lab | zefry. Resolves the invitation's server-owned context; canonical login owns authentication.
final readonly class BusinessLoginController
{
    public function __construct(private PlatformStore $store,private FirstPartySessionController $sessions) {}
    public function show(Request $request):mixed
    {
        return Inertia::render('Platform/Login',['mfa_state'=>$request->session()->get(FirstPartySessionKeys::PENDING_MFA_STATE)])->toResponse($request)->withHeaders(['Cache-Control'=>'no-store, private','Referrer-Policy'=>'no-referrer']);
    }
    public function login(Request $request):mixed
    {
        try {
            $input=$request->except('_token');
            if(strlen($request->getContent())>4096 || array_diff(array_keys($input),['email','password','business_id']))throw new \InvalidArgumentException();
            $email=BusinessActivation::email($input['email']??null);$password=$input['password']??null;$id=$input['business_id']??null;
            if(!is_string($password)||strlen($password)<1||strlen($password)>72||($id!==null && !is_string($id)))throw new \InvalidArgumentException();
            $business=$this->store->loginBusiness($email,$id);
            $hostBusiness=$request->attributes->get('oneqay.host_business');if($hostBusiness!==null&&($business['tenant_id']??null)!==$hostBusiness['tenant_id'])throw new \InvalidArgumentException();
            if($business===null)throw new \InvalidArgumentException();
            $payload=array_intersect_key($business,array_flip(['tenant_id','identity_id','organization_id','outlet_id','device_id']));$payload['password']=$password;
            $request->replace($payload);
            return $this->sessions->login($request);
        } catch(\Throwable) {
            return response()->json(SafeErrorEnvelope::make('AUTHENTICATION_FAILED',(string)$request->attributes->get('oneqay.correlation_id','correlation-missing')),401,['Cache-Control'=>'no-store, private']);
        }
    }
}
