<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizSubmitTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private Course $course;
    private Quiz $quiz;

    protected function setUp(): void
    {
        parent::setUp();

        $coach = User::factory()->create(['role' => 'coach']);
        $this->student = User::factory()->create(['role' => 'student']);
        $category = Category::factory()->create();

        $this->course = Course::factory()->published()->create([
            'user_id' => $coach->id,
            'category_id' => $category->id,
        ]);

        $chapter = Chapter::factory()->create(['course_id' => $this->course->id]);
        $lesson = Lesson::factory()->create(['chapter_id' => $chapter->id]);
        $this->quiz = Quiz::factory()->create(['lesson_id' => $lesson->id]);
    }

    public function test_score_is_100_when_all_answers_are_correct(): void
    {
        $question1 = Question::factory()->create(['quiz_id' => $this->quiz->id, 'order' => 1]);
        $correct1 = Option::factory()->correct()->create(['question_id' => $question1->id]);
        Option::factory()->create(['question_id' => $question1->id]);

        $question2 = Question::factory()->create(['quiz_id' => $this->quiz->id, 'order' => 2]);
        $correct2 = Option::factory()->correct()->create(['question_id' => $question2->id]);
        Option::factory()->create(['question_id' => $question2->id]);

        $response = $this->actingAs($this->student)->post(
            route('courses.quizzes.submit', [$this->course, $this->quiz]),
            [
                'answers' => [
                    0 => ['option_id' => $correct1->id],
                    1 => ['option_id' => $correct2->id],
                ],
            ]
        );

        $response->assertRedirect(route('courses.quizzes.result', [$this->course, $this->quiz]));
        $this->assertDatabaseHas('submissions', [
            'user_id' => $this->student->id,
            'quiz_id' => $this->quiz->id,
            'score' => 100,
        ]);
    }

    public function test_unanswered_questions_are_treated_as_wrong_and_score_is_correct(): void
    {
        $question1 = Question::factory()->create(['quiz_id' => $this->quiz->id, 'order' => 1]);
        $correct1 = Option::factory()->correct()->create(['question_id' => $question1->id]);

        $question2 = Question::factory()->create(['quiz_id' => $this->quiz->id, 'order' => 2]);
        Option::factory()->correct()->create(['question_id' => $question2->id]);

        $response = $this->actingAs($this->student)->post(
            route('courses.quizzes.submit', [$this->course, $this->quiz]),
            [
                'answers' => [
                    0 => ['option_id' => $correct1->id],
                    // index 1 未回答
                ],
            ]
        );

        $response->assertRedirect(route('courses.quizzes.result', [$this->course, $this->quiz]));
        $this->assertDatabaseHas('submissions', [
            'user_id' => $this->student->id,
            'quiz_id' => $this->quiz->id,
            'score' => 50,
        ]);
    }

    public function test_score_is_0_when_all_questions_are_unanswered(): void
    {
        Question::factory()->create(['quiz_id' => $this->quiz->id, 'order' => 1]);
        Question::factory()->create(['quiz_id' => $this->quiz->id, 'order' => 2]);

        $response = $this->actingAs($this->student)->post(
            route('courses.quizzes.submit', [$this->course, $this->quiz]),
            ['answers' => []]
        );

        $response->assertRedirect(route('courses.quizzes.result', [$this->course, $this->quiz]));
        $this->assertDatabaseHas('submissions', [
            'user_id' => $this->student->id,
            'quiz_id' => $this->quiz->id,
            'score' => 0,
        ]);
    }

    public function test_quiz_with_no_questions_does_not_cause_error(): void
    {
        $response = $this->actingAs($this->student)->post(
            route('courses.quizzes.submit', [$this->course, $this->quiz]),
            ['answers' => []]
        );

        $response->assertRedirect(route('courses.quizzes.result', [$this->course, $this->quiz]));
        $this->assertDatabaseHas('submissions', [
            'user_id' => $this->student->id,
            'quiz_id' => $this->quiz->id,
            'score' => 0,
        ]);
    }
}
