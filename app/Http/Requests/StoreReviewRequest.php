<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => '評価を選択してください。',
            'rating.integer'  => '評価は数値で指定してください。',
            'rating.min'      => '評価は1以上で指定してください。',
            'rating.max'      => '評価は5以下で指定してください。',
            'comment.max'     => 'コメントは1000文字以内で入力してください。',
        ];
    }
}
