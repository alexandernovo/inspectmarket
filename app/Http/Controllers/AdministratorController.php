<?php

namespace App\Http\Controllers;

use App\Models\CashTicketCollection;
use App\Models\ContactMessage;
use App\Models\LivestockInspection;
use App\Models\MarketNotification;
use App\Models\Payment;
use App\Models\Stall;
use App\Models\StallApplication;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdministratorController extends Controller
{
    public function dashboard()
    {
        return view('market.portal.dashboard', [
            'pageTitle' => 'Administrator Dashboard',
            'stats' => [
                ['label' => 'Cash Collected', 'value' => '₱'.number_format(CashTicketCollection::sum('amount'), 2), 'icon' => 'bi-cash-stack'],
                ['label' => 'Tenant Inspected', 'value' => LivestockInspection::where('status', 'COMPLETED')->distinct('tenant_id')->count('tenant_id'), 'icon' => 'bi-clipboard2-check'],
                ['label' => 'Stall Collection', 'value' => '₱'.number_format(Payment::where('status', 'PAID')->sum('amount'), 2), 'icon' => 'bi-wallet2'],
                ['label' => 'Total Members', 'value' => User::count(), 'icon' => 'bi-people-fill'],
            ],
            'rows' => StallApplication::with(['tenant', 'stall'])->latest()->limit(8)->get(),
            'rowType' => 'applications',
            'chartTitle' => 'E-Inspect public market data',
        ]);
    }

    public function members(Request $request, string $role)
    {
        $role = strtoupper($role);
        abort_unless(in_array($role, [
            User::ROLE_TREASURER,
            User::ROLE_CLERK,
            User::ROLE_INSPECTOR,
            User::ROLE_TENANT,
        ], true), 404);

        $members = User::where('usertype', $role)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($nested) use ($search) {
                    $nested->where('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('phone_num', 'like', "%{$search}%");
                });
            })
            ->orderBy('lastname')
            ->orderBy('firstname')
            ->paginate(15)
            ->withQueryString();

        return view('market.portal.members', [
            'pageTitle' => ucfirst(strtolower($role)).' Records',
            'role' => $role,
            'members' => $members,
        ]);
    }

    public function roleRecords(string $role, ?string $area = null)
    {
        $role = strtolower($role);
        $areas = match ($role) {
            'treasurer' => ['stall-rental', 'cash-ticket', 'report'],
            'clerk' => ['cash-ticket', 'stall-rental', 'report'],
            'inspector' => ['inspection', 'report'],
            'tenant' => ['stall-rental', 'payment', 'inspection'],
            default => abort(404),
        };
        if ($area === null) {
            return view('market.portal.role-records', [
                'pageTitle' => ucfirst($role).' Records',
                'roleRecord' => $role,
                'areas' => $areas,
                'area' => null,
                'rows' => collect(),
            ]);
        }
        abort_unless(in_array($area, $areas, true), 404);

        $rows = match ($area) {
            'cash-ticket' => CashTicketCollection::with('collector')->latest('collection_date')->get(),
            'inspection' => LivestockInspection::with(['tenant', 'inspector'])->latest('scheduled_at')->get(),
            'payment' => Payment::with('tenant')->latest('due_date')->get(),
            'report' => collect(),
            default => StallApplication::with(['tenant', 'stall'])->latest()->get(),
        };

        return view('market.portal.role-records', [
            'pageTitle' => ucfirst($role).' '.str($area)->replace('-', ' ')->title(),
            'roleRecord' => $role,
            'areas' => $areas,
            'area' => $area,
            'rows' => $rows,
        ]);
    }

    public function storeMember(Request $request, string $role)
    {
        $role = strtoupper($role);
        abort_unless(in_array($role, [
            User::ROLE_TREASURER,
            User::ROLE_CLERK,
            User::ROLE_INSPECTOR,
            User::ROLE_TENANT,
        ], true), 404);

        $data = $request->validate([
            'firstname' => ['required', 'string', 'max:100'],
            'middlename' => ['nullable', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone_num' => ['required', 'string', 'max:30', 'unique:users,phone_num'],
            'address' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:150'],
            'password' => ['required', 'min:8'],
        ]);

        User::create([
            ...$data,
            'usertype' => $role,
            'status' => 'ACTIVE',
            'phone_verified_at' => now(),
        ]);

        return back()->with('success', ucfirst(strtolower($role)).' account created.');
    }

    public function updateMember(Request $request, User $user)
    {
        $data = $request->validate([
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
            'designation' => ['nullable', 'string', 'max:150'],
        ]);

        $user->update(array_filter($data, fn ($value) => $value !== null));
        MarketNotification::create([
            'user_id' => $user->id,
            'type' => 'ACCOUNT',
            'title' => 'Account status updated',
            'message' => "Your E-Inspect account is now {$user->status}.",
            'action_url' => route('profile'),
        ]);

        return back()->with('success', 'Member status updated.');
    }

    public function reports(Request $request)
    {
        $report = $request->string('report', 'stall-rental')->toString();

        $rows = match ($report) {
            'cash-ticket' => CashTicketCollection::with('collector')->latest('collection_date')->get(),
            'inspection' => LivestockInspection::with(['tenant', 'inspector'])->latest('scheduled_at')->get(),
            'payments' => Payment::with('tenant')->latest('due_date')->get(),
            default => StallApplication::with(['tenant', 'stall'])->latest()->get(),
        };

        return view('market.portal.reports', [
            'pageTitle' => 'Market Reports',
            'report' => $report,
            'rows' => $rows,
        ]);
    }

    public function stallMap()
    {
        return view('market.portal.stall-map', [
            'pageTitle' => 'Public Market Stall Map',
            'stalls' => Stall::orderBy('section')->orderBy('stall_number')->get()->groupBy('section'),
            'canEdit' => true,
        ]);
    }

    public function updateStall(Request $request, Stall $stall)
    {
        $stall->update($request->validate([
            'status' => ['required', Rule::in(['AVAILABLE', 'OCCUPIED', 'MAINTENANCE'])],
            'monthly_rate' => ['required', 'numeric', 'min:0'],
        ]));

        return back()->with('success', "Stall {$stall->section} #{$stall->stall_number} updated.");
    }

    public function contacts()
    {
        return view('market.portal.contacts', [
            'pageTitle' => 'Contact Messages',
            'messages' => ContactMessage::latest()->paginate(20),
        ]);
    }

    public function readContact(ContactMessage $contact)
    {
        $contact->update(['read_at' => now()]);

        return back()->with('success', 'Contact message marked as read.');
    }

    public function settings()
    {
        return view('market.portal.settings', [
            'pageTitle' => 'System Settings',
            'settings' => SystemSetting::pluck('value', 'key'),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'market_name' => ['required', 'string', 'max:255'],
            'market_address' => ['required', 'string', 'max:255'],
            'market_email' => ['nullable', 'email', 'max:255'],
            'market_phone' => ['nullable', 'string', 'max:30'],
            'cash_ticket_value' => ['required', 'numeric', 'min:0'],
            'inspection_lead_days' => ['required', 'integer', 'min:0', 'max:60'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                SystemSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => (string) $value, 'group' => 'GENERAL']
                );
            }
        });

        return back()->with('success', 'System settings saved.');
    }
}
