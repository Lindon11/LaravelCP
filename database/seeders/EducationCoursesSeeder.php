<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EducationCoursesSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            // Intelligence Courses
            [
                'name' => 'Basic Education',
                'description' => 'Learn basic reading, writing, and arithmetic',
                'type' => 'intelligence',
                'duration_hours' => 24,
                'intelligence_reward' => 5,
                'endurance_reward' => 0,
                'cost' => 1000,
                'required_level' => 1,
                'required_intelligence' => 0,
            ],
            [
                'name' => 'Computer Science',
                'description' => 'Study programming and computer systems',
                'type' => 'intelligence',
                'duration_hours' => 72,
                'intelligence_reward' => 15,
                'endurance_reward' => 0,
                'cost' => 5000,
                'required_level' => 5,
                'required_intelligence' => 10,
            ],
            [
                'name' => 'Business Administration',
                'description' => 'Master business management and economics',
                'type' => 'intelligence',
                'duration_hours' => 120,
                'intelligence_reward' => 25,
                'endurance_reward' => 0,
                'cost' => 15000,
                'required_level' => 10,
                'required_intelligence' => 20,
            ],
            [
                'name' => 'Law Degree',
                'description' => 'Complete comprehensive legal education',
                'type' => 'intelligence',
                'duration_hours' => 168,
                'intelligence_reward' => 40,
                'endurance_reward' => 0,
                'cost' => 30000,
                'required_level' => 15,
                'required_intelligence' => 35,
            ],
            [
                'name' => 'Medical Degree',
                'description' => 'Become a licensed medical professional',
                'type' => 'intelligence',
                'duration_hours' => 240,
                'intelligence_reward' => 60,
                'endurance_reward' => 5,
                'cost' => 50000,
                'required_level' => 20,
                'required_intelligence' => 50,
            ],
            [
                'name' => 'PhD Program',
                'description' => 'Achieve the highest level of academic achievement',
                'type' => 'intelligence',
                'duration_hours' => 336,
                'intelligence_reward' => 100,
                'endurance_reward' => 10,
                'cost' => 100000,
                'required_level' => 30,
                'required_intelligence' => 80,
            ],
            
            // Endurance Courses
            [
                'name' => 'Basic Training',
                'description' => 'Physical conditioning and basic fitness',
                'type' => 'endurance',
                'duration_hours' => 24,
                'intelligence_reward' => 0,
                'endurance_reward' => 5,
                'cost' => 800,
                'required_level' => 1,
                'required_endurance' => 0,
            ],
            [
                'name' => 'Advanced Fitness',
                'description' => 'Intensive physical training program',
                'type' => 'endurance',
                'duration_hours' => 72,
                'intelligence_reward' => 0,
                'endurance_reward' => 15,
                'cost' => 4000,
                'required_level' => 5,
                'required_endurance' => 10,
            ],
            [
                'name' => 'Military Training',
                'description' => 'Combat and survival skills training',
                'type' => 'endurance',
                'duration_hours' => 120,
                'intelligence_reward' => 5,
                'endurance_reward' => 25,
                'cost' => 12000,
                'required_level' => 10,
                'required_endurance' => 20,
            ],
            [
                'name' => 'Special Forces Training',
                'description' => 'Elite tactical and combat training',
                'type' => 'endurance',
                'duration_hours' => 168,
                'intelligence_reward' => 10,
                'endurance_reward' => 40,
                'cost' => 25000,
                'required_level' => 15,
                'required_endurance' => 35,
            ],
            
            // Mixed Courses
            [
                'name' => 'Detective Course',
                'description' => 'Learn investigation and analytical skills',
                'type' => 'mixed',
                'duration_hours' => 96,
                'intelligence_reward' => 20,
                'endurance_reward' => 10,
                'cost' => 10000,
                'required_level' => 8,
                'required_intelligence' => 15,
            ],
            [
                'name' => 'Leadership Academy',
                'description' => 'Develop leadership and management skills',
                'type' => 'mixed',
                'duration_hours' => 144,
                'intelligence_reward' => 30,
                'endurance_reward' => 15,
                'cost' => 20000,
                'required_level' => 12,
                'required_intelligence' => 25,
            ],
        ];

        foreach ($courses as $course) {
            DB::table('education_courses')->insert(array_merge($course, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
