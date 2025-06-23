<?php

namespace App\Http\Controllers;

use App\Models\HeroProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class HeroProfileController extends Controller
{
    public function show($id = 1)
    {
        $hero = HeroProfile::findOrFail($id);
        
        if ($hero->avatar) {
            $hero->avatar = url('storage/' . $hero->avatar);
        }
        
        return response()->json($hero);
    }

    public function update(Request $request, $id = 1)
    {
        try {
            $data = $request->validate([
                'full_name' => 'required|string|max:255',
                'specialization' => 'required|string|max:255',
                'bio' => 'required|string',
                'birth_date' => 'required|date',
                'city' => 'required|string|max:255',
                'education' => 'required|string',
                'experience_start' => 'required|date',
                'stack' => 'required|string',
                'avatar' => 'nullable|string'
            ]);

            $hero = HeroProfile::findOrFail($id);

            if ($request->has('avatar') && str_starts_with($request->avatar, 'data:image')) {
                // Удаляем старое изображение
                if ($hero->avatar) {
                    Storage::disk('public')->delete($hero->avatar);
                }
                
                $imageData = $request->avatar;
                $imageName = 'avatars/hero_' . time() . '.png';
                $image = explode(',', $imageData)[1];
                Storage::disk('public')->put($imageName, base64_decode($image));
                $data['avatar'] = $imageName;
            }

            $hero->update($data);
            
            if ($hero->avatar) {
                $hero->avatar = url('storage/' . $hero->avatar);
            }

            return response()->json($hero);
        } catch (\Throwable $e) {
            \Log::error('Ошибка обновления профиля: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка на сервере: ' . $e->getMessage()
            ], 500);
        }
    }
} 