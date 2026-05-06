<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizRetakeTest extends TestCase
{
    use RefreshDatabase;

    private User $coach;
    private User $student;
    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coach   = User::factory()->create(['role' => 'coach']);
        $this->student = User::factory()->create(['role' => 'student']);
        $this->course  = Course::factory()->create(['user_id' => $this->coach->id]);
    }

    private function createQuiz(int $passingScore = 70): Quiz
    {
        $chapter = Chapter::factory()->create(['course_id' => $this->course->id]);
        $lesson  = Lesson::factory()->create(['chapter_id' => $chapter->id]);
        return Quiz::factory()->create(['lesson_id' => $lesson->id, 'passing_score' => $passingScore]);
    }

    private function enrollStudent(): void
    {
        Enrollment::factory()->create([
            'user_id'   => $this->student->id,
            'course_id' => $this->course->id,
            'status'    => 'active',
        ]);
    }

    private function submission(Quiz $quiz, int $score, ?string $submittedAt = null): Submission
    {
        return Submission::factory()->create([
            'user_id'      => $this->student->id,
            'quiz_id'      => $quiz->id,
            'score'        => $score,
            'submitted_at' => $submittedAt ?? now(),
        ]);
    }

    // 未受験の student が小テストを受験する → Submission が作成される
    public function test_student_can_submit_quiz(): void
    {
        $quiz = $this->createQuiz();
        $this->enrollStudent();

        $this->actingAs($this->student)
            ->post(route('courses.quizzes.submit', [$this->course, $quiz]), ['answers' => []])
            ->assertRedirect(route('courses.quizzes.result', [$this->course, $quiz]));

        $this->assertDatabaseHas('submissions', [
            'user_id' => $this->student->id,
            'quiz_id' => $quiz->id,
        ]);
    }

    // 不合格の student が再受験する → 新しい Submission が作成される
    public function test_student_who_failed_can_resubmit(): void
    {
        $quiz = $this->createQuiz();
        $this->enrollStudent();
        $this->submission($quiz, 69, now()->subMinute()->toDateTimeString());

        $this->actingAs($this->student)
            ->post(route('courses.quizzes.submit', [$this->course, $quiz]), ['answers' => []])
            ->assertRedirect(route('courses.quizzes.result', [$this->course, $quiz]));

        $this->assertSame(
            2,
            Submission::where('user_id', $this->student->id)->where('quiz_id', $quiz->id)->count()
        );
    }

    // 合格済みの student が再受験しようとする → 結果画面にリダイレクト
    public function test_student_who_passed_is_redirected_from_show(): void
    {
        $quiz = $this->createQuiz();
        $this->submission($quiz, 70);

        $this->actingAs($this->student)
            ->get(route('courses.quizzes.show', [$this->course, $quiz]))
            ->assertRedirect(route('courses.quizzes.result', [$this->course, $quiz]));
    }

    // 合格済みの student が submit エンドポイントに直接 POST する → 403
    public function test_student_who_passed_cannot_post_to_submit(): void
    {
        $quiz = $this->createQuiz();
        $this->enrollStudent();
        $this->submission($quiz, 70);

        $this->actingAs($this->student)
            ->post(route('courses.quizzes.submit', [$this->course, $quiz]), ['answers' => []])
            ->assertStatus(403);
    }

    // 結果画面に受験履歴が表示される → 全 Submission が新しい順に表示される
    public function test_result_shows_all_submissions_in_descending_order(): void
    {
        $quiz = $this->createQuiz();
        $this->submission($quiz, 50, now()->subMinutes(10)->toDateTimeString()); // 古い
        $this->submission($quiz, 69, now()->toDateTimeString());                  // 新しい

        $response = $this->actingAs($this->student)
            ->get(route('courses.quizzes.result', [$this->course, $quiz]));

        $response->assertStatus(200);
        $response->assertSee('受験履歴');
        $response->assertSeeInOrder(['69%', '50%']); // 新しい順に並んでいること
    }

    // 合格済みの結果画面に再受験ボタンが表示されない
    public function test_retake_button_hidden_when_passed(): void
    {
        $quiz = $this->createQuiz();
        $this->submission($quiz, 70);

        $this->actingAs($this->student)
            ->get(route('courses.quizzes.result', [$this->course, $quiz]))
            ->assertDontSee('再受験する');
    }

    // 不合格の結果画面に再受験ボタンが表示される
    public function test_retake_button_shown_when_failed(): void
    {
        $quiz = $this->createQuiz();
        $this->submission($quiz, 69);

        $this->actingAs($this->student)
            ->get(route('courses.quizzes.result', [$this->course, $quiz]))
            ->assertSee('再受験する');
    }
}
