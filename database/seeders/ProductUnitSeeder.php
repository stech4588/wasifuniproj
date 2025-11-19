<?php

namespace Database\Seeders;

use App\Models\Product\ProductUnit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ProductUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $kg = ProductUnit::where(['name' => 'kg'])->first();
        if(!$kg){
            DB::table('product_units')->insert([
                'name' => 'kg',
                'created_by' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }

        $piece = ProductUnit::where(['name' => 'piece'])->first();
        if(!$piece){
            DB::table('product_units')->insert([
                'name' => 'piece',
                'created_by' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }

        $litre = ProductUnit::where(['name' => 'litre'])->first();
        if(!$litre){
            DB::table('product_units')->insert([
                'name' => 'litre',
                'created_by' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }

    }
}
