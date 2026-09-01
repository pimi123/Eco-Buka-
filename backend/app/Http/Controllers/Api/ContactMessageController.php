<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    public function store(StoreContactMessageRequest $request)
    {
        $data = $request->validated();

        $message = ContactMessage::create([
            ...$data,
            'status' => ContactMessage::STATUS_NEW,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return response()->json([
            'message' => 'Mesazhi u dergua me sukses. Ekipi yne do tju kontaktoje se shpejti.',
            'id' => $message->id,
            'status' => $message->status,
        ], 201);
    }
}
