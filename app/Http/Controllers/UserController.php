<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class UserController extends Controller
{
    //
    public function index()
    {
        return view('livewire.admin.users');
    }
}
