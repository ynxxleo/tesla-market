const stocks = [
  {symbol:'TSLA',name:'Tesla',price:445.91,change:1.42,base:439.67,color:'#ef3f42'},
  {symbol:'NVDA',name:'NVIDIA',price:182.12,change:0.87,base:180.55,color:'#78d5a6'},
  {symbol:'AAPL',name:'Apple',price:226.94,change:-0.31,base:227.65,color:'#b8b8bf'},
  {symbol:'MSFT',name:'Microsoft',price:527.75,change:0.54,base:524.91,color:'#6ba4ff'},
  {symbol:'AMZN',name:'Amazon',price:222.69,change:-0.18,base:223.09,color:'#f4a64d'}
];
let selected = stocks[0], side = 'Buy', range = '1D', series = [];
const $ = s => document.querySelector(s); const $$ = s => [...document.querySelectorAll(s)];
const money = n => '$' + Number(n).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
function seedSeries(stock, points=48){let v=stock.base; const out=[]; const target=stock.price; for(let i=0;i<points;i++){const pull=(target-v)/(points-i); v+=pull+(Math.sin(i*.74+stock.symbol.length)*.65)+(Math.random()-.5)*.95; out.push(v)} out[out.length-1]=target; return out}
function sparkline(values,color){const min=Math.min(...values),max=Math.max(...values),d=values.map((v,i)=>`${i?'L':'M'}${i/(values.length-1)*62},${22-(v-min)/(max-min||1)*18}`).join(' ');return `<svg class="spark" viewBox="0 0 62 24"><path d="${d}" fill="none" stroke="${color}" stroke-width="1.4"/></svg>`}
function renderWatchlist(){ $('#watchlist').innerHTML=stocks.map(s=>{const vals=seedSeries(s,16);return `<div class="watch-item ${s.symbol===selected.symbol?'active':''}" data-symbol="${s.symbol}"><div class="watch-icon">${s.symbol[0]}</div><div class="watch-meta"><b>${s.symbol}</b><span>${s.name}</span></div>${sparkline(vals,s.change>=0?'#6de0a0':'#ef3f42')}<div class="watch-price"><b>${money(s.price)}</b><span class="${s.change>=0?'positive':'negative'}">${s.change>=0?'+':''}${s.change.toFixed(2)}%</span></div></div>`}).join(''); $$('.watch-item').forEach(el=>el.onclick=()=>selectStock(el.dataset.symbol)) }
function renderQuote(){const pos=selected.change>=0,delta=selected.price*selected.change/(100+selected.change); $('#tickerLogo').textContent=selected.symbol[0]; $('#companyName').textContent=selected.name+(selected.symbol==='TSLA'?', Inc.':''); $('#tickerName').textContent=selected.symbol; $('#mainPrice').textContent=money(selected.price); $('#mainChange').className=`quote-change ${pos?'positive':'negative'}`; $('#mainChange').innerHTML=`${pos?'+':''}${money(delta).replace('$','$')} <span>(${pos?'+':''}${selected.change.toFixed(2)}%)</span> today`; $('#openValue').textContent=money(selected.base); $('#highValue').textContent=money(Math.max(...series)); $('#volumeValue').textContent= selected.symbol==='TSLA'?'72.8M':(18+Math.random()*50).toFixed(1)+'M'; $('#chartTicker').textContent=`${selected.symbol} performance`; $('#chartReturn').textContent=`${pos?'+':''}${selected.change.toFixed(2)}%`; $('#chartReturn').style.color=pos?'var(--green)':'var(--red)'; $('#reviewText').textContent=`Review ${selected.symbol} ${side.toLowerCase()}`; updateEstimate(); renderChart() }
function renderChart(){const w=800,h=205,pad=12,min=Math.min(...series),max=Math.max(...series),pts=series.map((v,i)=>[i/(series.length-1)*w,pad+(max-v)/(max-min||1)*(h-pad*2)]);const d=pts.map((p,i)=>`${i?'L':'M'} ${p[0].toFixed(1)} ${p[1].toFixed(1)}`).join(' '); const line=$('#linePath');line.setAttribute('d',d);line.style.stroke=selected.change>=0?'#6de0a0':'#ef3f42';line.style.animation='none';requestAnimationFrame(()=>{line.style.animation='draw 1.2s forwards'});$('#areaPath').setAttribute('d',`${d} L 800 220 L 0 220 Z`);const end=pts.at(-1);$('#chartDot').setAttribute('cx',end[0]);$('#chartDot').setAttribute('cy',end[1]);$('#chartDot').style.stroke=selected.change>=0?'#6de0a0':'#ef3f42'}
function selectStock(symbol){selected=stocks.find(s=>s.symbol===symbol);series=seedSeries(selected,range==='1D'?48:range==='1W'?60:72);renderWatchlist();renderQuote();fetchQuote(symbol)}
function updateEstimate(){const shares=parseFloat($('#shares').value)||0;$('#estimate').textContent=`≈ ${money(shares*selected.price)}`}
async function fetchQuote(symbol){$('#updatedAt').textContent='Refreshing market data…';try{const interval=range==='1D'?'5m':range==='1W'?'30m':range==='1M'?'1d':'1wk';const period=range==='1D'?'1d':range==='1W'?'5d':range==='1M'?'1mo':'1y';const url=`https://query1.finance.yahoo.com/v8/finance/chart/${symbol}?range=${period}&interval=${interval}`;const res=await fetch(url);if(!res.ok)throw Error('blocked');const result=(await res.json()).chart.result[0],meta=result.meta,closes=result.indicators.quote[0].close.filter(Number.isFinite);if(closes.length>2){series=closes;selected.price=meta.regularMarketPrice;selected.base=meta.chartPreviousClose||meta.previousClose;selected.change=(selected.price-selected.base)/selected.base*100;$('#dataMode').textContent='LIVE';$('#updatedAt').textContent=`Live market quote · ${new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}`;renderWatchlist();renderQuote()}}catch(e){$('#dataMode').textContent='DEMO';$('#updatedAt').textContent='Demo feed · live endpoint unavailable';}}
$$('.order-tabs button').forEach(b=>b.onclick=()=>{side=b.dataset.side;$$('.order-tabs button').forEach(x=>x.classList.toggle('active',x===b));$('#reviewText').textContent=`Review ${selected.symbol} ${side.toLowerCase()}`});
$$('.range-tabs button').forEach(b=>b.onclick=()=>{range=b.dataset.range;$$('.range-tabs button').forEach(x=>x.classList.toggle('active',x===b));series=seedSeries(selected,range==='1D'?48:range==='1W'?60:72);renderChart();fetchQuote(selected.symbol)});
function setView(view, push=true){
  const valid=['trade','markets','portfolio','news']; view=valid.includes(view)?view:'trade';
  document.body.dataset.view=view;
  $$('.nav-link').forEach(link=>link.classList.toggle('active',link.dataset.view===view));
  $$('.page-view').forEach(page=>page.classList.toggle('active',page.id===`${view}View`));
  if(push) history.replaceState(null,'',view==='trade'?'#trade':`#${view}`);
  document.title=`${view[0].toUpperCase()+view.slice(1)} — Tesla Markets`;
}
$$('[data-view]').forEach(link=>link.addEventListener('click',e=>{e.preventDefault();setView(link.dataset.view)}));
const savedTheme=localStorage.getItem('tesla-market-theme');
if(savedTheme==='light'||(!savedTheme&&matchMedia('(prefers-color-scheme: light)').matches))document.body.classList.add('light');
function syncTheme(){const light=document.body.classList.contains('light');$('.theme-icon').textContent=light?'☾':'☼';document.querySelector('meta[name="theme-color"]').content=light?'#eeeeeb':'#050505'}
$('.theme-toggle').onclick=()=>{document.body.classList.toggle('light');localStorage.setItem('tesla-market-theme',document.body.classList.contains('light')?'light':'dark');syncTheme();showToast(`${document.body.classList.contains('light')?'Light':'Dark'} mode enabled`)};
syncTheme();setView(location.hash.slice(1)||'trade',false);window.addEventListener('hashchange',()=>setView(location.hash.slice(1),false));
$('#marketDate').textContent=new Intl.DateTimeFormat('en-US',{weekday:'long',month:'long',day:'numeric'}).format(new Date());
$$('.news-card button').forEach(button=>button.onclick=()=>showToast('Briefing opened in reading mode'));
$('.outline-btn').onclick=()=>showToast('Secure deposit flow ready');
$('#shares').addEventListener('input',updateEstimate);$('.star').onclick=e=>e.currentTarget.classList.toggle('active');
$('#orderForm').onsubmit=e=>{e.preventDefault();showToast(`${side} order ready: ${$('#shares').value} ${selected.symbol} shares`)};
function showToast(msg){const t=$('#toast');t.textContent=msg;t.classList.add('show');clearTimeout(window.toastTimer);window.toastTimer=setTimeout(()=>t.classList.remove('show'),2600)}
function tickClock(){try{$('#nyClock').textContent=new Intl.DateTimeFormat('en-US',{timeZone:'America/New_York',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}).format(new Date())}catch(e){}}setInterval(tickClock,1000);tickClock();
function demoTick(){if($('#dataMode').textContent==='LIVE')return;const drift=(Math.random()-.49)*.6;selected.price=Math.max(1,selected.price+drift);series.push(selected.price);if(series.length>60)series.shift();renderQuote();renderWatchlist()}
const stage=$('.stage'),hero=$('.hero-car');
stage.addEventListener('pointermove',e=>{if(document.body.dataset.view!=='trade'||matchMedia('(prefers-reduced-motion: reduce)').matches)return;const rect=stage.getBoundingClientRect(),x=(e.clientX-rect.left)/rect.width-.5,y=(e.clientY-rect.top)/rect.height-.5;requestAnimationFrame(()=>{hero.style.transform=`scale(1.025) translate(${x*-9}px,${y*-6}px)`})});
stage.addEventListener('pointerleave',()=>{hero.style.transform='scale(1) translate(0,0)'});
series=seedSeries(selected);renderWatchlist();renderQuote();fetchQuote('TSLA');setInterval(demoTick,4000);
