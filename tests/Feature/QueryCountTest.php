<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueryCountTest extends TestCase
{
    use RefreshDatabase;

    // ----------------------------------------------------------------
    // 1. CourseController::index  — N+1 on category / user / chapters / enrollments
    // ----------------------------------------------------------------

    public function test_course_index_query_count(): void
    {
        $coach    = User::factory()->create(['role' => 'coach']);
        $category = Category::factory()->create(['slug' => 'test-cat-' . uniqid()]);
        $student  = User::factory()->create(['role' => 'student']);

        Course::factory(5)->create([
            'user_id'     => $coach->id,
            'category_id' => $category->id,
            'status'      => 'published',
        ]);

        DB::enableQueryLog();
        $this->actingAs($student)->get(route('courses.index'))->assertStatus(200);
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Before fix: 1(courses) + 1(paginate count) + 1(categories dropdown)
        //             + 5(category N+1) + 5(user N+1) + 5(chapters N+1) + 5(enrollments N+1) = 23 queries
        // After fix : ≤ 7 queries (eager load で件数に依存しない)
        $this->assertLessThanOrEqual(7, $count, "N+1 detected: {$count} queries for 5 courses");
    }

    // ----------------------------------------------------------------
    // 2. CoachCourseController::dashboard  — loop N+1 on enrollments
    // ----------------------------------------------------------------

    public function test_coach_dashboard_query_count(): void
    {
        $coach    = User::factory()->create(['role' => 'coach']);
        $category = Category::factory()->create(['slug' => 'dash-cat-' . uniqid()]);
        $courses  = Course::factory(5)->create([
            'user_id'     => $coach->id,
            'category_id' => $category->id,
            'status'      => 'published',
        ]);

        foreach ($courses as $course) {
            Enrollment::factory()->create([
                'user_id'   => User::factory()->create(['role' => 'student'])->id,
                'course_id' => $course->id,
                'status'    => 'active',
            ]);
        }

        DB::enableQueryLog();
        $this->actingAs($coach)->get(route('coach.dashboard'))->assertStatus(200);
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Before fix: 1(courses) + 5(enrollments N+1) = 6 queries
        // After fix : ≤ 3 queries (withCount で1クエリに集約)
        $this->assertLessThanOrEqual(3, $count, "N+1 detected: {$count} queries for 5 courses");
    }

    // ----------------------------------------------------------------
    // 3. Course::getProgressRate  — 4 queries per call (chapters×2 + lessons + progress)
    // ----------------------------------------------------------------

    public function test_my_courses_index_query_count(): void
    {
        $coach    = User::factory()->create(['role' => 'coach']);
        $student  = User::factory()->create(['role' => 'student']);
        $category = Category::factory()->create(['slug' => 'my-cat-' . uniqid()]);

        $courses = Course::factory(3)->create([
            'user_id'     => $coach->id,
            'category_id' => $category->id,
            'status'      => 'published',
        ]);

        foreach ($courses as $course) {
            $chapter = Chapter::factory()->create(['course_id' => $course->id, 'order' => 1]);
            $lesson  = Lesson::factory()->create([
                'chapter_id'   => $chapter->id,
                'is_published' => true,
                'order'        => 1,
            ]);

            Enrollment::factory()->create([
                'user_id'   => $student->id,
                'course_id' => $course->id,
                'status'    => 'active',
            ]);

            LessonProgress::factory()->create([
                'user_id'   => $student->id,
                'lesson_id' => $lesson->id,
                'status'    => 'completed',
            ]);
        }

        DB::enableQueryLog();
        $this->actingAs($student)->get(route('my-courses.index'))->assertStatus(200);
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Before fix: ~5(eager load) + 3×4(getProgressRate N+1) = 17 queries
        // After fix : ≤ 10 queries (chapters/lessons は eager load を再利用)
        $this->assertLessThanOrEqual(10, $count, "N+1 detected: {$count} queries for 3 enrollments");
    }
}
