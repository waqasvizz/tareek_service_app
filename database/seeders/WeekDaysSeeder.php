<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WeekDays;
use DB;
use Exception;

class WeekDaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try{
            if(DB::table('week_days')->count() == 0){
                DB::table('week_days')->insert([
                    [
                        'name' => 'Monday',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => 'Tuesday',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => 'Wednesday',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => 'Thursday',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => 'Friday',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => 'Saturday',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => 'Sunday',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            } else { echo "<br>[Week Day Table is not empty] "; }

        }catch(Exception $e) {
            echo $e->getMessage();
        }
            
    }
}