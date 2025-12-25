<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // validate, create user, return token
    }

    public function login(Request $request)
    {
        // authenticate and return token
    }

    public function logout(Request $request)
    {
        // invalidate token
    }

    public function refresh(Request $request)
    {
        // refresh JWT token
    }
}
