<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'exam_date'  => $this->exam_date?->format('Y-m-d'),
            'status'     => $this->status,
            'memo'       => $this->memo,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
