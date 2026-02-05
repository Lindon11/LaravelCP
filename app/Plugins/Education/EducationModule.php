<?php

namespace App\Plugins\Education;

use App\Plugins\Plugin;

class EducationModule extends Plugin
{
    protected string $name = 'Education';

    public function construct(): void
    {
        $this->config = [
            'max_active_courses' => 1,
            'allow_cancel' => true,
            'cancel_refund_percent' => 50,
        ];
    }

    public function getAvailableCourses($user): array
    {
        $courses = \App\Plugins\Education\Models\EducationCourse::where('is_active', true)
            ->where('required_level', '<=', $user->level)
            ->withCount('enrollments')
            ->get();

        return $this->applyModuleHook('alterEducationCourses', [
            'courses' => $courses,
            'user' => $user,
        ]);
    }

    public function getStats(?\App\Core\Models\User $user = null): array
    {
        return [
            'total_courses' => \App\Plugins\Education\Models\EducationCourse::where('is_active', true)->count(),
            'active_enrollments' => \App\Plugins\Education\Models\UserEducation::where('status', 'in_progress')->count(),
            'completed_total' => \App\Plugins\Education\Models\UserEducation::where('status', 'completed')->count(),
        ];
    }
}
