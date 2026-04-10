<?php

declare(strict_types=1);

namespace App\Http\Controllers\Filament;

use App\Services\TelavoxService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OutgoingSmsController extends Controller
{
    public function store(Request $request, TelavoxService $telavox)
    {
        $data = $request->validate([
            'number' => ['required', 'string'],
            'message' => ['required', 'string'],
        ]);

        $ok = $telavox->sendSmsOk($data['number'], $data['message']);

        if ($ok) {
            return back()->with('status', 'Meddelande skickat');
        }

        return back()->with('status', 'Misslyckades att skicka meddelande');
    }
}
