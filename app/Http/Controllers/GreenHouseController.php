<?php

namespace App\Http\Controllers;

use App\Models\GreenHouse;
use App\Services\GreenHouse\GreenHouseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GreenHouseController extends Controller
{
    protected $greenHouseService;

    public function __construct(GreenHouseService $greenHouseService)
    {
        $this->greenHouseService = $greenHouseService;
    }

    public function index(): JsonResponse
    {
        $greenhouses = $this->greenHouseService->getAll();


        return response()->json($greenhouses);
    }

    public function show(int $id): JsonResponse
    {
        $greenhouse = $this->greenHouseService->getById($id);
        return response()->json($greenhouse);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:20240'
        ]);

        $greenhouse = $this->greenHouseService->create(
            $validated,
            $request->file('image')
        );

        return response()->json($greenhouse, 201);
    }

    public function update(Request $request, GreenHouse $greenhouse): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ]);

        $greenhouse = $this->greenHouseService->update(
            $greenhouse,
            $validated,
            $request->file('image')
        );

        return response()->json($greenhouse);
    }

    public function destroy(GreenHouse $greenhouse): JsonResponse
    {
        $this->greenHouseService->delete($greenhouse);
        return response()->json(null, 204);
    }
} 