// Author by Lab | zefry. Uncertain writes retry only the same operation and payload.
import { ref } from 'vue';
import { t } from './i18n';
export function useBusinessMutation() {
    const busy=ref(false),uncertain=ref(false),error=ref('');
    let pending: {url:string;body:{operation_id:string;data:unknown}}|null=null;
    function clear():void {if(busy.value||uncertain.value)return;pending=null;error.value='';}
    async function submit(url:string,data:unknown):Promise<any|null> {
        if(busy.value)return null;if(!pending)pending={url,body:{operation_id:`business:${crypto.randomUUID()}`,data:JSON.parse(JSON.stringify(data))}};
        busy.value=true;error.value='';const controller=new AbortController();const timer=setTimeout(()=>controller.abort(),30000);
        try {
            const token=document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;if(!token)throw new Error();
            const response=await fetch(pending.url,{method:'POST',credentials:'same-origin',redirect:'error',signal:controller.signal,headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':token},body:JSON.stringify(pending.body)});
            const v=await response.json();if(!response.ok){if(response.status>=500)uncertain.value=true;else{uncertain.value=false;pending=null;}error.value=t(v.error?.code??v.code??'Could not save. Please check the fields.');return null;}
            uncertain.value=false;pending=null;return v.record;
        }catch{uncertain.value=true;error.value=t('The result is not confirmed. Retry the same request to avoid duplicate records.');return null;}
        finally{clearTimeout(timer);busy.value=false;}
    }
    return {busy,uncertain,error,submit,clear};
}
