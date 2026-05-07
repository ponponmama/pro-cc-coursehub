<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    private User $coach;
    private User $student;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coach = User::factory()->create(['role' => 'coach']);
        $this->student = User::factory()->create(['role' => 'student']);
        $this->category = Category::factory()->create();
    }

    public function test_student_can_view_course_list(): void
    {
        $course = Course::factory()->create([
            'user_id' => $this->coach->id,
            'category_id' => $this->category->id,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->student)->get('/courses');

        $response->assertStatus(200);
        $response->assertSee($course->title);
    }

    public function test_student_can_view_published_course(): void
    {
        $course = Course::factory()->create([
            'user_id' => $this->coach->id,
            'category_id' => $this->category->id,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->student)->get("/courses/{$course->id}");

        $response->assertStatus(200);
        $response->assertSee($course->title);
    }

    public function test_coach_can_create_course(): void
    {
        $tags = Tag::factory()->count(2)->create();

        $response = $this->actingAs($this->coach)->post('/coach/courses', [
            'title' => 'テストコース',
            'category_id' => $this->category->id,
            'description' => 'テストコースの説明文です。',
            'difficulty' => 'beginner',
            'status' => 'draft',
            'tags' => $tags->pluck('id')->toArray(),
        ]);

        $response->assertRedirect('/coach/courses');
        $this->assertDatabaseHas('courses', [
            'title' => 'テストコース',
            'user_id' => $this->coach->id,
        ]);
    }

    public function test_coach_can_update_course(): void
    {
        $course = Course::factory()->create([
            'user_id' => $this->coach->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->coach)->put("/coach/courses/{$course->id}", [
            'title' => '更新されたタイトル',
            'category_id' => $this->category->id,
            'description' => '更新された説明文です。',
            'difficulty' => 'intermediate',
            'status' => 'published',
        ]);

        $response->assertRedirect('/coach/courses');
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'title' => '更新されたタイトル',
        ]);
    }

    public function test_coach_can_delete_own_course(): void
    {
        $course = Course::factory()->create([
            'user_id' => $this->coach->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->coach)->delete("/coach/courses/{$course->id}");

        $response->assertRedirect('/coach/courses');
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_coach_cannot_delete_other_coaches_course(): void
    {
        $otherCoach = User::factory()->create(['role' => 'coach']);
        $course = Course::factory()->create([
            'user_id' => $otherCoach->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->coach)->delete("/coach/courses/{$course->id}");

        $response->assertStatus(403);
    }

    public function test_student_cannot_create_course(): void
    {
        $response = $this->actingAs($this->student)->get('/coach/courses/create');

        $response->assertStatus(403);
    }

    public function test_course_list_can_be_filtered_by_category(): void
    {
        // CategoryFactory は日本語名から Str::slug() で空文字スラッグを生成し unique 制約違反になるため明示指定
        $otherCategory = Category::factory()->create(['slug' => 'other-' . uniqid()]);

        Course::factory()->create([
            'user_id' => $this->coach->id,
            'category_id' => $this->category->id,
            'status' => 'published',
            'title' => 'カテゴリAのコース',
        ]);

        Course::factory()->create([
            'user_id' => $this->coach->id,
            'category_id' => $otherCategory->id,
            'status' => 'published',
            'title' => 'カテゴリBのコース',
        ]);

        $response = $this->actingAs($this->student)
            ->get("/courses?category={$this->category->id}");

        $response->assertStatus(200);
        $response->assertSee('カテゴリAのコース');
        $response->assertDontSee('カテゴリBのコース');
    }

    public function test_course_list_can_be_searched(): void
    {
        Course::factory()->create([
            'user_id' => $this->coach->id,
            'category_id' => $this->category->id,
            'status' => 'published',
            'title' => 'Laravel入門',
        ]);

        Course::factory()->create([
            'user_id' => $this->coach->id,
            'category_id' => $this->category->id,
            'status' => 'published',
            'title' => 'React基礎',
        ]);

        $response = $this->actingAs($this->student)
            ->get('/courses?search=Laravel');

        $response->assertStatus(200);
        $response->assertSee('Laravel入門');
        $response->assertDontSee('React基礎');
    }
}
