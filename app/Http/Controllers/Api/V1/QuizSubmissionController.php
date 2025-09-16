<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\BlockedIp;
use App\Http\Requests\StoreQuizSubmissionRequest;
use App\Services\Analytics\WidgetAnalytics;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class QuizSubmissionController extends Controller
{
    public function store(string $uuid, StoreQuizSubmissionRequest $r, WidgetAnalytics $wa)
    {
        $quiz = Quiz::where('uuid',$uuid)->where('is_active',true)->firstOrFail();

        // блок IP
        $isBlocked = BlockedIp::where('ip', $r->ip())
            ->where(fn($q)=>$q->whereNull('quiz_id')->orWhere('quiz_id',$quiz->id))
            ->exists();
        if ($isBlocked) return response()->json(['success'=>false,'blocked'=>true], 429);

        $v = $r->validated();

        // GeoIP (з кешем усередині пакета)
        $country = null;
        $city = null;

        $ip = $r->ip();
        $geo = Cache::remember("geoip:$ip", 86400, function () use ($ip) {
            // ip-api.com: без ключа, але rate-limit; повертає { status, country, city, ... }
            $res = Http::timeout(2)->retry(1, 200)
                ->get("http://ip-api.com/json/{$ip}?fields=status,message,country,city");
            $json = $res->json();
            return ($json['status'] ?? '') === 'success' ? $json : [];
        });

        $country = $geo['country'] ?? null;
        $city    = $geo['city'] ?? null;

        $s = QuizSubmission::create([
            'quiz_id' => $quiz->id,
            'uuid'    => (string) Str::uuid(),
            'status'  => 'new','paid'=>false,'viewed_at'=>null,
            'contact_name'  => data_get($v,'contacts.name'),
            'contact_phone' => data_get($v,'contacts.phone'),
            'contact_email' => data_get($v,'contacts.email'),
            'contact_text'  => data_get($v,'contacts.text'),
            'ip'        => $r->ip(),
            'referrer'  => $r->headers->get('referer'),
            'source_url'=> data_get($v,'extra.href'),
            'country'   => $country,
            'city'      => $city,
            'discount_percent' => data_get($v,'extra.discount'),
            'answers'   => $v['answers'],
            'extra'     => $v['extra'] ?? null,
            'result'    => $v['result'] ?? null,
        ]);

        // лічильник "leads"
        $domain = parse_url($s->source_url ?? '', PHP_URL_HOST);

        if (data_get($v, 'extra.visitor')) {
            $wa->hit($quiz, 'lead', $domain, data_get($v, 'extra.visitor'), $s->source_url);
        }

        return response()->json(['success'=>true,'id'=>$s->uuid], 201);
    }
}
