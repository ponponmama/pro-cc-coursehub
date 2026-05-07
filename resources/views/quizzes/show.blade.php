@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center text-sm text-gray-500 hover:text-indigo-600 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ $course->title }} に戻る
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $quiz->title }}</h1>
            <p class="text-sm text-gray-500">合格点: <span class="font-medium text-gray-700">{{ $quiz->passing_score }}%</span></p>
        </div>

        @if($quiz->questions->isEmpty())
            <div class="flex flex-col items-center justify-center py-12">
                <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-gray-500">この小テストにはまだ問題がありません。</p>
            </div>
        @else
            <form method="POST" action="{{ route('courses.quizzes.submit', [$course, $quiz]) }}" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf

                @foreach($quiz->questions->sortBy('order') as $index => $question)
                    <div class="mb-6 p-5 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="font-medium text-gray-900 mb-3">問{{ $index + 1 }}. {{ $question->body }}</p>
                        <div class="space-y-2">
                            @foreach($question->options as $option)
                                <label class="flex items-center p-3 rounded-lg hover:bg-white cursor-pointer transition-colors">
                                    <input type="radio" name="answers[{{ $index }}][option_id]" value="{{ $option->id }}"
                                        class="text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    <span class="ml-3 text-sm text-gray-700">{{ $option->body }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <button type="submit" :disabled="submitting"
                    :class="submitting ? 'opacity-50 cursor-not-allowed' : ''"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg px-4 py-3 shadow-sm transition-all duration-150">
                    <span x-show="!submitting">回答を送信する</span>
                    <span x-show="submitting" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        送信中...
                    </span>
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
