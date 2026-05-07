<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user, Course $course): bool
    {
        if ($user->role !== 'student') {
            return false;
        }

        if (Review::where('user_id', $user->id)->where('course_id', $course->id)->exists()) {
            return false;
        }

        return Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', 'completed')
            ->exists();
    }
}
