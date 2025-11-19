<?php

use App\Models\Setting\Setting;

function getNextNumber($moduleName, $name, $userId)
{
    $setting = Setting::where(['module_name' => $moduleName, 'name' => $name])->where('created_by', $userId)->withTrashed()->first();

    if ($setting) {
        $nextNo = $setting->value + 1;
        $setting->update(['value' => $nextNo]);
        return $nextNo;
    }

    $newSetting = Setting::create([
        'module_name' => $moduleName,
        'name'        => $name,
        'value'       => 1,
        'created_by'  => $userId,
    ]);

    return $newSetting->value;
}
