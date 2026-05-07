<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseProgressRateTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private Course $course;
    private Chapter $chapter;

    protected function setUp(): void
    {
        parent::setUp();

        $coach = User::factory()->create(['role' => 'coach']);
        $this->student = User::factory()->create(['role' => 'student']);
        $category = Category::factory()->create();

        $this->course = Course::factory()->create([
            'user_id' => $coach->id,
            'category_id' => $category->id,
            'status' => 'published',
        ]);

        $this->chapter = Chapter::factory()->create(['course_id' => $this->course->id]);
    }

    public function test_progress_rate_is_100_when_all_published_lessons_completed(): void
    {
        $published1 = Lesson::factory()->create(['chapter_id' => $this->chapter->id, 'is_published' => true]);
        $published2 = Lesson::factory()->create(['chapter_id' => $this->chapter->id, 'is_published' => true]);
        Lesson::factory()->create(['chapter_id' => $this->chapter->id, 'is_published' => false]);

        LessonProgress::factory()->create(['user_id' => $this->student->id, 'lesson_id' => $published1->id]);
        LessonProgress::factory()->create(['user_id' => $this->student->id, 'lesson_id' => $published2->id]);

        $this->assertSame(100, $this->course->getProgressRate($this->student->id));
    }

    public function test_progress_rate_reflects_partial_completion(): void
    {
        $published1 = Lesson::factory()->create(['chapter_id' => $this->chapter->id, 'is_published' => true]);
        $published2 = Lesson::factory()->create(['chapter_id' => $this->chapter->id, 'is_published' => true]);
        $published3 = Lesson::factory()->create(['chapter_id' => $this->chapter->id, 'is_published' => true]);
        $published4 = Lesson::factory()->create(['chapter_id' => $this->chapter->id, 'is_published' => true]);

        LessonProgress::factory()->create(['user_id' => $this->student->id, 'lesson_id' => $published1->id]);
        LessonProgress::factory()->create(['user_id' => $this->student->id, 'lesson_id' => $published2->id]);

        $this->assertSame(50, $this->course->getProgressRate($this->student->id));
    }

    public function test_progress_rate_is_0_when_course_has_no_lessons(): void
    {
        $this->assertSame(0, $this->course->getProgressRate($this->student->id));
    }
}
