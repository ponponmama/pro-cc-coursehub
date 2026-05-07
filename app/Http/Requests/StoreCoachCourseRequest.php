<?php

namespace App\Http\Requests;

use App\Models\Course;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreCoachCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'string'],
            'difficulty'  => ['required', 'in:beginner,intermediate,advanced'],
            'status'      => ['required', 'in:draft,published'],
            'image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'tags'        => ['nullable', 'array'],
            'tags.*'      => ['exists:tags,id'],
            'new_tags'    => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'コースタイトルは必須です。',
            'category_id.required' => 'カテゴリは必須です。',
            'category_id.exists'   => '選択したカテゴリは存在しません。',
            'description.required' => 'コース説明は必須です。',
            'difficulty.required'  => '難易度は必須です。',
            'difficulty.in'        => '難易度の値が不正です。',
            'status.required'      => 'ステータスは必須です。',
            'status.in'            => 'ステータスの値が不正です。',
            'image.image'          => '画像ファイルをアップロードしてください。',
            'image.max'            => '画像サイズは2MB以下にしてください。',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if (Course::where('user_id', $this->user()->id)
                ->where('title', $this->input('title'))
                ->exists()) {
                $v->errors()->add('title', '同じタイトルのコースが既に存在します。');
            }
        });
    }
}
