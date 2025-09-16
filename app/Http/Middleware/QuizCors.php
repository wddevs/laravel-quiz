<?php

// app/Http/Middleware/QuizCors.php
namespace App\Http\Middleware;

use App\Models\Quiz;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuizCors
{
    public function handle(Request $request, Closure $next)
    {


        $uuid = $request->route('uuid');

        $quiz = $uuid ? Quiz::select('uuid','domain_allowlist')->where('uuid',$uuid)->first() : null;
        if (!$quiz) return $next($request);

        $origin = $request->headers->get('Origin');               // є лише при CORS
        $apiHost = Str::lower($request->getHost());



        if ($origin) {
            $originHost = Str::lower(parse_url($origin, PHP_URL_HOST) ?: '');



            // ✅ SAME-ORIGIN: завжди дозволяємо, без allowlist
            if ($originHost === $apiHost) {
                if ($request->isMethod('OPTIONS')) {
                    return response('', 204)->withHeaders($this->preflightHeaders($origin));
                }
                $resp = $next($request);
                // (можемо й не ставити, але це безпечно)
                foreach ($this->corsHeaders($origin) as $k=>$v) $resp->headers->set($k,$v);
                return $resp;
            }



            // 🔒 CROSS-ORIGIN: перевіряємо allowlist квіза
            $allowlist = $this->normalize($quiz->domain_allowlist);
            if (!$this->hostAllowed($originHost, $allowlist)) {
                return $this->deny($request, $origin);
            }

            if ($request->isMethod('OPTIONS')) {
                return response('', 204)->withHeaders($this->preflightHeaders($origin));
            }

            $resp = $next($request);
            foreach ($this->corsHeaders($origin) as $k=>$v) $resp->headers->set($k,$v);
            return $resp;
        }

        // ❄️ Нема Origin (same-origin або legacy): нічого не ставимо, але можемо
        // додатково перевірити вбудований домен з body/query (project/parentOrigin), якщо хочеш
        return $next($request);
    }

    private function corsHeaders(string $origin): array
    {
        return [
            'Access-Control-Allow-Origin'      => $origin,
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Expose-Headers'    => 'ETag',
            'Vary'                             => 'Origin',
        ];
    }

    private function preflightHeaders(string $origin): array
    {
        return $this->corsHeaders($origin) + [
                'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, If-None-Match',
                'Access-Control-Max-Age'       => '600',
            ];
    }

    private function deny(Request $r, string $origin)
    {
        if ($r->isMethod('OPTIONS')) {
            return response('Origin not allowed', 403)->withHeaders($this->preflightHeaders($origin));
        }
        return response('Origin not allowed', 403)->withHeaders($this->corsHeaders($origin));
    }

    private function normalize($raw): array
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
        if($host === '') return false;
        if($host == 'localhost' || Str::startsWith($host, 'localhost:')) return true;
        foreach ($allow as $p) {
            $p = Str::lower(trim($p));
            $hostL = Str::lower($host);
            if ($p === '') continue;

            if (Str::startsWith($p, '*.')) {
                $suffix = substr($p, 1); // ".example.com"
                if (Str::endsWith($hostL, $suffix) && $hostL !== ltrim($suffix, '.')) return true;
            } elseif ($hostL === $p) {
                return true;
            }
        }
        return false;
    }
}
