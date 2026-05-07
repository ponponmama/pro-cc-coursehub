<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoachCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('course'));
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'string'],
            'difficulty'  => ['required', 'in:beginner,intermediate,advanced'],
            'status'      => ['required', 'in:draft,published,archived'],
            'tags'        => ['nullable', 'array'],
            'tags.*'      => ['exists:tags,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'コースタイトルは必須です。',
            'title.max'            => 'コースタイトルは255文字以内で入力してください。',
            'category_id.required' => 'カテゴリを選択してください。',
            'category_id.exists'   => '選択されたカテゴリは存在しません。',
            'description.required' => 'コース説明は必須です。',
            'difficulty.required'  => '難易度を選択してください。',
            'difficulty.in'        => '難易度の値が不正です。',
            'status.required'      => '公開ステータスを選択してください。',
            'status.in'            => '公開ステータスの値が不正です。',
            'tags.array'           => 'タグの形式が不正です。',
            'tags.*.exists'        => '選択されたタグは存在しません。',
        ];
    }
}
