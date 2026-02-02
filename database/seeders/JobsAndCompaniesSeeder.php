<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobsAndCompaniesSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name' => 'City Hospital',
                'type' => 'Medical',
                'description' => 'Leading medical facility providing healthcare services',
                'rating' => 85,
                'is_active' => true,
            ],
            [
                'name' => 'Law Firm Associates',
                'type' => 'Law',
                'description' => 'Prestigious law firm handling criminal and corporate cases',
                'rating' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Tech Solutions Inc',
                'type' => 'Technology',
                'description' => 'Leading technology company developing software solutions',
                'rating' => 88,
                'is_active' => true,
            ],
            [
                'name' => 'Golden Casino',
                'type' => 'Casino',
                'description' => 'Premier casino and entertainment venue',
                'rating' => 75,
                'is_active' => true,
            ],
            [
                'name' => 'Security Services Co',
                'type' => 'Security',
                'description' => 'Professional security and protection services',
                'rating' => 80,
                'is_active' => true,
            ],
        ];

        foreach ($companies as $company) {
            $companyId = DB::table('companies')->insertGetId(array_merge($company, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            // Create jobs for each company
            $this->createJobsForCompany($companyId, $company['type']);
        }
    }

    private function createJobsForCompany(int $companyId, string $type): void
    {
        $jobsByType = [
            'Medical' => [
                ['title' => 'Janitor', 'level' => 1, 'salary' => 500, 'intelligence' => 0, 'endurance' => 5],
                ['title' => 'Nurse', 'level' => 5, 'salary' => 2000, 'intelligence' => 10, 'endurance' => 15],
                ['title' => 'Doctor', 'level' => 15, 'salary' => 5000, 'intelligence' => 30, 'endurance' => 20],
                ['title' => 'Surgeon', 'level' => 25, 'salary' => 10000, 'intelligence' => 50, 'endurance' => 30],
            ],
            'Law' => [
                ['title' => 'Paralegal', 'level' => 1, 'salary' => 800, 'intelligence' => 5, 'endurance' => 0],
                ['title' => 'Legal Assistant', 'level' => 5, 'salary' => 1500, 'intelligence' => 15, 'endurance' => 5],
                ['title' => 'Attorney', 'level' => 15, 'salary' => 4000, 'intelligence' => 40, 'endurance' => 10],
                ['title' => 'Senior Partner', 'level' => 30, 'salary' => 12000, 'intelligence' => 70, 'endurance' => 15],
            ],
            'Technology' => [
                ['title' => 'IT Support', 'level' => 1, 'salary' => 1000, 'intelligence' => 10, 'endurance' => 0],
                ['title' => 'Developer', 'level' => 10, 'salary' => 3000, 'intelligence' => 30, 'endurance' => 5],
                ['title' => 'Senior Developer', 'level' => 20, 'salary' => 6000, 'intelligence' => 50, 'endurance' => 10],
                ['title' => 'CTO', 'level' => 35, 'salary' => 15000, 'intelligence' => 80, 'endurance' => 15],
            ],
            'Casino' => [
                ['title' => 'Dealer', 'level' => 1, 'salary' => 600, 'intelligence' => 5, 'endurance' => 10],
                ['title' => 'Pit Boss', 'level' => 10, 'salary' => 2500, 'intelligence' => 20, 'endurance' => 20],
                ['title' => 'Casino Manager', 'level' => 20, 'salary' => 5500, 'intelligence' => 35, 'endurance' => 25],
            ],
            'Security' => [
                ['title' => 'Guard', 'level' => 1, 'salary' => 700, 'intelligence' => 0, 'endurance' => 15],
                ['title' => 'Security Officer', 'level' => 8, 'salary' => 2000, 'intelligence' => 10, 'endurance' => 25],
                ['title' => 'Head of Security', 'level' => 18, 'salary' => 4500, 'intelligence' => 25, 'endurance' => 40],
            ],
        ];

        $jobs = $jobsByType[$type] ?? [];

        foreach ($jobs as $job) {
            DB::table('employment_positions')->insert([
                'company_id' => $companyId,
                'title' => $job['title'],
                'description' => "Work as a {$job['title']} at our company",
                'required_level' => $job['level'],
                'required_intelligence' => $job['intelligence'],
                'required_endurance' => $job['endurance'],
                'base_salary' => $job['salary'],
                'max_employees' => 50,
                'current_employees' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
