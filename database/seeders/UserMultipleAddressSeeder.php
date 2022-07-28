<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserMultipleAddresse;
use DB;
use Exception;

class UserMultipleAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try{
            if(DB::table('user_multiple_addresses')->count() == 0){
                DB::table('user_multiple_addresses')->insert([

                    [
                        'user_id' => 3,
                        'title' => 'Office',
                        'address' => '292 St 56, F-11/4 F 11/4 F-11, Islamabad, Islamabad Capital Territory, Pakistan',
                        'latitude' => '33.685199',
                        'longitude' => '72.996838',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],

                    [
                        'user_id' => 3,
                        'title' => 'Home',
                        'address' => 'Plot 227, Block L Phase 2 Johar Town, Lahore, Punjab, Pakistan',
                        'latitude' => '31.468061',
                        'longitude' => '74.269452',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            } else { echo "<br>[User multiple address Table is not empty] "; }

        }catch(Exception $e) {
            echo $e->getMessage();
        }
    }
}