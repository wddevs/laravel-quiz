<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\QuizSubmission;
use App\Models\Quiz;
use App\Models\BlockedIp;
use App\Http\Resources\QuizSubmissionResource;

class LeadController extends Controller
{
    public function index(Request $r)
    {
        $user = $r->user();

        // 1) Валідні фільтри з дефолтами
        $filters = $r->validate([
            'status'  => 'nullable|in:all,new,updated,error,viewed',
            'quiz_id' => 'nullable|integer|exists:quizzes,id',
            'city'    => 'nullable|string',
            'time'    => 'nullable|in:all,today,7d,30d',
        ]);

        $status = $filters['status']  ?? 'all';
        $quizId = $filters['quiz_id'] ?? null;
        $city   = filled($filters['city'] ?? null) ? trim($filters['city']) : null;
        $time   = $filters['time']    ?? 'all';

        // 2) Базова вибірка з урахуванням власника (multi-tenant)
        $base = \App\Models\QuizSubmission::query()
            ->with('quiz')
            ->when(!$user->hasRole('superadmin'), fn($q) =>
            $q->whereHas('quiz', fn($qq) => $qq->where('user_id', $user->id))
            );



        // 3) Спільні фільтри (і для списку, і для статистики)
        $filtered = (clone $base)
            ->when(isset($quizId), fn($q) => $q->where('quiz_id', $quizId))
            ->when(filled($city),   fn($q) => $q->where('city', 'like', "%{$city}%"));



        // 3.1 Часовий фільтр – через діапазон дат у твоєму TZ (Europe/Warsaw) з конвертацією в UTC
        [$from, $to] = $this->timeRangeUtc($time); // див. метод нижче
        if ($from && $to) {
            $filtered->whereBetween('created_at', [$from, $to]);
        }



        // 4) Лістинг (додаємо статус тільки тут)
        $list = (clone $filtered)
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->latest();

        $leads = $list->paginate(20)->withQueryString();

        // 5) Статистика по тій самій базі (без статусу)
        $stats = [
            'total' => (clone $filtered)->count(),
            'new'   => (clone $filtered)->where('status', 'new')->count(),
        ];

        // 6) Квізи в селекті – теж тільки свої (крім superadmin)
        $quizzes = \App\Models\Quiz::query()
            ->select('id', 'title')
            ->when(!$user->hasRole('superadmin'), fn($q) => $q->where('user_id', $user->id))
            ->orderBy('title')
            ->get();

        return \Inertia\Inertia::render('Leads/Index', [
            'filters' => [
                'status'  => $status,
                'quiz_id' => $quizId,
                'city'    => $city,
                'time'    => $time,
            ],
            'stats'   => $stats,
            'quizzes' => $quizzes,
            'leads'   => QuizSubmissionResource::collection($leads),
        ]);
    }

    private function timeRangeUtc(string $time): array
    {
        $tz = 'Europe/Warsaw'; // або config('app.dashboard_tz', 'Europe/Warsaw')

        return match ($time) {
            'today' => [
                now($tz)->startOfDay()->utc(),
                now($tz)->endOfDay()->utc(),
            ],
            '7d' => [
                now($tz)->subDays(7)->startOfDay()->utc(),
                now($tz)->endOfDay()->utc(),
            ],
            '30d' => [
                now($tz)->subDays(30)->startOfDay()->utc(),
                now($tz)->endOfDay()->utc(),
            ],
            default => [null, null],
        };
    }

    public function show(string $uuid)
    {
        $lead = QuizSubmission::with('quiz')->where('uuid',$uuid)->firstOrFail();
        if ($lead->status === 'new') {
            $lead->update([
                'status'=>'viewed',
                'viewed_at'=>now()
            ]);
        }
        return Inertia::render('Leads/Show',[
            'lead'=> new QuizSubmissionResource($lead)
        ]);
    }

    public function destroy(string $uuid)
    {
        QuizSubmission::where('uuid',$uuid)->firstOrFail()->delete();
        return redirect()->route('leads.index')->with('toast','Lead deleted');
    }

    public function blockIp(string $uuid, Request $r)
    {
        $lead = QuizSubmission::where('uuid',$uuid)->firstOrFail();
        if ($lead->ip) {
            BlockedIp::firstOrCreate(
                ['ip'=>$lead->ip,'quiz_id'=>$lead->quiz_id],
                ['user_id'=>$r->user()?->id,'reason'=>$r->string('reason')->toString()]
            );
        }
        return back()->with('toast','IP blocked');
    }
}
