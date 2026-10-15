// Author by Lab | zefry
export type Item={title:string;body:string;href:string;icon:string};
export type Block={id:string;type:'hero'|'features'|'split'|'text'|'faq'|'cta'|'pricing';tone:'light'|'soft'|'dark';eyebrow:string;title:string;body:string;image:string;alt:string;button:string;href:string;items:Item[]};
export type Translation={title:string;description:string;blocks:Block[]};
export type SiteDocument={id:Translation;en:Translation};
