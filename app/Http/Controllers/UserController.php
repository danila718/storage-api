<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a user info.
     *
     * @return \Illuminate\Http\Response
     */
    public function info(Request $request)
    {
        return $request->user();
    }
}
