<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use DB;
use Exception;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try{
            if(DB::table('categories')->count() == 0){
                DB::table('categories')->insert([

                    [
                        'category_title' => 'Appliances',
                        'commission' => 5,
                        'category_type' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],

                    [
                        'category_title' => 'Apps & Games',
                        'commission' => 6,
                        'category_type' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],

                    [
                        'category_title' => 'Arts, Crafts, & Sewing',
                        'commission' => 7,
                        'category_type' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],

                    [
                        'category_title' => 'Books',
                        'commission' => 8,
                        'category_type' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],

                    [
                        'category_title' => 'Computers',
                        'commission' => 9,
                        'category_type' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],

                    [
                        'category_title' => 'Painting',
                        'commission' => 5,
                        'category_type' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],

                    [
                        'category_title' => 'Carpet cleaning',
                        'commission' => 6,
                        'category_type' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],

                    [
                        'category_title' => 'Cleaning',
                        'commission' => 7,
                        'category_type' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],

                    [
                        'category_title' => 'Landscaping',
                        'commission' => 8,
                        'category_type' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],

                    [
                        'category_title' => 'Drywall Repair',
                        'commission' => 9,
                        'category_type' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],

                    [
                        'category_title' => 'Pressure Washing',
                        'commission' => 10,
                        'category_type' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
            } else { echo "<br>[Category Table is not empty] "; }

        }catch(Exception $e) {
            echo $e->getMessage();
        }
    }
}