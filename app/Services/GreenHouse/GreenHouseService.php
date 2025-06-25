<?php

namespace App\Services\GreenHouse;

use App\Models\GreenHouse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class GreenHouseService
{
    public function getAll()
    {
        return GreenHouse::all();
    }

    public function getById(int $id)
    {
        return GreenHouse::findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $image = null)
    {
        if ($image) {
            $path = $image->store('greenhouses', 'public');
            $data['image_url'] = '/storage/' . $path;
         
        }
        return GreenHouse::create($data);
    }

    public function update(GreenHouse $greenhouse, array $data, ?UploadedFile $image = null)
    {
        if ($image) {
            if ($greenhouse->image_url) {
                $oldPath = str_replace('/storage/', '', $greenhouse->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            
            $path = $image->store('greenhouses', 'public');
            $data['image_url'] = '/storage/' . $path;
         
        }

        $greenhouse->update($data);
        return $greenhouse;
    }

    public function delete(GreenHouse $greenhouse)
    {
        if ($greenhouse->image_url) {
            $path = str_replace('/storage/', '', $greenhouse->image_url);
            Storage::disk('public')->delete($path);
           
        }
        
        return $greenhouse->delete();
    }
} 