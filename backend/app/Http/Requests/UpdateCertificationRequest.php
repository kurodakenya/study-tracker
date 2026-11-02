<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // sometimes = そのフィールドが送られた場合のみバリデーション（部分更新対応）
        return [
            'name'      => 'sometimes|required|string|max:255',
            'exam_date' => 'nullable|date',
            'status'    => 'sometimes|in:studying,passed,failed',
            'memo'      => 'nullable|string|max:5000',
        ];
    }
}
