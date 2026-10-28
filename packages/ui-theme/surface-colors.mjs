// Author by Lab | zefry. Owner instruction, 28 October 2026:
// white surfaces use Black Onyx; colored surfaces use white text.
const ONYX = '#202020', WHITE = '#FFFFFF', NAVY = '#1B1B39', BLUE = '#3965FA';
const colors = /#[\da-f]{3,8}\b|rgba?\([^)]*\)|hsla?\([^)]*\)|\b(?:white|black|navy|blue|gray|grey|red|green|orange|yellow)\b/gi;
function normalized(color) {
  const value = color.toUpperCase();
  if (value === 'WHITE' || value === '#FFF' || value === WHITE) return WHITE;
  if (value === BLUE || value === NAVY) return value;
  if (/^#[\dA-F]{6}(?:[\dA-F]{2})?$/.test(value)) {
    const rgb = [1,3,5].map(n => parseInt(value.slice(n,n+2),16));
    // Pale surfaces become white, so white labels never sit on a pale fill.
    if (rgb.every(v => v >= 140)) return WHITE;
  }
  return NAVY;
}
export function surfaceColors() {
  return {
    postcssPlugin: 'oneqay-owner-surface-colors',
    Once(root) {
      const variables = new Map(Object.entries({'--oq-navy':NAVY,'--oq-primary':BLUE,'--oq-soft':'#99B7FC','--oq-pale':'#E9EBFF','--oq-white':WHITE,'--oq-light':'#EDEDED','--oq-dark':ONYX,'--oq-bg':WHITE,'--oq-panel':WHITE,'--oq-ink':ONYX,'--oq-muted':NAVY,'--oq-brand':BLUE,'--oq-brand-dark':NAVY,'--oq-brand-soft':'#E9EBFF','--oq-sidebar':WHITE}));
      root.walkDecls(/^--/, d => { if (!d.prop.startsWith('--oq-surface')) variables.set(d.prop,d.value); });
      function resolve(value, depth=0) {
        if (depth > 10) return value;
        return value.replace(/var\((--[\w-]+)(?:,\s*([^()]+))?\)/g,(match,key,fallback)=>{
          const replacement = variables.get(key) ?? fallback;
          return replacement === undefined ? match : resolve(replacement,depth+1);
        });
      }
      root.walkRules(rule => {
        let background;
        rule.each(d => {
          if (d.type !== 'decl') return;
          if (['background','background-color','background-image'].includes(d.prop)) {
            const resolved = resolve(d.value);
            const found = resolved.match(colors);
            if (found?.length) {
              const mapped = found.map(normalized);
              const ink = mapped.every(c => c === WHITE) ? ONYX : WHITE;
              // Keep images and layout keywords; normalize only actual color tokens.
              d.value = resolved.replace(colors,c => ink === WHITE && normalized(c) === WHITE ? NAVY : normalized(c));
              if (!background || d.important || !background.important) background = {ink,important:d.important};
            } else if (/^(?:none|initial|unset)$/.test(resolved.trim()) && d.prop !== 'background-image') {
              background = {ink:'inherit',important:d.important};
            }
          }
          if (d.prop === 'color' || d.prop === '-webkit-text-fill-color') d.value = 'var(--oq-surface-ink, #202020)';
        });
        if (background) rule.append({prop:'--oq-surface-ink',value:background.ink,important:background.important});
      });
      root.append({selector:':where(body, body *)',nodes:[{prop:'color',value:'var(--oq-surface-ink, #202020)',important:true}]});
      root.append({selector:':where(button, a, input, select, textarea)',nodes:[{prop:'transition-property',value:'transform, box-shadow, opacity, border-color',important:true}]});
      root.append({selector:':where(input, textarea)::placeholder',nodes:[{prop:'color',value:'var(--oq-surface-ink, #202020)',important:true},{prop:'opacity',value:'0.75'}]});
      root.walk(node => { node.source ??= root.source; });
    },
  };
}
