<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\ContactMessage;
use App\Models\LivestockInspection;
use App\Models\MarketNotification;
use App\Models\Stall;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MarketHomeController extends Controller
{
    public function index()
    {
        $announcements = Announcement::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->limit(3)
            ->get();

        $stallCounts = Stall::query()
            ->selectRaw('section, count(*) as total')
            ->selectRaw("sum(case when status = 'AVAILABLE' then 1 else 0 end) as available")
            ->groupBy('section')
            ->orderBy('section')
            ->get();
        $settings = SystemSetting::pluck('value', 'key');

        return view('market.home', compact('announcements', 'stallCounts', 'settings'));
    }

    public function contact(Request $request)
    {
        $contact = ContactMessage::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:5000'],
        ]));

        User::where('usertype', User::ROLE_ADMINISTRATOR)
            ->where('status', 'ACTIVE')
            ->each(fn (User $user) => MarketNotification::create([
                'user_id' => $user->id,
                'type' => 'CONTACT',
                'title' => 'New public contact message',
                'message' => "{$contact->name}: ".\Illuminate\Support\Str::limit($contact->message, 140),
                'action_url' => route('administrator.contacts'),
            ]));

        return back()->with('success', 'Your message was sent to the market administration.');
    }

    public function service(Request $request, string $screen)
    {
        abort_unless(in_array($screen, [
            'contact',
            'stall-rental',
            'stall-location',
            'stall-application',
            'slaughtered-inspection',
            'inspection-request',
            'announcements',
        ], true), 404);

        return view('market.public.service', [
            'screen' => $screen,
            'announcements' => Announcement::where('is_published', true)->latest('published_at')->get(),
            'stalls' => Stall::orderBy('section')->orderBy('stall_number')->get()->groupBy('section'),
            'settings' => SystemSetting::pluck('value', 'key'),
        ]);
    }

    public function publicInspection(Request $request)
    {
        $data = $request->validate([
            'livestock_type' => ['required', 'in:POULTRY,PORK,BEEF'],
            'owner_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'animal_count' => ['required', 'integer', 'min:1'],
        ]);

        $inspection = LivestockInspection::create([
            ...$data,
            'tenant_id' => null,
            'request_source' => 'PUBLIC',
            'request_number' => 'INSP-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'status' => 'PENDING',
        ]);

        User::where('usertype', User::ROLE_INSPECTOR)
            ->where('status', 'ACTIVE')
            ->each(fn (User $user) => MarketNotification::create([
                'user_id' => $user->id,
                'type' => 'INSPECTION',
                'title' => 'New public inspection request',
                'message' => "{$inspection->request_number}: {$inspection->livestock_type} inspection requested.",
                'action_url' => route('inspector.inspections'),
            ]));

        return back()->with('success', "Inspection request {$inspection->request_number} was submitted.");
    }
}
