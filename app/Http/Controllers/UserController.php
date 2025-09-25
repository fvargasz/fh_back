<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use function Pest\Laravel\json;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            $user->password = bcrypt('defaultpassword'); // Add required fields
            $user->save();

            return response()->json([
                'message' => 'User created successfully',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ], 201); // 201 for created
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create user',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
