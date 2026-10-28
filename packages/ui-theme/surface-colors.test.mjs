import {test} from 'node:test';
import assert from 'node:assert/strict';
import {createRequire} from 'node:module';
import {surfaceColors} from './surface-colors.mjs';
const require=createRequire(new URL('../../apps/web/package.json',import.meta.url));
const postcss=require('postcss');
async function css(input){return (await postcss([surfaceColors()]).process(input,{from:'owner-theme.css'})).css;}
test('white surfaces use Black Onyx and blue surfaces use white',async()=>{
 const result=await css('.card{background:#FFFFFF;color:blue}.action{background:#3965FA;color:black}');
 assert.match(result,/\.card\{[^}]*--oq-surface-ink:#202020/);
 assert.match(result,/\.action\{[^}]*--oq-surface-ink:#FFFFFF/);
});
test('pale fills become white instead of unreadable white text on pale blue',async()=>{
 const result=await css('.card{background:#E9EBFF;color:#1B1B39}');
 assert.match(result,/background:#FFFFFF/);assert.match(result,/--oq-surface-ink:#202020/);
});
test('shared semantic variables resolve even in separately compiled Vue styles',async()=>{
 const result=await css('.button{background:var(--oq-primary)}.input{background:var(--oq-white)}');
 assert.match(result,/\.button\{[^}]*--oq-surface-ink:#FFFFFF/);
 assert.match(result,/\.input\{[^}]*--oq-surface-ink:#202020/);
});
test('transparent children inherit surface ink and colored gradients stay legible',async()=>{
 const result=await css('.hero{background:linear-gradient(#3965FA,#FFFFFF)}.child{background:transparent;color:navy}');
 assert.match(result,/linear-gradient\(#3965FA,#1B1B39\)/);
 assert.doesNotMatch(result,/\.child\{[^}]*--oq-surface-ink:/);
});
