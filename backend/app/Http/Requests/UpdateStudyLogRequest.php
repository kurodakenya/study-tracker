<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudyLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'certification_id' => 'sometimes|required|exists:certifications,id',
            'category_id'      => 'nullable|exists:categories,id',
            'studied_on'       => 'sometimes|required|date',
            'duration_minutes' => 'sometimes|required|integer|min:1|max:1440',
            'content'          => 'nullable|string|max:5000',
        ];
    }
}
