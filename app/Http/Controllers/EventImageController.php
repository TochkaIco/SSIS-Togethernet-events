<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Facades\Storage;

class EventImageController extends Controller
{
    public function destroy(Event $event)
    {
        Storage::disk('public')->delete($event->image_path);
        $event->update(['image_path' => null]);

        return back();
    }
}
