<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::query()
            ->when($request->string('search')->trim()->toString(), function ($builder, string $search): void {
                $builder->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->when($request->string('purpose')->trim()->toString(), fn ($builder, string $purpose) => $builder->where('purpose', $purpose))
            ->when($request->string('status')->trim()->toString(), fn ($builder, string $status) => $builder->where('status', $status))
            ->latest();

        $messages = $query->paginate(20)->withQueryString();
        $purposes = ContactMessage::PURPOSES;
        $statuses = ContactMessage::STATUSES;

        return view('admin.contact-messages.index', compact('messages', 'purposes', 'statuses'));
    }

    public function show(ContactMessage $contactMessage)
    {
        if ($contactMessage->status === ContactMessage::STATUS_NEW) {
            $contactMessage->setStatus(ContactMessage::STATUS_READ);
            $contactMessage->save();
        }

        $statuses = ContactMessage::STATUSES;

        return view('admin.contact-messages.show', compact('contactMessage', 'statuses'));
    }

    public function update(Request $request, ContactMessage $contactMessage)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(ContactMessage::STATUSES)],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $contactMessage->setStatus($data['status']);
        $contactMessage->admin_note = $data['admin_note'] ?? null;
        $contactMessage->save();

        return back()->with('status', 'Contact message updated.');
    }
}
