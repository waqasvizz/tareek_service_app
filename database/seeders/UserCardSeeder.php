<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserCard;
use DB;
use Exception;
use File;

class UserCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try{
            if(DB::table('user_cards')->count() == 0){
                
                DB::table('user_cards')->insert([

                    [
                        'user_id' => 3,
                        'card_name' => 'John',
                        'card_number' => '4242424242424242',
                        'exp_month' => '05',
                        'exp_year' => '2025',
                        'cvc_number' => '123',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],

                    [
                        'user_id' => 3,
                        'card_name' => 'Doe',
                        'card_number' => '4242424242424242',
                        'exp_month' => '06',
                        'exp_year' => '2026',
                        'cvc_number' => '456',
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