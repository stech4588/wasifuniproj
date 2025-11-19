<?php

namespace Database\Seeders;

use App\Models\Metatags;
use App\Models\Pages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();   //here we are disabling it so that if there are any parent/child relations
        // with other tables, it should not stop us from doing what we want or prompt any errors.

        if(Schema::hasTable('pages')){
            DB::table('pages')->truncate();
        }
        // this function here makes sure that after running the seeder duplication of data is ignored so if there is data
        // already in the table, first it will drop/empty the table then store latest permissions

        Schema::enableForeignKeyConstraints();

        // again enabling to maintain all relations (parent/child) will all other tables to ensure the smooth functioning

        $pages = $this->getPages();   // it is a php function from php.net which returns an array.

        foreach ($pages as $page) {
            Pages::firstOrCreate($page);
        }
    }
    /**
     * Get the permissions data from an external source (e.g., array, configuration file).
     */
    private function getPages()
    {
        return [
            [
                'name' => 'HomePage',
            ],
            [
                'name' => 'Category',
            ],
            [
                'name' => 'login',
            ],
            [
                'name' => 'register',
            ],
            [
                'name' => 'products',
            ],

        ];
    }
}
