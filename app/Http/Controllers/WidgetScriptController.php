<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class WidgetScriptController extends Controller
{
    public function script(Request $request, string $uuid): Response
    {
        if (!Str::isUuid($uuid)) return $this->jsError("Invalid widget UUID format.", 400);

        $quiz = Quiz::select(['id','uuid','title','is_active','domain_allowlist'])->where('uuid',$uuid)->first();
        if (!$quiz) return $this->jsError("Quiz not found for UUID: {$uuid}", 404);
        if (!$quiz->is_active) return $this->jsError("Quiz is not published or inactive.", 403);

        $appUrl  = rtrim(config('app.url'), '/');                         // домен з iFrame /embed
        $apiBase = rtrim(config('services.api.url') ?? $appUrl, '/');     // API домен (може = appUrl)
        $ver     = $request->query('v', '');
        $theme   = $request->query('theme');
        $lang    = $request->query('lang');
        $refHost = parse_url($request->headers->get('Referer', ''), PHP_URL_HOST) ?: '';

        $allowlist = $this->normalizeAllowlist($quiz->domain_allowlist);



        // server-side allowlist
        if (!empty($allowlist) && !$this->hostAllowed($refHost, $allowlist)) {
            return $this->jsError("Host '{$refHost}' is not allowed for this widget.", 403);
        }

        $jsonAllow = json_encode($allowlist, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        $jsonCfg   = json_encode([
            'uuid'=>$quiz->uuid,'v'=>$ver,'theme'=>$theme,'lang'=>$lang
        ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

        $js = <<<JS
(function(){
  var S = document.currentScript || (function(){var a=document.getElementsByTagName('script');return a[a.length-1]})();
  var base = {$this->jsString($appUrl)};
  var api  = {$this->jsString($apiBase)};
  var cfg  = {$jsonCfg};
  var allow = {$jsonAllow};
  var refHost = {$this->jsString($refHost)};

  function getVid(){ try{ var k='rq_vid',v=localStorage.getItem(k); if(!v){ v=(crypto.randomUUID?crypto.randomUUID():String(Date.now())+Math.random()); localStorage.setItem(k,v); } return v; }catch(e){ return 'anon-'+Date.now(); } }
  function getSid(){ try{ var k='rq_sid',v=sessionStorage.getItem(k); if(!v){ v=(crypto.randomUUID?crypto.randomUUID():String(Date.now())+Math.random()); sessionStorage.setItem(k,v); } return v; }catch(e){ return 'sess-'+Date.now(); } }
  var VID = getVid();
  var SID = getSid();

  try {
    var host = (window.location && window.location.hostname) ? window.location.hostname : '';
    if (Array.isArray(allow) && allow.length > 0) {
      var ok = allow.some(function(d){
        if (!d) return false;
        if (d[0] === '.') { return host === d.slice(1) || host.endsWith(d); }
        return host === d;
      });
      if (!ok) { console.warn('[Rocket Quiz] Host \"'+host+'\" is not in allowlist. Widget will not load.'); return; }
    }
  } catch(e) { console.warn('[Rocket Quiz] Allowlist check failed:', e); }

  // container
  var wrap = document.createElement('div');
  wrap.className='rq-embed'; wrap.style.display='block'; wrap.style.width='100%';
  S.parentNode.insertBefore(wrap, S);

  function sendEvt(type){
    try{
      var body = JSON.stringify({ type:type, vid:VID, sid:SID, page:location.href, project:location.host });
      var url = api + '/api/v1/quizzes/' + cfg.uuid + '/events';
      if (navigator.sendBeacon) navigator.sendBeacon(url, new Blob([body], {type:'application/json'}));
      else fetch(url, {method:'POST', headers:{'Content-Type':'application/json'}, body:body, keepalive:true});
    }catch(e){
        console.log('sendEvt', e)
    }
  }

  // iframe
  var iframe = document.createElement('iframe');
  var q = new URLSearchParams();
  q.set('uuid', cfg.uuid);
  if (cfg.v)     q.set('v', cfg.v);
  if (cfg.theme) q.set('theme', cfg.theme);
  if (cfg.lang)  q.set('lang', cfg.lang);
  try { q.set('parentOrigin', window.location.origin); } catch(e) {}
  q.set('vid', VID); q.set('sid', SID); q.set('project', location.host); q.set('page', location.href);

  iframe.src = base + '/embed/index.html?' + q.toString();
  iframe.allow='clipboard-read; clipboard-write';
  iframe.style.width='100%'; iframe.style.border='0'; iframe.style.display='block';
  iframe.setAttribute('title','Rocket Quiz');
  wrap.appendChild(iframe);

  // impression once (>=50% visible) + fallback
  (function(){
    var fired = false;
    function fire(){ if (fired) return; fired = true; sendEvt('impression'); }
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function(entries){
        entries.forEach(function(e){ if (e.isIntersecting && e.intersectionRatio >= 0.5) { fire(); io.disconnect(); }});
      }, { threshold:[0.5] });
      io.observe(iframe);
      setTimeout(fire, 5000);
    } else { fire(); }
  })();

  function onMsg(e){
    if (e.origin !== base) return;
    var d = e.data || {};
    if (d && d.type === 'size' && typeof d.height === 'number') {
      iframe.style.height = Math.ceil(d.height) + 'px';
    }
    if (d && d.type === 'quiz:event') {
      if (d.name === 'open') sendEvt('open');
      if (d.name === 'lead') sendEvt('lead');
    }
  }
  window.addEventListener('message', onMsg);
})();
JS;

        return (new Response($js, 200))
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Cache-Control', 'public, max-age=86400, immutable')
            ->header('Access-Control-Allow-Origin', '*');
    }

    private function jsError(string $message, int $status): Response
    {
        $js = "(function(){console.warn('%c[Rocket Quiz]%c ". $this->escapeForJs($message) ."','color:#0ea5e9;font-weight:700;','');})();";
        return (new Response($js, $status))
            ->header('Content-Type','application/javascript; charset=UTF-8')
            ->header('X-Content-Type-Options','nosniff')
            ->header('Cache-Control','no-store, no-cache, must-revalidate')
            ->header('Access-Control-Allow-Origin','*');
    }

    private function normalizeAllowlist($raw): array
    {
        if (is_array($raw)) return array_values(array_filter(array_map('trim',$raw)));
        if (is_string($raw) && $raw !== '') {
            $json = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                return array_values(array_filter(array_map('trim',$json)));
            }
            return array_values(array_filter(array_map('trim', explode(',', $raw))));
        }
        return [];
    }

    private function hostAllowed(string $host, array $allow): bool
    {
        if ($host === '') return false;
        foreach ($allow as $d) {
            $d = trim($d); if ($d === '') continue;
            if ($d[0] === '.') { if ($host === substr($d,1) || str_ends_with($host, $d)) return true; }
            else { if ($host === $d) return true; }
        }
        return false;
    }

    private function jsString(string $s): string
    { return json_encode($s, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); }

    private function escapeForJs(string $s): string
    { return str_replace(['\\',"\n","\r","'"], ['\\\\','\\n','\\r',"\\'"], $s); }
}
