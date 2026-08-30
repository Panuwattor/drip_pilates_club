<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            BranchSeeder::class,
            TrainerSeeder::class,
            ClassTypeSeeder::class,
            PackageSeeder::class,
            AnnouncementSeeder::class,
            AdminSeeder::class,
            ClassScheduleSeeder::class,
        ]);
    }
}
