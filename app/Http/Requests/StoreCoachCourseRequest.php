<?php

namespace App\Http\Requests;

use App\Models\Course;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreCoachCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Course::class);
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
            'title.max'            => 'コースタイトルは255文字以内で入力してください。',
            'category_id.required' => 'カテゴリを選択してください。',
            'category_id.exists'   => '選択されたカテゴリは存在しません。',
            'description.required' => 'コース説明は必須です。',
            'difficulty.required'  => '難易度を選択してください。',
            'difficulty.in'        => '難易度の値が不正です。',
            'status.required'      => '公開ステータスを選択してください。',
            'status.in'            => '公開ステータスの値が不正です。',
            'image.image'          => '画像ファイルを選択してください。',
            'image.mimes'          => '画像はjpeg,png,jpg,gif形式のみアップロードできます。',
            'image.max'            => '画像サイズは2MB以内にしてください。',
            'tags.array'           => 'タグの形式が不正です。',
            'tags.*.exists'        => '選択されたタグは存在しません。',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $exists = Course::where('user_id', $this->user()->id)
                ->where('title', $this->input('title'))
                ->exists();

            if ($exists) {
                $v->errors()->add('title', '同じタイトルのコースが既に存在します。');
            }
        });
    }
}
