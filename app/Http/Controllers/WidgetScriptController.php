<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class WidgetScriptController extends Controller
{
    /**Опційні покращення

        allowlist доменів: перед віддачею конфігу/або у бутлоадері перевіряй document.referrer і/або заголовок Origin (на API).

        події назовні: додай у віджет postMessage({type:'quiz:event', name:'start'|...}) і обробляй у бутлоадері для інтеграції з GA/GTAG клієнта.

        темізація параметрами: підтримай &theme=dark&lang=uk → прокидай у iframe.src і враховуй у App.vue.

        безпека postMessage: у віджеті заміни '*' на точний домен сторінки клієнта (можеш передати parentOrigin як параметр).

        версіонування: завжди став &v=... у <script> і додавай його у iframe.src — це зручно для гарячих фіксів.*/
    public function script(Request $request, string $uuid): Response
    {
        // 1) Перевірити UUID + завантажити квіз
        if (!Str::isUuid($uuid)) {
            return $this->jsError(
                "Invalid widget UUID format.",
                400
            );
        }

        $quiz = Quiz::query()
            ->select(['id','uuid','title','is_active']) // підлаштуй під свої поля
            ->where('uuid', $uuid)
            ->first();

        if (!$quiz) {
            return $this->jsError(
                "Quiz not found for UUID: {$uuid}",
                404
            );
        }

        // (опціонально) доступність/публічність
        if (property_exists($quiz, 'is_active') && !$quiz->is_active) {
            return $this->jsError(
                "Quiz is not published or inactive.",
                403
            );
        }

        // 2) Підготуємо конфіг для бутлоадера
        $appUrl   = rtrim(config('app.url'), '/');        // напр. https://app.example.com
        $ver      = $request->query('v', '');              // версіонування
        $theme    = $request->query('theme');              // прокинемо у iFrame (опц.)
        $lang     = $request->query('lang');               // прокинемо у iFrame (опц.)
        $parent   = $request->headers->get('Origin') ?: ''; // може бути порожнім для <script>
        $refHost  = parse_url($request->headers->get('Referer', ''), PHP_URL_HOST) ?: '';

        // allowlist з БД (рядок через кому або json/array — підлаштуй)
        $allowlist = $this->normalizeAllowlist($quiz->domain_allowlist ?? null);

        $jsonAllow = json_encode($allowlist, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        $jsonCfg   = json_encode([
            'uuid'   => $quiz->uuid,
            'v'      => $ver,
            'theme'  => $theme,
            'lang'   => $lang,
        ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

        // 3) Видаємо JS-скрипт (self-contained)
        $js = <<<JS
(function(){
  var S = document.currentScript || (function(){var a=document.getElementsByTagName('script');return a[a.length-1]})();
  var base = {$this->jsString($appUrl)};
  var cfg  = {$jsonCfg};
  var allow = {$jsonAllow}; // масив дозволених доменів або []

  // Перевірка домену на клієнті (fallback). Origin у <script> може бути порожнім, тому беремо location.hostname
  try {
    var host = (window.location && window.location.hostname) ? window.location.hostname : '';
    if (Array.isArray(allow) && allow.length > 0) {
      var ok = allow.some(function(d){
        // підтримка піддоменів типу .example.com
        if (d[0] === '.') {
          return host === d.slice(1) || host.endsWith(d);
        }
        return host === d;
      });
      if (!ok) {
        console.warn('[Rocket Quiz] Host "'+host+'" is not in allowlist. Widget will not load.');
        return;
      }
    }
  } catch(e) {
    console.warn('[Rocket Quiz] Allowlist check failed:', e);
  }

  // контейнер поруч зі скриптом
  var wrap = document.createElement('div');
  wrap.className = 'rq-embed';
  wrap.style.display = 'block';
  wrap.style.width = '100%';
  S.parentNode.insertBefore(wrap, S);

  // IFRAME (зі збиранням query)
  var iframe = document.createElement('iframe');
  var q = new URLSearchParams();

  q.set('uuid', cfg.uuid);
  if (cfg.v)     q.set('v', cfg.v);
  if (cfg.theme) q.set('theme', cfg.theme);
  if (cfg.lang)  q.set('lang', cfg.lang);

  // передамо origin сторінки для безпечного postMessage (не обов'язково)
  try { q.set('parentOrigin', window.location.origin); } catch(e) {}

  iframe.src = base + '/embed/index.html?' + q.toString();
  iframe.allow = 'clipboard-read; clipboard-write';
  iframe.style.width = '100%';
  iframe.style.border = '0';
  iframe.style.display = 'block';
  iframe.setAttribute('title', 'Rocket Quiz');
  wrap.appendChild(iframe);

  // авто-рісайз висоти
  function onMsg(e){
    if (e.origin !== base) return; // приймаємо повідомлення тільки від нашого домену
    var d = e.data || {};
    if (d && d.type === 'size' && typeof d.height === 'number') {
      iframe.style.height = Math.ceil(d.height) + 'px';
    }
    // (опц.) ретранслювати зовнішні події хост-сторінці
    // if (d && d.type === 'quiz:event') { window.dispatchEvent(new CustomEvent('rocket-quiz', { detail: d })); }
  }
  window.addEventListener('message', onMsg);
})();
JS;

        return (new Response($js, 200))
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('X-Content-Type-Options', 'nosniff')
            // кешуй сміливо; інвалідунь через ?v=...
            ->header('Cache-Control', 'public, max-age=86400, immutable')
            ->header('Access-Control-Allow-Origin', '*');
    }

    private function jsError(string $message, int $status): Response
    {
        $js = <<<JS
(function(){
  console.warn('%c[Rocket Quiz]%c {$this->escapeForJs($message)}', 'color:#0ea5e9;font-weight:700;', '');
})();
JS;
        return (new Response($js, $status))
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Access-Control-Allow-Origin', '*');
    }

    private function normalizeAllowlist($raw): array
    {
        // Підлаштуй під схему зберігання:
        // - string "example.com, .client.com"
        // - json ["example.com",".client.com"]
        // - null
        if (is_array($raw)) {
            return array_values(array_filter(array_map('trim', $raw)));
        }
        if (is_string($raw) && $raw !== '') {
            // спробуємо json
            $json = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                return array_values(array_filter(array_map('trim', $json)));
            }
            // інакше це список через кому
            return array_values(array_filter(array_map('trim', explode(',', $raw))));
        }
        return []; // пустий = дозволити всюди
    }

    private function jsString(string $s): string
    {
        return json_encode($s, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }

    private function escapeForJs(string $s): string
    {
        return str_replace(['\\', "\n", "\r", "'"], ['\\\\', '\\n', '\\r', "\\'"], $s);
    }
}
