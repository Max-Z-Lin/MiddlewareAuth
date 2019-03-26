<?php

namespace App\Repository;
use App\User;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    protected $user;

    public function __construct(User $user)
    {
        $this->users = $user;
    }

    public function createUser($request)
    {
        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'gender' => $request->get('gender'),
            'password' => Hash::make($request->get('password')),
        ]);

        return $user;
    }

    public function readUser($request)
    {
        $data = User::find($request['id']);

        return $data;
    }

    public function updateUser($request)
    {
        $data = User::find($request['id']);
        $data->update([
            'name' => $request['name'] ?? $data->name,
            'gender' => $request['gender'] ?? $data->gender,
            'email' => $request['email'] ?? $data->email,
            'password' => Hash::make($request['password']) ?? $data->password
        ]);
        return $data;
    }

    public function deleteUser($request)
    {
        $data = User::find($request['id']);
        $data -> delete();

        if ($data->trashed()) {
            return true;
        } else {
            return false;
        }

    }
}