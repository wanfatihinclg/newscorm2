<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        Course::create([
            'name' => 'Sample Course',
            'description' => 'A sample course for SCORM content'
        ]);
    }
}
