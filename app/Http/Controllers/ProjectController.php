<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::orderBy('order')->get();
        
        $projects->transform(function ($project) {
            if ($project->image) {
                $project->image = url('storage/' . $project->image);
            }
            return $project;
        });

        return response()->json($projects);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'required|string',
                'order' => 'required|integer'
            ]);

            if (str_starts_with($request->image, 'data:image')) {
                $imageData = $request->image;
                $imageName = 'projects/project_' . time() . '.png';
                $image = explode(',', $imageData)[1];
                Storage::disk('public')->put($imageName, base64_decode($image));
                $data['image'] = $imageName;
            }

            $project = Project::create($data);
            
            if ($project->image) {
                $project->image = url('storage/' . $project->image);
            }

            return response()->json($project);
        } catch (\Throwable $e) {
            Log::error('Ошибка создания проекта: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка на сервере: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Project $project)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'required|string',
                'order' => 'required|integer'
            ]);

            if ($request->has('image') && str_starts_with($request->image, 'data:image')) {
                if ($project->image) {
                    Storage::disk('public')->delete($project->image);
                }
                
                $imageData = $request->image;
                $imageName = 'projects/project_' . time() . '.png';
                $image = explode(',', $imageData)[1];
                Storage::disk('public')->put($imageName, base64_decode($image));
                $data['image'] = $imageName;
            }

            $project->update($data);
            
            if ($project->image) {
                $project->image = url('storage/' . $project->image);
            }

            return response()->json($project);
        } catch (\Throwable $e) {
            Log::error('Ошибка обновления проекта: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка на сервере: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Project $project)
    {
        try {
            if ($project->image) {
                Storage::disk('public')->delete($project->image);
            }
            $project->delete();
            return response()->json(['message' => 'Project successfully deleted']);
        } catch (\Throwable $e) {
            Log::error('Ошибка удаления проекта: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка на сервере: ' . $e->getMessage()
            ], 500);
        }
    }
} 