<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'    => $this->uuid,
            'id'      => $this->id,
            'status'  => $this->status,
            'paid'    => (bool) $this->paid,
            'created_at' => $this->created_at,
            'created_human' => $this->created_at?->format('M d, Y H:i'),
            'viewed_at' => $this->viewed_at?->toDateTimeString(),

            'quiz'    => [
                'id' => $this->quiz?->id,
                'title' => $this->quiz?->title,
                'domain'=> $this->quiz?->domain,
            ],

            'contact' => [
                'name'  => $this->contact_name,
                'phone' => $this->contact_phone,
                'email' => $this->contact_email,
                'text'  => $this->contact_text,
            ],

            'location' => [
                'country' => $this->country,
                'city'    => $this->city,
            ],

            'ip'       => $this->ip,
            'referrer' => $this->referrer,
            'source_url' => $this->source_url,
            'discount_percent' => $this->discount_percent,

            'answers' => $this->answers ?? [],
            'result'  => $this->result ?? [],
            'extra'   => $this->extra ?? [],
        ];
    }
}
