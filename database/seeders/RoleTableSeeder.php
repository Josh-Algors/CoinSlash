<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            ["name"=>"admin", 'guard_name'=>"api"],
            ["name"=>"doctor", 'guard_name'=>"api"],
            ["name"=>"nurse", 'guard_name'=>"api"],
            ["name"=>"patient", 'guard_name'=>"api"],
            ["name"=>"pharmacy", 'guard_name'=>"api"],
            ["name"=>"manufacturer", 'guard_name'=>"api"],
            ["name"=>"distributor", 'guard_name'=>"api"],
            ["name"=>"hospital", 'guard_name'=>"api"],
            ["name"=>"clinic", 'guard_name'=>"api"],
            ["name"=>"other", 'guard_name'=>"api"],
        ];

        foreach($roles as $role){
            $roleExist = DB::table('roles')->where('name',$role['name'])->first();
            if($roleExist == null){
                DB::table('roles')->insert($role);
            }
        }
    }
}
