<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class EventController extends Controller
{
    //
    public function index()
    {
        return view('livewire.admin.events');
    }
}
