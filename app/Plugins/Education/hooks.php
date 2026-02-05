<?php

use App\Plugins\Education\EducationModule;
use Illuminate\Support\Facades\Log;

return [
    'OnCourseEnrolled' => function ($data) {
        Log::info('Course enrolled', [
            'user_id' => $data['user']->id,
            'course_id' => $data['course']->id,
            'course_name' => $data['course']->name,
        ]);
        return $data;
    },

    'OnCourseCompleted' => function ($data) {
        Log::info('Course completed', [
            'user_id' => $data['user']->id,
            'course_id' => $data['course']->id,
            'course_name' => $data['course']->name,
            'intelligence_gained' => $data['course']->intelligence_reward ?? 0,
            'endurance_gained' => $data['course']->endurance_reward ?? 0,
        ]);
        return $data;
    },

    'alterEducationCourses' => function ($data) {
        return $data;
    },
];
