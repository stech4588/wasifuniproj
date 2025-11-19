<?php

namespace Database\Seeders;

use App\Models\Permission\Permission;
use App\Models\Role\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{

    public function run()
    {
        Schema::disableForeignKeyConstraints();   //here we are disabling it so that if there are any parent/child relations
        if (Schema::hasTable('roles')) {
            DB::table('roles')->truncate();
        }
        Schema::enableForeignKeyConstraints();

        // again enabling to maintain all relations (parent/child) will all other tables to ensure the smooth functioning

        $roles = $this->getRoles();  // it is a php function from php.net which returns an array.

        foreach ($roles as $role) {


            Role::firstOrCreate($role);
        }
    }

    /**
     * Get the roles data from an external source (e.g., array, configuration file).
     */

    private function getRoles()
    {
        // Alternatively, you can define the roles directly in the seeder:
        return [
            [
                'name' => 'Super Admin',
                'permission_id' => [1,2,3,4,5,6,7,8,9,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30, 31,32,
                    33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56, 57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73],
                'description' => 'Super Admin',
            ],
            [
                'name' => 'User',
                'permission_id' => [10],
                'description' => 'Normal User',
                // User has access to some modules and sub-modules
            ]
        ];
    }
}
