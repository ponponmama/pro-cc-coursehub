<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Option;
use App\Models\Quiz;
use App\Models\Submission;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function show(Course $course, Quiz $quiz)
    {
        $this->authorize('view', $course);

        if (auth()->user()->role === 'student') {
            $hasPassed = Submission::where('user_id', auth()->id())
                ->where('quiz_id', $quiz->id)
                ->where('score', '>=', $quiz->passing_score)
                ->exists();

            if ($hasPassed) {
                return redirect()
                    ->route('courses.quizzes.result', [$course, $quiz])
                    ->with('info', 'すでに合格しています。');
            }
        }

        $quiz->load('questions.options');

        return view('quizzes.show', compact('course', 'quiz'));
    }

    public function submit(Request $request, Course $course, Quiz $quiz)
    {
        $this->authorize('submit', [Submission::class, $quiz, $course]);

        $answers = $request->input('answers', []);

        $quiz->load('questions');

        $correctCount = 0;
        foreach ($quiz->questions as $question) {
            $userAnswer = collect($answers)->firstWhere('question_id', $question->id);
            $selectedOption = Option::find($userAnswer['option_id'] ?? null);
            if ($selectedOption && $selectedOption->is_correct) {
                $correctCount++;
            }
        }

        $total = $quiz->questions->count();
        $score = $total > 0 ? (int) round($correctCount / $total * 100) : 0;

        Submission::create([
            'user_id'      => auth()->id(),
            'quiz_id'      => $quiz->id,
            'score'        => $score,
            'answers'      => $answers,
            'submitted_at' => now(),
        ]);

        return redirect()->route('courses.quizzes.result', [$course, $quiz]);
    }

    public function result(Course $course, Quiz $quiz)
    {
        $this->authorize('view', $course);

        $quiz->load('questions.options');

        $submissions = Submission::where('user_id', auth()->id())
            ->where('quiz_id', $quiz->id)
            ->latest('submitted_at')
            ->get();

        abort_if($submissions->isEmpty(), 404);

        $submission = $submissions->first();

        $hasPassed = $submissions->contains(
            fn ($s) => $s->score >= $quiz->passing_score
        );

        return view('quizzes.result', compact('course', 'quiz', 'submission', 'submissions', 'hasPassed'));
    }
}
