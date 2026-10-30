<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\ContactSubmitted;
use App\Models\Contact;
use App\Models\User;
use App\Services\ContactSpamAssessor;
use App\Services\TurnstileVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function store(Request $request, TurnstileVerifier $turnstile, ContactSpamAssessor $spamAssessor)
    {
        // Silently accept bot submissions that fill the hidden field. This
        // avoids teaching bots which field exposed them and sends no mail.
        if (filled($request->input('website'))) {
            return back()->with('success', 'Your message has been sent successfully!');
        }

        $validated = $request->validate([
            'name' => ['bail', 'required', 'string', 'max:255'],
            'email' => ['bail', 'required', 'email:rfc', 'max:255'],
            'message' => ['bail', 'required', 'string', 'max:5000'],
            'cf-turnstile-response' => [config('services.turnstile.secret_key') ? 'required' : 'nullable', 'string'],
        ]);

        if (! $turnstile->passes((string) $request->input('cf-turnstile-response'), $request->ip())) {
            return back()->withInput()->withErrors([
                'cf-turnstile-response' => 'Security verification failed. Please try again.',
            ]);
        }

        unset($validated['cf-turnstile-response']);

        $assessment = $spamAssessor->assess($validated['message']);

        $contact = new Contact($validated);
        $contact->spam_score = $assessment['score'];
        $contact->spam_reasons = $assessment['reasons'];
        $contact->mail_suppressed_at = $assessment['shouldSuppress'] ? now() : null;
        $contact->save();

        $notifyAddress = config('collector.contact_notify_address')
            ?? User::where('role_id', 1)->first()?->email;

        if (! $assessment['shouldSuppress'] && $notifyAddress) {
            Mail::to($notifyAddress)->send(new ContactSubmitted($contact));
        }

        return back()->with('success', 'Your message has been sent successfully!');
    }
}
