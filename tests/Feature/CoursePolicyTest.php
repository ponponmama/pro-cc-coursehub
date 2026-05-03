<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoursePolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $coach;
    private User $student;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->coach = User::factory()->create(['role' => 'coach']);
        $this->student = User::factory()->create(['role' => 'student']);
        $this->category = Category::factory()->create();
    }

    public function test_admin_can_view_draft_course(): void
    {
        $course = Course::factory()->draft()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->admin)->get("/courses/{$course->id}");

        $response->assertStatus(200);
    }

    public function test_coach_can_view_own_draft_course(): void
    {
        $course = Course::factory()->draft()->create([
            'user_id' => $this->coach->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->coach)->get("/courses/{$course->id}");

        $response->assertStatus(200);
    }

    public function test_student_can_view_published_course(): void
    {
        $course = Course::factory()->published()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->student)->get("/courses/{$course->id}");

        $response->assertStatus(200);
    }

    public function test_student_cannot_view_draft_course(): void
    {
        $course = Course::factory()->draft()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->student)->get("/courses/{$course->id}");

        $response->assertStatus(403);
    }

    public function test_student_cannot_view_archived_course(): void
    {
        $course = Course::factory()->archived()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->student)->get("/courses/{$course->id}");

        $response->assertStatus(403);
    }
}
