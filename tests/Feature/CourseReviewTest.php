<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseReviewTest extends TestCase
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

    private function completeEnrollment(): Enrollment
    {
        return Enrollment::factory()->completed()->create([
            'user_id'   => $this->student->id,
            'course_id' => $this->course->id,
        ]);
    }

    private function postReview(array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->student)
            ->post(route('courses.reviews.store', $this->course), array_merge([
                'rating'  => 4,
                'comment' => '良いコースでした。',
            ], $data));
    }

    // 受講完了した student がレビューを投稿する → 成功（Review が作成される）
    public function test_completed_student_can_post_review(): void
    {
        $this->completeEnrollment();

        $this->postReview()
            ->assertRedirect(route('courses.show', $this->course));

        $this->assertDatabaseHas('reviews', [
            'user_id'   => $this->student->id,
            'course_id' => $this->course->id,
            'rating'    => 4,
        ]);
    }

    // 受講中（active）の student がレビューを投稿しようとする → 403 Forbidden
    public function test_active_student_cannot_post_review(): void
    {
        Enrollment::factory()->create([
            'user_id'   => $this->student->id,
            'course_id' => $this->course->id,
            'status'    => 'active',
        ]);

        $this->postReview()->assertStatus(403);
    }

    // coach がレビューを投稿しようとする → 403 Forbidden
    public function test_coach_cannot_post_review(): void
    {
        $this->actingAs($this->coach)
            ->post(route('courses.reviews.store', $this->course), ['rating' => 5])
            ->assertStatus(403);
    }

    // 同じ student が同じコースに2件目を投稿しようとする → 403 Forbidden
    public function test_completed_student_cannot_post_second_review(): void
    {
        $this->completeEnrollment();

        Review::factory()->create([
            'user_id'   => $this->student->id,
            'course_id' => $this->course->id,
            'rating'    => 3,
        ]);

        $this->postReview()->assertStatus(403);
    }

    // 評価（rating）が 0 または 6 で投稿する → バリデーションエラー
    public function test_invalid_rating_is_rejected(): void
    {
        $this->completeEnrollment();

        $this->postReview(['rating' => 0])
            ->assertSessionHasErrors('rating');

        $this->postReview(['rating' => 6])
            ->assertSessionHasErrors('rating');

        $this->assertDatabaseMissing('reviews', ['user_id' => $this->student->id]);
    }

    // コメントなしで投稿する → 成功（comment は任意）
    public function test_review_without_comment_succeeds(): void
    {
        $this->completeEnrollment();

        $this->postReview(['comment' => null])
            ->assertRedirect(route('courses.show', $this->course));

        $this->assertDatabaseHas('reviews', [
            'user_id'   => $this->student->id,
            'course_id' => $this->course->id,
            'rating'    => 4,
            'comment'   => null,
        ]);
    }

    // コース詳細画面にレビュー一覧が表示される
    public function test_review_is_displayed_on_course_page(): void
    {
        Review::factory()->create([
            'user_id'   => $this->student->id,
            'course_id' => $this->course->id,
            'rating'    => 5,
            'comment'   => '素晴らしいコースです。',
        ]);

        $this->actingAs($this->student)
            ->get(route('courses.show', $this->course))
            ->assertStatus(200)
            ->assertSee('素晴らしいコースです。');
    }

    // 受講完了済みの student には投稿フォームが表示される
    public function test_form_shown_to_completed_student_without_review(): void
    {
        $this->completeEnrollment();

        $this->actingAs($this->student)
            ->get(route('courses.show', $this->course))
            ->assertSee('レビューを投稿する');
    }

    // 既にレビュー済みの student にはフォームが表示されない
    public function test_form_hidden_when_already_reviewed(): void
    {
        $this->completeEnrollment();

        Review::factory()->create([
            'user_id'   => $this->student->id,
            'course_id' => $this->course->id,
            'rating'    => 4,
        ]);

        $this->actingAs($this->student)
            ->get(route('courses.show', $this->course))
            ->assertDontSee('レビューを投稿する');
    }
}
