<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudyLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'certification_id' => $this->certification_id,
            'certification'    => new CertificationResource($this->whenLoaded('certification')),
            'category_id'      => $this->category_id,
            'category'         => new CategoryResource($this->whenLoaded('category')),
            'studied_on'       => $this->studied_on?->format('Y-m-d'),
            'duration_minutes' => $this->duration_minutes,
            'content'          => $this->content,
            'created_at'       => $this->created_at?->toISOString(),
        ];
    }
}
