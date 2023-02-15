<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    	User::create([
	            'name' => 'Pankaj Kumar',
	            'email' => 'prince9914773530@gmail.com',
	            'password' => Hash::make('123456'),
                'role' => 'Admin'
            ]);
            User::create([
	            'name' => 'Priyanshu Kumar',
	            'email' => 'Priyanshu@gmail.com',
	            'password' => Hash::make('123456'),
                'role' => 'Admin'
            ]);

        for ($i=0; $i < 3; $i++) {
	    	User::create([
	            'name' => Str::random(10),
	            'email' => Str::random(10).'@gmail.com',
	            'password' => Hash::make('123456'),
                'role' => 'Admin'
	        ]);
    	}
    }
}
