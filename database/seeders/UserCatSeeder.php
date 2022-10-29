<?php

namespace Database\Seeders;

use App\Models\UserCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserCatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user_categories = [
            ["name"=>"Doctor"],
            ["name"=>"Nurse"],
            ["name"=>"Patient"],
            ["name"=>"Pharmacy"],
            ["name"=>"Manufacturer"],
            ["name"=>"Distributor"],
            ["name"=>"Hospital"],
            ["name"=>"Clinic"],
            ["name"=>"Other Service Providers"],
            ["name"=>"Admin"],
        ];

        foreach($user_categories as $user_category){
            UserCategory::updateOrCreate($user_category);
        }
        
    }
}
