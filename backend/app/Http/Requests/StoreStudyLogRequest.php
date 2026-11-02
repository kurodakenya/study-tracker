<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudyLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'certification_id' => 'required|exists:certifications,id',
            'category_id'      => 'nullable|exists:categories,id',
            'studied_on'       => 'required|date',
            'duration_minutes' => 'required|integer|min:1|max:1440',
            'content'          => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'certification_id.required' => '対象の資格を選択してください。',
            'certification_id.exists'   => '指定された資格が見つかりません。',
            'studied_on.required'       => '学習日は必須です。',
            'duration_minutes.required' => '学習時間は必須です。',
            'duration_minutes.min'      => '学習時間は1分以上で入力してください。',
            'duration_minutes.max'      => '学習時間は1440分（24時間）以内で入力してください。',
        ];
    }
}
