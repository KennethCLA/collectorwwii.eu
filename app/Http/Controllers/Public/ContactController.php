<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\ContactSubmitted;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $contact = Contact::create($validated);

        $notifyAddress = config('collector.contact_notify_address')
            ?? User::where('role_id', 1)->first()?->email;

        if ($notifyAddress) {
            Mail::to($notifyAddress)->send(new ContactSubmitted($contact));
        }

        return back()->with('success', 'Your message has been sent successfully!');
    }
}
