<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Department;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Departments
        $it = Department::create([
            'name' => 'Information Technology',
            'name_kh' => 'បច្ចេកវិទ្យាព័ត៌មាន',
            'code' => 'IT',
            'description' => 'ដេប៉ាតឺម៉ង់បច្ចេកវិទ្យាព័ត៌មាន STIC សៀមរាប',
        ]);

        $ce = Department::create([
            'name' => 'Civil Engineering',
            'name_kh' => 'វិស្វកម្មសំណង់',
            'code' => 'CE',
            'description' => 'ដេប៉ាតឺម៉ង់វិស្វកម្មសំណង់ស៊ីវិល',
        ]);

        $ee = Department::create([
            'name' => 'Electrical Engineering',
            'name_kh' => 'វិស្វកម្មអគ្គិសនី',
            'code' => 'EE',
            'description' => 'ដេប៉ាតឺម៉ង់វិស្វកម្មអគ្គិសនី និងអេឡិចត្រូនិក',
        ]);

        $acc = Department::create([
            'name' => 'Accounting',
            'name_kh' => 'គណនេយ្យ',
            'code' => 'ACC',
            'description' => 'ដេប៉ាតឺម៉ង់គណនេយ្យ និងហិរញ្ញវត្ថុ',
        ]);

        // 2. Create Classes
        $classItY1M = SchoolClass::create([
            'department_id' => $it->id,
            'name' => 'IT Year 1 Morning',
            'name_kh' => 'IT ឆ្នាំ១ ព្រឹក',
            'year' => 1,
            'shift' => 'morning',
            'room' => '101',
            'floor' => '1',
            'capacity' => 35,
        ]);

        $classItY2M = SchoolClass::create([
            'department_id' => $it->id,
            'name' => 'IT Year 2 Morning',
            'name_kh' => 'IT ឆ្នាំ២ ព្រឹក',
            'year' => 2,
            'shift' => 'morning',
            'room' => '102',
            'floor' => '1',
            'capacity' => 35,
        ]);

        $classItY1A = SchoolClass::create([
            'department_id' => $it->id,
            'name' => 'IT Year 1 Afternoon',
            'name_kh' => 'IT ឆ្នាំ១ រសៀល',
            'year' => 1,
            'shift' => 'afternoon',
            'room' => '103',
            'floor' => '1',
            'capacity' => 35,
        ]);

        $classCeY1M = SchoolClass::create([
            'department_id' => $ce->id,
            'name' => 'Civil Eng Year 1 Morning',
            'name_kh' => 'សំណង់ ឆ្នាំ១ ព្រឹក',
            'year' => 1,
            'shift' => 'morning',
            'room' => '201',
            'floor' => '2',
            'capacity' => 40,
        ]);

        // 3. Create Admin User
        $admin = User::create([
            'name' => 'Admin STIC',
            'email' => 'admin@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '012345678',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        // 4. Create Teacher User
        $teacher = User::create([
            'name' => 'លោកគ្រូ សុខ ចាន់',
            'email' => 'teacher@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'phone' => '098765432',
            'department_id' => $it->id,
            'status' => 'active',
            'approved_at' => now(),
        ]);

        // Assign teacher to class
        $teacher->teachingClasses()->attach($classItY1M->id, ['subject' => 'Web Development (Laravel)']);

        // 5. Create Student User
        $student = User::create([
            'name' => 'សិស្ស រតនា វីរៈ',
            'email' => 'student@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'phone' => '011223344',
            'department_id' => $it->id,
            'class_id' => $classItY1M->id,
            'status' => 'active',
            'approved_at' => now(),
        ]);

        // 6. Create Schedules
        Schedule::create([
            'class_id' => $classItY1M->id,
            'teacher_id' => $teacher->id,
            'subject' => 'Web Development (Laravel)',
            'day_of_week' => 'monday',
            'shift' => 'morning',
            'room' => 'Lab 1',
            'start_time' => '08:00:00',
            'end_time' => '11:00:00',
        ]);

        Schedule::create([
            'class_id' => $classItY1M->id,
            'teacher_id' => $teacher->id,
            'subject' => 'Database Management (SQL)',
            'day_of_week' => 'wednesday',
            'shift' => 'morning',
            'room' => 'Lab 2',
            'start_time' => '08:00:00',
            'end_time' => '11:00:00',
        ]);

        // 7. Create Announcements
        Announcement::create([
            'author_id' => $admin->id,
            'title' => 'សេចក្តីជូនដំណឹងស្តីពីការបើកបវេសនកាលឆ្នាំសិក្សាថ្មី ២០២៦-២០២៧',
            'content' => 'វិទ្យាស្ថានពហុបច្ចេកទេសភូមិភាគតេជោសែនសៀមរាប (STIC) សូមជូនដំណឹងដល់និស្សិតទាំងអស់ឱ្យបានជ្រាបថា ការចូលរៀននឹងចាប់ផ្តើមពីថ្ងៃច័ន្ទសប្តាហ៍ក្រោយ។',
            'target_type' => 'public',
            'target_id' => null,
            'is_telegram_sent' => false,
        ]);

        Announcement::create([
            'author_id' => $teacher->id,
            'title' => 'ការរៀបចំកុំព្យូទ័រសម្រាប់ម៉ោងរៀន Web Development',
            'content' => 'សូមនិស្សិតថ្នាក់ IT ឆ្នាំ១ ព្រឹក ទាំងអស់ ដំឡើង VS Code និង Git លើកុំព្យូទ័រយួរដៃឱ្យបានរួចរាល់មុនម៉ោងរៀន។',
            'target_type' => 'class',
            'target_id' => $classItY1M->id,
            'is_telegram_sent' => false,
        ]);
    }
}
