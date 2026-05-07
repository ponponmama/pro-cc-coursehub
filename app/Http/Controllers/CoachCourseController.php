<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoachCourseRequest;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CoachCourseController extends Controller
{
    public function index()
    {
        $courses = Course::where('user_id', auth()->id())
            ->withCount('chapters', 'enrollments')
            ->latest()
            ->get();

        return view('coach.courses.index', compact('courses'));
    }

    public function dashboard()
    {
        $user = auth()->user();
        $courses = Course::where('user_id', $user->id)->get();
        $totalStudents = 0;
        foreach ($courses as $course) {
            $totalStudents += $course->enrollments()->where('status', 'active')->count();
        }

        return view('coach.dashboard', [
            'courseCount' => $courses->count(),
            'publishedCount' => $courses->where('status', 'published')->count(),
            'totalStudents' => $totalStudents,
        ]);
    }

    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('coach.courses.create', compact('categories', 'tags'));
    }

    public function store(StoreCoachCourseRequest $request)
    {
        $validated = $request->validated();

        $course = Course::create([
            'user_id'      => auth()->id(),
            'category_id'  => $validated['category_id'],
            'title'        => $validated['title'],
            'slug'         => $this->generateUniqueSlug($validated['title']),
            'description'  => $validated['description'],
            'difficulty'   => $validated['difficulty'],
            'image_path'   => $request->hasFile('image') ? $this->uploadImage($request->file('image')) : null,
            'status'       => $validated['status'],
            'published_at' => null,
        ]);

        $this->syncTags($course, $validated['tags'] ?? [], $validated['new_tags'] ?? null);

        Chapter::create([
            'course_id' => $course->id,
            'title'     => 'はじめに',
            'order'     => 1,
        ]);

        if ($validated['status'] === 'published') {
            $course->update(['published_at' => now()]);
        }

        return redirect()->route('coach.courses.index')
            ->with('success', 'コースを作成しました。');
    }

    public function edit(Course $course)
    {
        $this->authorize('update', $course);

        $categories = Category::all();
        $tags = Tag::all();
        $course->load('tags');

        return view('coach.courses.edit', compact('course', 'categories', 'tags'));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'string'],
            'difficulty' => ['required', 'in:beginner,intermediate,advanced'],
            'status' => ['required', 'in:draft,published,archived'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
        ]);

        $course->update([
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'difficulty' => $validated['difficulty'],
            'status' => $validated['status'],
            'published_at' => $validated['status'] === 'published' && !$course->published_at ? now() : $course->published_at,
        ]);

        $course->tags()->sync($validated['tags'] ?? []);

        return redirect()->route('coach.courses.index')
            ->with('success', 'コースを更新しました。');
    }

    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);

        $course->delete();

        return redirect()->route('coach.courses.index')
            ->with('success', 'コースを削除しました。');
    }

    private function syncTags(Course $course, array $tagIds, ?string $newTags): void
    {
        if (!empty($newTags)) {
            foreach (array_map('trim', explode(',', $newTags)) as $tagName) {
                if (empty($tagName)) {
                    continue;
                }
                $tag = Tag::firstOrCreate(
                    ['slug' => Str::slug($tagName)],
                    ['name' => $tagName]
                );
                if (!in_array($tag->id, $tagIds)) {
                    $tagIds[] = $tag->id;
                }
            }
        }

        if (!empty($tagIds)) {
            $course->tags()->sync($tagIds);
        }
    }

    private function uploadImage(UploadedFile $image): string
    {
        $fileName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

        return $image->storeAs('courses', $fileName, 'public');
    }

    private function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        if (empty($slug)) {
            $slug = 'course-' . time();
        }

        $original = $slug;
        $count = 1;
        while (Course::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
