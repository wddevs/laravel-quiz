<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

//        User::factory()->create([
//            'name' => 'wd',
//            'email' => 'wddevs@gmail.com',
//            'password' => bcrypt('hollister98765'),
//        ]);

//        $admin = Role::firstOrCreate(['name' => 'superadmin']);
//        $client= Role::firstOrCreate(['name' => 'client']);
//
//        Permission::firstOrCreate(['name' => 'manage widgets']);
//        $admin->givePermissionTo('manage widgets');

        $user = User::find(1);

        $user->assignRole('client');

        $user->givePermissionTo('manage widgets');

        $user->save();
    }
}
