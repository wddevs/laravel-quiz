<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BlockedIp;
use App\Models\Quiz;
use App\Services\Analytics\WidgetAnalytics;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnalyticsEventController extends Controller
{
    public function store(string $uuid, Request $r, WidgetAnalytics $svc)
    {
        $data = $r->validate([
            'type'    => ['required', Rule::in(['impression','open','lead'])],
            'vid'     => ['required','string','max:64'],
            'sid'     => ['nullable','string','max:64'],
            'page'    => ['nullable','string','max:2048'],
            'project' => ['nullable','string','max:255'],
        ]);

        $quiz = Quiz::where('uuid',$uuid)->where('is_active',true)->firstOrFail();

        $blocked = BlockedIp::where('ip',$r->ip())
            ->where(fn($q)=>$q->whereNull('quiz_id')->orWhere('quiz_id',$quiz->id))
            ->exists();
        if ($blocked) return response()->json(['success'=>false,'blocked'=>true],429);


        $counted = $svc->hit($quiz, $data['type'], $data['project'] ?? null, $data['vid'], $data['page'] ?? null);

        return response()->json(['success'=>true,'counted'=>$counted]);
    }
}
