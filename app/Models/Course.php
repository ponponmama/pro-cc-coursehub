<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'difficulty',
        'image_path',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'course_tag');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function getAllLessonIds(): array
    {
        $chapters = $this->relationLoaded('chapters')
            ? $this->chapters
            : $this->chapters()->with('lessons')->get();

        return $chapters->flatMap->lessons
            ->where('is_published', true)
            ->pluck('id')
            ->toArray();
    }

    public function getProgressRate($userId): int
    {
        if ($this->relationLoaded('chapters')) {
            $allLessons   = $this->chapters->flatMap->lessons;
            $totalLessons = $allLessons->count();
            $lessonIds    = $allLessons->where('is_published', true)->pluck('id');
        } else {
            $totalLessons = $this->chapters()->withCount('lessons')->get()->sum('lessons_count');
            $lessonIds    = collect($this->getAllLessonIds());
        }

        $completedLessons = LessonProgress::where('user_id', $userId)
            ->whereIn('lesson_id', $lessonIds)
            ->where('status', 'completed')
            ->count();

        return $totalLessons > 0 ? (int) round($completedLessons / $totalLessons * 100) : 0;
    }
}
