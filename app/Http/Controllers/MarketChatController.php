<?php

namespace App\Http\Controllers;

use App\Models\MarketMessage;
use App\Models\MarketNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarketChatController extends Controller
{
    public function index(Request $request)
    {
        $contacts = User::whereKeyNot($request->user()->id)
            ->where('status', 'ACTIVE')
            ->orderBy('usertype')
            ->orderBy('firstname')
            ->get();
        $selected = $request->filled('user')
            ? $contacts->firstWhere('id', (int) $request->integer('user'))
            : $contacts->first();

        $messages = collect();
        if ($selected) {
            $messages = MarketMessage::query()
                ->where(fn ($query) => $query->where('sender_id', $request->user()->id)->where('recipient_id', $selected->id))
                ->orWhere(fn ($query) => $query->where('sender_id', $selected->id)->where('recipient_id', $request->user()->id))
                ->oldest()
                ->get();

            MarketMessage::where('sender_id', $selected->id)
                ->where('recipient_id', $request->user()->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return view('market.portal.chat', [
            'pageTitle' => 'Messages',
            'contacts' => $contacts,
            'selected' => $selected,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'recipient_id' => ['required', 'exists:users,id'],
            'body' => ['nullable', 'string', 'max:5000', 'required_without:attachment'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $messageData = [
            'sender_id' => $request->user()->id,
            'recipient_id' => $data['recipient_id'],
            'body' => $data['body'] ?? '',
        ];

        if ($request->hasFile('attachment')) {
            $messageData['attachment_path'] = $request->file('attachment')->store('messages', 'public');
            $messageData['attachment_name'] = $request->file('attachment')->getClientOriginalName();
        }

        MarketMessage::create($messageData);
        MarketNotification::create([
            'user_id' => $data['recipient_id'],
            'type' => 'MESSAGE',
            'title' => 'New message from '.$request->user()->full_name,
            'message' => \Illuminate\Support\Str::limit($data['body'] ?? 'Sent an attachment.', 160),
            'action_url' => route('chat.index', ['user' => $request->user()->id]),
        ]);

        return redirect()->route('chat.index', ['user' => $data['recipient_id']]);
    }

    public function attachment(Request $request, MarketMessage $message)
    {
        abort_unless(
            $message->sender_id === $request->user()->id || $message->recipient_id === $request->user()->id,
            403
        );
        abort_unless($message->attachment_path && Storage::disk('public')->exists($message->attachment_path), 404);

        return Storage::disk('public')->response($message->attachment_path, $message->attachment_name, [
            'Content-Disposition' => 'inline; filename="'.$message->attachment_name.'"',
        ]);
    }
}
