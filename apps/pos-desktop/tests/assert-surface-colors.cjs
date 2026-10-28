// Author by Lab | zefry. Check the owner's actual rendered text/surface contract.
module.exports=async function assertSurfaceColors(page){
 const violations=await page.evaluate(()=>{
  const failures=[];
  for(const element of document.querySelectorAll('body *')){
   if(!element.getClientRects().length||getComputedStyle(element).visibility==='hidden')continue;
   const hasText=[...element.childNodes].some(n=>n.nodeType===Node.TEXT_NODE&&n.textContent.trim());
   if(!hasText&&!element.matches('input,select,textarea'))continue;
   let surface='white';
   for(let parent=element;parent;parent=parent.parentElement){
    const style=getComputedStyle(parent);
    if(style.backgroundImage!=='none'){const colors=[...style.backgroundImage.matchAll(/rgb\((\d+), (\d+), (\d+)\)/g)];surface=colors.length&&colors.every(c=>c.slice(1).every(v=>Number(v)===255))?'white':'colored';break;}
    const values=style.backgroundColor.match(/[\d.]+/g)?.map(Number)??[];
    if(values.length>=3&&(values[3]??1)>0){surface=values.slice(0,3).every(v=>v===255)?'white':'colored';break;}
   }
   const expected=surface==='white'?'rgb(32, 32, 32)':'rgb(255, 255, 255)';
   const actual=getComputedStyle(element).color;
   if(actual!==expected)failures.push({tag:element.tagName,class:element.className,text:element.textContent.slice(0,60),surface,actual,expected,background:getComputedStyle(element).backgroundColor,ink:getComputedStyle(element).getPropertyValue('--oq-surface-ink')});
  }
  return failures.slice(0,20);
 });
 if(violations.length)throw Error('SURFACE_TEXT_CONTRACT '+JSON.stringify(violations));
};
