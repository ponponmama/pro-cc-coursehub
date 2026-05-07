<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoachCourseRequest;
use App\Http\Requests\UpdateCoachCourseRequest;
use App\Models\Category;
use App\Models\Course;
use App\Models\Tag;
use App\Services\CoachCourseService;
use Illuminate\Support\Facades\Log;

class CoachCourseController extends Controller
{
    public function __construct(private CoachCourseService $courseService) {}

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
        $user    = auth()->user();
        $courses = Course::where('user_id', $user->id)
            ->withCount(['enrollments as active_enrollments_count' => fn($q) => $q->where('status', 'active')])
            ->get();
        $totalStudents = $courses->sum('active_enrollments_count');

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
        try {
            $this->courseService->create(auth()->user(), $request->validated(), $request->file('image'));
        } catch (\Exception $e) {
            Log::error('コース作成エラー: ' . $e->getMessage(), ['user_id' => auth()->id()]);
            return back()->withInput()->withErrors(['error' => 'コースの作成中にエラーが発生しました。もう一度お試しください。']);
        }

        return redirect()->route('coach.courses.index')->with('success', 'コースを作成しました。');
    }

    public function edit(Course $course)
    {
        $this->authorize('update', $course);

        $categories = Category::all();
        $tags = Tag::all();
        $course->load('tags');

        return view('coach.courses.edit', compact('course', 'categories', 'tags'));
    }

    public function update(UpdateCoachCourseRequest $request, Course $course)
    {
        $validated = $request->validated();

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
}
