<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserDeliveryOption;
use DB;
use Exception;

class UserDeliveryOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try{
            if(DB::table('user_delivery_options')->count() == 0){
                DB::table('user_delivery_options')->insert([

                    [
                        'user_id' => 3,
                        'title' => 'Standard Delivery',
                        'status' => 2,
                        'amount' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'user_id' => 3,
                        'title' => 'Express Delivery',
                        'status' => 1,
                        'amount' => 30,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            } else { echo "<br>[User delivery option Table is not empty] "; }

        }catch(Exception $e) {
            echo $e->getMessage();
        }
    }
}