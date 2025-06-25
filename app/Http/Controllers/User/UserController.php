<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Enums\Users\RoleEnum;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames()->toArray()
            ];
        });
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'roles' => 'required|array'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->syncRoles($validated['roles']);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames()->toArray()
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'roles' => 'required|array'
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($request->has('password') && $request->password) {
            $request->validate(['password' => 'string|min:8']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles($validated['roles']);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames()->toArray()
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }

    public function generateHR()
    {
        try {
            // Генерируем UUID
            $uuid = Str::uuid()->toString();
            
            Log::info('Starting HR account generation with UUID: ' . $uuid);
            
            // Находим все HR аккаунты
            $hrAccounts = User::where('email', 'like', 'hr%@hr.ru')
                ->orderBy('email', 'asc')
                ->get();
                
            Log::info('Found existing HR accounts: ' . $hrAccounts->pluck('email')->implode(', '));
            
            // Находим следующий свободный номер
            $hrNumber = 1;
            $email = "hr{$hrNumber}@hr.ru";
            $attempts = 0;
            
            while (User::where('email', $email)->exists()) {
                $hrNumber++;
                $email = "hr{$hrNumber}@hr.ru";
                $attempts++;
                
                // Защита от бесконечного цикла
                if ($attempts > 100) {
                    throw new \Exception('Не удалось найти свободный email для HR аккаунта после 100 попыток');
                }
            }
            
            Log::info('Selected email for new HR account: ' . $email);
            
            // Создаем нового пользователя
            $user = User::create([
                'name' => $uuid,
                'email' => $email,
                'password' => Hash::make($uuid),
            ]);

            // Назначаем роль HR
            $user->assignRole(RoleEnum::HR->value);

            Log::info('HR account created successfully: ' . $user->email);

            return response()->json([
                'success' => true,
                'uuid' => $uuid,
                'email' => $user->email,
                'password' => $uuid
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error generating HR account: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании HR аккаунта: ' . $e->getMessage()
            ], 500);
        }
    }
}
