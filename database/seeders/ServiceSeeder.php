<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use DB;
use Exception;
use File;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try{
            if(DB::table('services')->count() == 0){
                
                DB::table('services')->insert([

                    [
                        'user_id' => 3,
                        'category_id' => 6,
                        'title' => 'Painting Service',
                        'price' => 50,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'user_id' => 3,
                        'category_id' => 7,
                        'title' => 'Carpet cleaning Service',
                        'price' => 60,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'user_id' => 3,
                        'category_id' => 8,
                        'title' => 'Cleaning Service',
                        'price' => 70,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'user_id' => 3,
                        'category_id' => 9,
                        'title' => 'Landscaping Service',
                        'price' => 80,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'user_id' => 3,
                        'category_id' => 10,
                        'title' => 'Drywall Repair Service',
                        'price' => 90,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            } else { echo "<br>[Service Table is not empty] "; }

        }catch(Exception $e) {
            echo $e->getMessage();
        }
    }
}