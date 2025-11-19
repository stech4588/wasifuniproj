<?php

namespace Database\Seeders;

use App\Models\Setting\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sn = Setting::where(['module_name' => 'product', 'name' => 'serial_no'])->first();
        if(!$sn){
            DB::table('settings')->insert([
                'module_name' => 'product',
                'name' => 'serial_no',
                'value' => 0,
                'created_by' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }

        $in = Setting::where(['module_name' => 'invoice', 'name' => 'invoice_no'])->first();
        if(!$in){
            DB::table('settings')->insert([
                'module_name' => 'invoice',
                'name' => 'invoice_no',
                'value' => 0,
                'created_by' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
