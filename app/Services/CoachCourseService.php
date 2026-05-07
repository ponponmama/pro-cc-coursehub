<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CoachCourseService
{
    public function create(User $user, array $validated, ?UploadedFile $image): Course
    {
        $slug      = $this->generateSlug($validated['title']);
        $imagePath = $image ? $this->uploadImage($image) : null;

        $course = Course::create([
            'user_id'      => $user->id,
            'category_id'  => $validated['category_id'],
            'title'        => $validated['title'],
            'slug'         => $slug,
            'description'  => $validated['description'],
            'difficulty'   => $validated['difficulty'],
            'image_path'   => $imagePath,
            'status'       => $validated['status'],
            'published_at' => $validated['status'] === 'published' ? now() : null,
        ]);

        $this->syncTags($course, $validated['tags'] ?? [], $validated['new_tags'] ?? null);

        Chapter::create(['course_id' => $course->id, 'title' => 'はじめに', 'order' => 1]);

        return $course;
    }

    private function generateSlug(string $title): string
    {
        $slug     = Str::slug($title) ?: 'course-' . time();
        $original = $slug;
        $count    = 1;
        while (Course::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $count++;
        }
        return $slug;
    }

    private function uploadImage(UploadedFile $file): string
    {
        $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('courses', $fileName, 'public');
    }

    private function syncTags(Course $course, array $tagIds, ?string $newTagsInput): void
    {
        if (!empty($newTagsInput)) {
            foreach (array_filter(array_map('trim', explode(',', $newTagsInput))) as $name) {
                $tag = Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
                if (!in_array($tag->id, $tagIds)) {
                    $tagIds[] = $tag->id;
                }
            }
        }

        if (!empty($tagIds)) {
            $course->tags()->sync($tagIds);
        }
    }
}
