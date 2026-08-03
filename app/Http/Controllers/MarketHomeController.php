<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\ContactMessage;
use App\Models\LivestockInspection;
use App\Models\MarketNotification;
use App\Models\Stall;
use App\Models\StallApplication;
use App\Models\StallApplicationDocument;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'inspections' => LivestockInspection::latest('scheduled_at')->get()->groupBy('livestock_type'),
            'stalls' => Stall::orderBy('section')->orderBy('stall_number')->get()->groupBy('section'),
            'settings' => SystemSetting::pluck('value', 'key'),
        ]);
    }

    public function roles(string $mode)
    {
        abort_unless(in_array($mode, ['register', 'login'], true), 404);

        return view('market.public.roles', [
            'mode' => $mode,
            'announcementCount' => Announcement::where('is_published', true)->count(),
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

        $tenant = $request->user()?->isRole(User::ROLE_TENANT) ? $request->user() : null;

        $inspection = LivestockInspection::create([
            ...$data,
            'tenant_id' => $tenant?->id,
            'request_source' => $tenant ? 'TENANT' : 'PUBLIC',
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

    public function publicStallApplication(Request $request)
    {
        $data = $request->validate([
            'business_owner' => ['required', 'string', 'max:255'],
            'tin_number' => ['required', 'string', 'max:15', 'regex:/^\d{3}-?\d{3}-?\d{3}(?:-?\d{3})?$/'],
            'birth_date' => ['required', 'date', 'before:today'],
            'civil_status' => ['required', 'string', 'max:30'],
            'sex' => ['required', 'in:MALE,FEMALE'],
            'email' => ['required', 'email', 'max:255'],
            'contact_number' => ['required', 'string', 'max:30'],
            'business_address' => ['required', 'string', 'max:255'],
            'business_name' => ['required', 'string', 'max:255'],
            'business_category' => ['required', 'string', 'max:255'],
            'business_nature' => ['required', 'string', 'max:255'],
            'trade_name' => ['required', 'string', 'max:255'],
            'permit_issued_at' => ['nullable', 'date'],
            'other_business' => ['nullable', 'string', 'max:255'],
            'preferred_section' => ['required', 'in:FISH,PORK,POULTRY,BEEF,MIXED'],
            'preferred_stall_number' => ['nullable', 'integer', 'min:1'],
            'documents' => ['required', 'array', 'min:1', 'max:5'],
            'documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ]);

        $documents = $data['documents'];
        unset($data['documents']);

        $application = DB::transaction(function () use ($data, $documents) {
            $application = StallApplication::create([
                ...$data,
                'tenant_id' => null,
                'request_source' => 'PUBLIC',
                'application_number' => 'APP-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'status' => 'PENDING',
            ]);

            foreach ($documents as $document) {
                StallApplicationDocument::create([
                    'stall_application_id' => $application->id,
                    'document_type' => 'PUBLIC_REQUIREMENT',
                    'path' => $document->store("stall-applications/{$application->id}", 'public'),
                    'original_name' => $document->getClientOriginalName(),
                    'mime_type' => $document->getClientMimeType(),
                    'size' => $document->getSize(),
                ]);
            }

            return $application;
        });

        User::where('usertype', User::ROLE_TREASURER)
            ->where('status', 'ACTIVE')
            ->each(fn (User $user) => MarketNotification::create([
                'user_id' => $user->id,
                'type' => 'STALL_APPLICATION',
                'title' => 'New public stall application',
                'message' => "{$application->application_number}: {$application->business_name}.",
                'action_url' => route('treasurer.rentals'),
            ]));

        return back()->with('success', "Application {$application->application_number} and its documents were submitted.");
    }
}
