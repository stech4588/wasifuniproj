<?php

namespace Database\Seeders;

use App\Models\Metatags;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MetaTagsSeeder extends Seeder
{

    public function run()
    {
        Schema::disableForeignKeyConstraints();   //here we are disabling it so that if there are any parent/child relations
        // with other tables, it should not stop us from doing what we want or prompt any errors.

        // this function here makes sure that after running the seeder duplication of data is ignored so if there is data
        // already in the table, first it will drop/empty the table then store latest permissions

        Schema::enableForeignKeyConstraints();

        // again enabling to maintain all relations (parent/child) will all other tables to ensure the smooth functioning

        $metatags = $this->getMetatags();  // it is a php function from php.net which returns an array.

        foreach ($metatags as $metatag) {
            Metatags::firstOrCreate($metatag);
        }
    }

    /**
     * Get the roles data from an external source (e.g., array, configuration file).
     */

    private function getMetatags()
    {
        // Alternatively, you can define the roles directly in the seeder:
        return [
            [
                'page_id' => 1,
                'name' => 'login page',
                'content' => 'login page of ecommerce',
                'created_by' => 1,
            ],
            [
                'page_id' => 1,
                'name' => 'login page data',
                'content' => 'login page of ecommerce data',
                'created_by' => 1,
            ],
            [
                'page_id' => 3,
                'name' => 'product page',
                'content' => 'product page of ecommerce',
                'created_by' => 1,
            ],
        ];
    }
}
