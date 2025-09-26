<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use function Pest\Laravel\json;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function get()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function login(Request $request)
    {
        if (!$request->has('email') || empty($request->email)) {
            return response()->json(['error' => 'email field is required'], 400);
        }

        if (!$request->has('password') || empty($request->password)) {
            return response()->json(['error' => 'password field is required'], 400);
        }
        
        $user = User::where('email', $request->email)->first();
        Log::info($user);

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json(([
            'message' => 'Login successful',
            'user' => $user->only(['name', 'email', 'id']),
            'token' => $token,
        ]));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
        if (!$request->has('name') || empty($request->name)) {
            return response()->json(['error' => 'name field is required'], 400);
        }

        if (!$request->has('password') || empty($request->password)) {
            return response()->json(['error' => 'password field is required'], 400);
        }

        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email ?? 'default@example.com'; // Add required fields
            $user->password = bcrypt($request->password); // Add required fields
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user->only(['name', 'email', 'id']),
                'token' => $token,
            ], 201); // 201 for created
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create user',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user()->currentaccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function getActiveUser(Request $request) {
        return response()->json([
            'success' => true,
            'user' => $request->user()->only(['id', 'name', 'email'])
            
        ]);
    }
}
