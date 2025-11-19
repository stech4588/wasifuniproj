<?php
namespace App\Traits;

use App\Models\Image\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ImageUpload
{
    private function imageUpload($images, $folderName, $moduleName, $moduleId, $userId)
    {
        if (!is_array($images)) {
            $images = [$images];
        }

        $images = array_filter($images);

        if (empty($images)) {
            return [];
        }

        $imageNames = [];
        Image::where('module_id', $moduleId)->where('module_name', $moduleName)->forceDelete();

        $path = 'images/' . $folderName;
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory($path);
        }

        $isFirstImage = true;
        foreach ($images as $image) {
            if (!$image instanceof UploadedFile) {
                continue;
            }

            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $sanitizedName = Str::slug($originalName) ?: 'image';
            $imageName = $sanitizedName . '_' . uniqid('', true) . '.' . $extension;

            Storage::disk('public')->putFileAs($path, $image, $imageName);

            Image::create([
                'module_name' => $moduleName,
                'module_id' => $moduleId,
                'image' => $imageName,
                'default' => $isFirstImage ? 1 : 0,
                'created_by' => $userId,
            ]);

            $imageNames[] = $imageName;
            $isFirstImage = false;
        }

        return $imageNames;
    }
}
