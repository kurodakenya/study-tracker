<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // 認証済みユーザーなら許可（ルートのmiddlewareで認証チェック済み）
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'exam_date' => 'nullable|date',
            'status'    => 'nullable|in:studying,passed,failed',
            'memo'      => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '資格名は必須です。',
            'name.max'      => '資格名は255文字以内で入力してください。',
            'status.in'     => 'ステータスは studying, passed, failed のいずれかです。',
        ];
    }
}
