<?php

namespace App\Http\Controllers;

use App\Models\CashTicketCollection;
use App\Models\CashTicketAssignment;
use App\Models\ContactMessage;
use App\Models\LivestockInspection;
use App\Models\MarketNotification;
use App\Models\Payment;
use App\Models\Stall;
use App\Models\StallApplication;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\ServerDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdministratorController extends Controller
{
    public function dashboard()
    {
        $cashTotal = (float) CashTicketCollection::sum('amount');
        $stallCollection = (float) Payment::where('status', 'PAID')->sum('amount');
        $tenantTotal = User::where('usertype', User::ROLE_TENANT)->count();
        $stallTotal = Stall::count();
        $stallAvailable = Stall::where('status', 'AVAILABLE')->count();

        return view('market.portal.dashboard', [
            'pageTitle' => 'Administrator Dashboard',
            'stats' => [
                ['label' => 'Total Collected Amount', 'value' => 'P'.number_format($cashTotal, 2), 'tone' => 'blue', 'icon' => 'bi-cash-stack'],
                ['label' => 'Total Inspected Tenants', 'value' => LivestockInspection::where('status', 'COMPLETED')->distinct('tenant_id')->count('tenant_id'), 'tone' => 'green', 'icon' => 'bi-person-check-fill'],
                ['label' => 'Total Cash Ticket', 'value' => number_format((int) CashTicketCollection::sum('ticket_quantity')), 'tone' => 'gold', 'icon' => 'bi-ticket-perforated-fill'],
                ['label' => 'Total Stalls', 'value' => $stallTotal, 'tone' => 'red', 'icon' => 'bi-shop-window'],
            ],
            'rows' => StallApplication::with(['tenant', 'stall'])->latest()->limit(8)->get(),
            'rowType' => 'applications',
            'chartTitle' => 'E-Inspect public market data',
            'dashboardTotals' => [
                'cash' => $cashTotal,
                'stall' => $stallCollection,
                'members' => User::count(),
                'stalls' => $stallTotal,
            ],
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

    public function roleRecords(Request $request, string $role, ?string $area = null)
    {
        $role = strtolower($role);
        $areas = match ($role) {
            'treasurer' => ['stall-rental', 'cash-ticket', 'report'],
            'clerk' => ['cash-ticket', 'stall-rental'],
            'inspector' => ['inspection'],
            'tenant' => ['payment'],
            default => abort(404),
        };

        if ($role === 'treasurer' && $area === null) {
            return view('market.portal.role-records', [
                'pageTitle' => 'Treasurer Records',
                'roleRecord' => $role,
                'areas' => $areas,
                'area' => null,
                'rows' => collect(),
                'directRecordArea' => 'stall-rental',
            ]);
        }

        if ($role === 'inspector' && $area === null) {
            return view('market.portal.role-records', [
                'pageTitle' => 'Inspector Records',
                'roleRecord' => $role,
                'areas' => $areas,
                'area' => null,
                'rows' => collect(),
                'directRecordArea' => 'inspection',
            ]);
        }

        if ($role === 'clerk' && $area === null) {
            return view('market.portal.role-records', [
                'pageTitle' => 'Clerk Records',
                'roleRecord' => $role,
                'areas' => $areas,
                'area' => null,
                'rows' => collect(),
            ]);
        }

        if ($role === 'tenant' && $area === null) {
            return view('market.portal.role-records', [
                'pageTitle' => 'Tenant Records',
                'roleRecord' => $role,
                'areas' => $areas,
                'area' => null,
                'rows' => collect(),
                'directRecordArea' => 'payment',
            ]);
        }

        if ($role === 'clerk' && $area === 'cash-ticket') {
            return view('market.portal.assignments', [
                'pageTitle' => 'Clerk Cash Ticket',
                'assignments' => CashTicketAssignment::with('collector')->latest('assigned_date')->get(),
                'collections' => CashTicketCollection::with(['collector', 'assignment'])->latest('collection_date')->get(),
                'collectors' => User::where('usertype', User::ROLE_CLERK)->where('status', 'ACTIVE')->orderBy('firstname')->get(),
                'collectorRecords' => User::where('usertype', User::ROLE_CLERK)->latest()->get(),
                'cashTicketTitle' => 'CLERK',
                'cashTicketBreadcrumb' => 'Dashboard | Clerk',
                'cashTicketTitleAvatar' => '3-Collector Clerk.png',
                'showCollectorAccess' => false,
            ]);
        }

        if ($role === 'clerk' && $area === 'stall-rental') {
            $query = Payment::query()
                ->with(['tenant', 'stallApplication.stall'])
                ->latest('due_date');

            if ($request->filled('section') && strtoupper($request->string('section')->toString()) !== 'ALL') {
                $section = strtoupper($request->string('section')->toString());
                $query->whereHas('stallApplication', fn ($builder) => $builder
                    ->where('preferred_section', $section)
                    ->orWhereHas('stall', fn ($stall) => $stall->where('section', $section)));
            }

            if ($request->filled('from')) {
                $query->whereDate('paid_at', '>=', $request->date('from'));
            }

            if ($request->filled('to')) {
                $query->whereDate('paid_at', '<=', $request->date('to'));
            }

            if ($request->filled('search')) {
                $search = $request->string('search')->toString();
                $query->where(function ($builder) use ($search) {
                    $builder->where('reference_number', 'like', "%{$search}%")
                        ->orWhereHas('tenant', fn ($tenant) => $tenant
                            ->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%"))
                        ->orWhereHas('stallApplication', fn ($application) => $application
                            ->where('business_owner', 'like', "%{$search}%")
                            ->orWhere('business_name', 'like', "%{$search}%"));
                });
            }

            $rentalStatus = fn (Payment $payment) => $payment->status === 'PAID'
                ? 'PAID'
                : (($payment->status === 'OVERDUE' || ($payment->due_date?->isPast() ?? false)) ? 'OVERDUE' : 'UNPAID');
            $filteredPayments = $query->get();
            $statusCounts = [
                'ALL' => $filteredPayments->count(),
                'OVERDUE' => $filteredPayments->filter(fn (Payment $payment) => $rentalStatus($payment) === 'OVERDUE')->count(),
                'PAID' => $filteredPayments->filter(fn (Payment $payment) => $rentalStatus($payment) === 'PAID')->count(),
                'UNPAID' => $filteredPayments->filter(fn (Payment $payment) => $rentalStatus($payment) === 'UNPAID')->count(),
            ];

            $payments = $filteredPayments->filter(function (Payment $payment) use ($request, $rentalStatus) {
                if (! $request->filled('status') || strtoupper($request->string('status')->toString()) === 'ALL') {
                    return true;
                }

                return $rentalStatus($payment) === strtoupper($request->string('status')->toString());
            })->values();

            $perPage = (int) $request->integer('per_page', 10);
            $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;
            $page = max(1, (int) $request->integer('page', 1));
            $visiblePayments = $payments->slice(($page - 1) * $perPage, $perPage)->values();
            $lastPage = max(1, (int) ceil($payments->count() / $perPage));

            return view('market.clerk.rentals', [
                'pageTitle' => 'Clerk Stall Rental',
                'payments' => $visiblePayments,
                'totalPayments' => $payments->count(),
                'perPage' => $perPage,
                'page' => $page,
                'lastPage' => $lastPage,
                'showTable' => true,
                'statusCounts' => $statusCounts,
                'rentalRoute' => 'administrator.records',
                'rentalRouteParams' => ['role' => 'clerk', 'area' => 'stall-rental'],
                'rentalHistoryRoute' => 'administrator.rentals.history.update',
                'rentalPageTitle' => 'CLERK',
                'rentalBreadcrumb' => 'Dashboard | Clerk',
                'rentalTitleAvatar' => '3-Collector Clerk.png',
            ]);
        }

        if ($role === 'inspector' && $area === 'inspection') {
            $type = strtoupper($request->string('type', 'POULTRY')->toString());
            $type = in_array($type, ['POULTRY', 'PORK', 'BEEF'], true) ? $type : 'POULTRY';

            return view('market.inspector.records', [
                'pageTitle' => 'Inspector Records',
                'livestockType' => $type,
                'inspectorPageTitle' => 'INSPECTOR',
                'inspectorPageBreadcrumb' => 'Dashboard | Inspector',
                'inspectorTitleAvatar' => '4-Sanitary Inspector.png',
                'inspectorShowTypeTabs' => true,
                'inspectorCanMutateRecords' => false,
                'inspectorDataTableRoute' => 'administrator.datatable.inspector-inspections',
                'inspectorRecordRoute' => 'administrator.records',
                'inspectorRecordRouteParams' => ['role' => 'inspector', 'area' => 'inspection'],
            ]);
        }

        if ($role === 'treasurer' && $area === 'all-tenants') {
            $query = Payment::query()
                ->with(['tenant', 'stallApplication.stall'])
                ->latest('due_date');

            if ($request->filled('section') && strtoupper($request->string('section')->toString()) !== 'ALL') {
                $section = strtoupper($request->string('section')->toString());
                $query->whereHas('stallApplication', fn ($builder) => $builder
                    ->where('preferred_section', $section)
                    ->orWhereHas('stall', fn ($stall) => $stall->where('section', $section)));
            }

            if ($request->filled('from')) {
                $query->whereDate('paid_at', '>=', $request->date('from'));
            }

            if ($request->filled('to')) {
                $query->whereDate('paid_at', '<=', $request->date('to'));
            }

            if ($request->filled('search')) {
                $search = $request->string('search')->toString();
                $query->where(function ($builder) use ($search) {
                    $builder->where('reference_number', 'like', "%{$search}%")
                        ->orWhereHas('tenant', fn ($tenant) => $tenant
                            ->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%"))
                        ->orWhereHas('stallApplication', fn ($application) => $application
                            ->where('business_owner', 'like', "%{$search}%")
                            ->orWhere('business_name', 'like', "%{$search}%"));
                });
            }

            $rentalStatus = fn (Payment $payment) => $payment->status === 'PAID'
                ? 'PAID'
                : (($payment->status === 'OVERDUE' || ($payment->due_date?->isPast() ?? false)) ? 'OVERDUE' : 'UNPAID');
            $filteredPayments = $query->get();
            $statusCounts = [
                'ALL' => $filteredPayments->count(),
                'OVERDUE' => $filteredPayments->filter(fn (Payment $payment) => $rentalStatus($payment) === 'OVERDUE')->count(),
                'PAID' => $filteredPayments->filter(fn (Payment $payment) => $rentalStatus($payment) === 'PAID')->count(),
                'UNPAID' => $filteredPayments->filter(fn (Payment $payment) => $rentalStatus($payment) === 'UNPAID')->count(),
            ];

            $payments = $filteredPayments->filter(function (Payment $payment) use ($request, $rentalStatus) {
                if (! $request->filled('status') || strtoupper($request->string('status')->toString()) === 'ALL') {
                    return true;
                }

                return $rentalStatus($payment) === strtoupper($request->string('status')->toString());
            })->values();

            $perPage = (int) $request->integer('per_page', 10);
            $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;
            $page = max(1, (int) $request->integer('page', 1));
            $visiblePayments = $payments->slice(($page - 1) * $perPage, $perPage)->values();
            $lastPage = max(1, (int) ceil($payments->count() / $perPage));

            return view('market.clerk.rentals', [
                'pageTitle' => 'Treasurer Records',
                'payments' => $visiblePayments,
                'totalPayments' => $payments->count(),
                'perPage' => $perPage,
                'page' => $page,
                'lastPage' => $lastPage,
                'showTable' => true,
                'rentalRoute' => 'administrator.records',
                'rentalRouteParams' => ['role' => 'treasurer', 'area' => 'all-tenants'],
                'rentalHistoryRoute' => 'administrator.rentals.history.update',
                'statusCounts' => $statusCounts,
                'rentalPageTitle' => 'TREASURER',
                'rentalBreadcrumb' => 'Dashboard | Treasurer',
                'rentalTitleAvatar' => '2-Treasurer.png',
            ]);
        }

        if ($role === 'treasurer' && $area === 'stall-rental') {
            $query = StallApplication::query()
                ->with(['tenant', 'stall', 'documents', 'payments'])
                ->latest();

            if ($request->filled('from')) {
                $query->whereDate('created_at', '>=', $request->date('from'));
            }

            if ($request->filled('to')) {
                $query->whereDate('created_at', '<=', $request->date('to'));
            }

            if ($request->filled('search')) {
                $search = $request->string('search')->toString();
                $query->where(function ($builder) use ($search) {
                    $builder->where('application_number', 'like', "%{$search}%")
                        ->orWhere('business_owner', 'like', "%{$search}%")
                        ->orWhere('business_address', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhereHas('tenant', fn ($tenant) => $tenant
                            ->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%")
                            ->orWhere('phone_num', 'like', "%{$search}%"));
                });
            }

            $filteredApplications = $query->get();
            $sectionCounts = collect(['MIXED', 'BEEF', 'PORK', 'POULTRY', 'FISH'])
                ->mapWithKeys(fn ($section) => [$section => $filteredApplications
                    ->filter(fn (StallApplication $application) => strtoupper($application->stall?->section ?? $application->preferred_section ?? '') === $section)
                    ->count()]);
            $statusCounts = [
                'ALL' => $filteredApplications->count(),
                'PENDING' => $filteredApplications->where('status', 'PENDING')->count(),
                'APPROVED' => $filteredApplications->where('status', 'APPROVED')->count(),
                'DISAPPROVED' => $filteredApplications->where('status', 'DISAPPROVED')->count(),
            ];

            if ($request->filled('section') && strtoupper($request->string('section')->toString()) !== 'ALL') {
                $section = strtoupper($request->string('section')->toString());
                $filteredApplications = $filteredApplications->filter(fn (StallApplication $application) => strtoupper($application->stall?->section ?? $application->preferred_section ?? '') === $section)->values();
            }

            if ($request->filled('status') && strtoupper($request->string('status')->toString()) !== 'ALL') {
                $filteredApplications = $filteredApplications->where('status', strtoupper($request->string('status')->toString()))->values();
            }

            $perPage = (int) $request->integer('per_page', 10);
            $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;
            $page = max(1, (int) $request->integer('page', 1));
            $visibleApplications = $filteredApplications->slice(($page - 1) * $perPage, $perPage)->values();
            $lastPage = max(1, (int) ceil($filteredApplications->count() / $perPage));
            $tenantRentalPayments = Payment::query()
                ->with(['tenant', 'stallApplication.stall', 'stallApplication.documents'])
                ->when(
                    $request->filled('section') && strtoupper($request->string('section')->toString()) !== 'ALL',
                    function ($query) use ($request) {
                        $section = strtoupper($request->string('section')->toString());

                        $query->whereHas('stallApplication', fn ($builder) => $builder
                            ->where('preferred_section', $section)
                            ->orWhereHas('stall', fn ($stall) => $stall->where('section', $section)));
                    },
                    fn ($query) => $query->whereHas('stallApplication', fn ($builder) => $builder
                        ->where('preferred_section', 'FISH')
                        ->orWhereHas('stall', fn ($stall) => $stall->where('section', 'FISH')))
                )
                ->latest('due_date')
                ->get()
                ->unique(fn (Payment $payment) => $payment->tenant_id.'-'.($payment->stall_application_id ?: 0))
                ->values();

            return view('market.treasurer.tenant-stalls', [
                'pageTitle' => 'Treasurer Stall Rental',
                'applications' => $visibleApplications,
                'totalApplications' => $filteredApplications->count(),
                'perPage' => $perPage,
                'page' => $page,
                'lastPage' => $lastPage,
                'statusCounts' => $statusCounts,
                'sectionCounts' => $sectionCounts,
                'stalls' => Stall::orderBy('section')->orderBy('stall_number')->get(),
                'tenantStallsRoute' => 'administrator.records',
                'tenantStallsRouteParams' => ['role' => 'treasurer', 'area' => 'stall-rental'],
                'tenantStallsReadOnly' => true,
                'tenantStallsTitle' => 'TREASURER',
                'tenantStallsBreadcrumb' => 'Dashboard | Treasurer',
                'tenantStallsTitleAvatar' => '2-Treasurer.png',
                'tenantStallsShowTopMap' => true,
                'tenantStallMapEditable' => true,
                'tenantStallSectionUpdateRoute' => 'administrator.stall-map.sections.update',
                'tenantApplicationShowStallButton' => false,
                'tenantRentalPayments' => $tenantRentalPayments,
                'tenantRentalHistoryRoute' => 'administrator.rentals.history.update',
            ]);
        }

        if ($role === 'tenant' && $area === 'stall-rental') {
            return redirect()->route('administrator.records', ['role' => 'tenant', 'area' => 'payment']);
        }

        if ($role === 'tenant' && $area === 'payment') {
            $query = Payment::query()
                ->with(['tenant', 'stallApplication.stall'])
                ->latest('due_date');

            if ($request->filled('section') && strtoupper($request->string('section')->toString()) !== 'ALL') {
                $section = strtoupper($request->string('section')->toString());
                $query->whereHas('stallApplication', fn ($builder) => $builder
                    ->where('preferred_section', $section)
                    ->orWhereHas('stall', fn ($stall) => $stall->where('section', $section)));
            }

            if ($request->filled('from')) {
                $query->whereDate('paid_at', '>=', $request->date('from'));
            }

            if ($request->filled('to')) {
                $query->whereDate('paid_at', '<=', $request->date('to'));
            }

            if ($request->filled('search')) {
                $search = $request->string('search')->toString();
                $query->where(function ($builder) use ($search) {
                    $builder->where('reference_number', 'like', "%{$search}%")
                        ->orWhereHas('tenant', fn ($tenant) => $tenant
                            ->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%"))
                        ->orWhereHas('stallApplication', fn ($application) => $application
                            ->where('business_owner', 'like', "%{$search}%")
                            ->orWhere('business_name', 'like', "%{$search}%"));
                });
            }

            $rentalStatus = fn (Payment $payment) => $payment->status === 'PAID'
                ? 'PAID'
                : (($payment->status === 'OVERDUE' || ($payment->due_date?->isPast() ?? false)) ? 'OVERDUE' : 'UNPAID');
            $filteredPayments = $query->get();
            $statusCounts = [
                'ALL' => $filteredPayments->count(),
                'OVERDUE' => $filteredPayments->filter(fn (Payment $payment) => $rentalStatus($payment) === 'OVERDUE')->count(),
                'PAID' => $filteredPayments->filter(fn (Payment $payment) => $rentalStatus($payment) === 'PAID')->count(),
                'UNPAID' => $filteredPayments->filter(fn (Payment $payment) => $rentalStatus($payment) === 'UNPAID')->count(),
            ];

            $payments = $filteredPayments->filter(function (Payment $payment) use ($request, $rentalStatus) {
                if (! $request->filled('status') || strtoupper($request->string('status')->toString()) === 'ALL') {
                    return true;
                }

                return $rentalStatus($payment) === strtoupper($request->string('status')->toString());
            })->values();

            $perPage = (int) $request->integer('per_page', 10);
            $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;
            $page = max(1, (int) $request->integer('page', 1));
            $visiblePayments = $payments->slice(($page - 1) * $perPage, $perPage)->values();
            $lastPage = max(1, (int) ceil($payments->count() / $perPage));

            return view('market.clerk.rentals', [
                'pageTitle' => 'Tenant Records',
                'payments' => $visiblePayments,
                'totalPayments' => $payments->count(),
                'perPage' => $perPage,
                'page' => $page,
                'lastPage' => $lastPage,
                'showTable' => true,
                'statusCounts' => $statusCounts,
                'rentalRoute' => 'administrator.records',
                'rentalRouteParams' => ['role' => 'tenant', 'area' => 'payment'],
                'rentalHistoryRoute' => 'administrator.rentals.history.update',
                'rentalPageTitle' => 'TENANT',
                'rentalBreadcrumb' => 'Dashboard | Tenant',
                'rentalTitleAvatar' => '5-Tenants.png',
                'rentalReadOnly' => true,
                'showShortCharges' => false,
            ]);
        }

        if ($role === 'tenant' && $area === 'inspection') {
            return redirect()->route('administrator.records', ['role' => 'tenant', 'area' => 'payment']);
        }

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

    public function updateStallSections(Request $request)
    {
        $sections = ['FISH', 'POULTRY', 'PORK', 'BEEF', 'MIXED'];
        $data = $request->validate([
            'sections' => ['required', 'array'],
            'sections.*' => ['required', 'integer', 'min:0', 'max:300'],
        ]);

        foreach ($sections as $section) {
            $targetTotal = (int) ($data['sections'][$section] ?? 0);
            $currentTotal = Stall::where('section', $section)->count();

            if ($targetTotal > $currentTotal) {
                $highestNumber = (int) Stall::where('section', $section)->max('stall_number');
                $monthlyRate = in_array($section, ['FISH', 'POULTRY'], true) ? 750 : 900;

                for ($number = $highestNumber + 1; $number <= $highestNumber + ($targetTotal - $currentTotal); $number++) {
                    Stall::create([
                        'section' => $section,
                        'stall_number' => $number,
                        'monthly_rate' => $monthlyRate,
                        'status' => 'AVAILABLE',
                    ]);
                }

                continue;
            }

            if ($targetTotal < $currentTotal) {
                $removeCount = $currentTotal - $targetTotal;
                $removableStalls = Stall::where('section', $section)
                    ->where('status', 'AVAILABLE')
                    ->orderByDesc('stall_number')
                    ->limit($removeCount)
                    ->get();

                if ($removableStalls->count() < $removeCount) {
                    return $request->expectsJson()
                        ? response()->json(['message' => "Cannot reduce {$section} section because occupied stalls would be removed."], 422)
                        : back()->withErrors(['sections' => "Cannot reduce {$section} section because occupied stalls would be removed."]);
                }

                Stall::whereKey($removableStalls->pluck('id'))->delete();
            }
        }

        if ($request->expectsJson()) {
            $updatedStalls = Stall::orderBy('section')->orderBy('stall_number')->get()->groupBy('section');
            $sectionData = collect($sections)->mapWithKeys(function (string $section) use ($updatedStalls) {
                $stalls = $updatedStalls->get($section, collect())->values();

                return [$section => [
                    'total' => $stalls->count(),
                    'available' => $stalls->where('status', 'AVAILABLE')->count(),
                    'occupied' => $stalls->where('status', 'OCCUPIED')->count(),
                    'stalls' => $stalls->map(fn (Stall $stall) => [
                        'id' => $stall->id,
                        'number' => $stall->stall_number,
                        'status' => $stall->status,
                    ])->values(),
                ]];
            });

            return response()->json([
                'message' => 'Stall section totals updated.',
                'sections' => $sectionData,
            ]);
        }

        return back()->with('success', 'Stall section totals updated.');
    }

    public function updateRentalHistory(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.status' => ['required', 'in:PAID,OVERDUE,UNPAID,PENDING'],
            'payments.*.shortage_amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        $editablePayments = Payment::query()
            ->where('tenant_id', $payment->tenant_id)
            ->where('stall_application_id', $payment->stall_application_id)
            ->whereIn('id', array_keys($data['payments']))
            ->get();

        foreach ($editablePayments as $editablePayment) {
            $row = $data['payments'][$editablePayment->id];
            $status = $row['status'] === 'UNPAID' ? 'PENDING' : $row['status'];

            $editablePayment->update([
                'status' => $status,
                'shortage_amount' => $row['shortage_amount'],
                'paid_at' => $status === 'PAID' ? ($editablePayment->paid_at ?? now()) : null,
                'recorded_by' => $request->user()->id,
            ]);
        }

        return back()->with('success', 'Tenant payment history updated.');
    }

    public function inspectionDataTable(Request $request)
    {
        $query = LivestockInspection::query()
            ->with(['tenant', 'inspector'])
            ->whereIn('status', ['APPROVED', 'COMPLETED']);

        if ($request->filled('status') && strtoupper($request->string('status')->toString()) !== 'ALL') {
            $query->where('status', strtoupper($request->string('status')->toString()));
        }
        if ($request->filled('type') && strtoupper($request->string('type')->toString()) !== 'ALL') {
            $query->where('livestock_type', strtoupper($request->string('type')->toString()));
        }
        if ($request->filled('dateFrom')) {
            $query->whereDate('scheduled_at', '>=', $request->date('dateFrom'));
        }
        if ($request->filled('dateTo')) {
            $query->whereDate('scheduled_at', '<=', $request->date('dateTo'));
        }

        return ServerDataTable::make(
            $request,
            $query,
            ['request_number', 'owner_name', 'address', 'contact_number', 'livestock_type', 'breed', 'inspection_result', 'status'],
            ['scheduled_at', 'owner_name', 'address', 'contact_number', 'livestock_type', 'scheduled_at', 'status'],
            function (LivestockInspection $inspection) {
                $record = e(json_encode($this->inspectionPayload($inspection)));
                $action = '<button type="button" class="inspector-table-action view js-inspector-view" data-record="'.$record.'" title="View"><i class="bi bi-eye-fill"></i></button>';

                return [
                    'reference' => e($inspection->request_number),
                    'owner' => e($inspection->owner_name),
                    'address' => e($inspection->address),
                    'contact' => e($inspection->contact_number),
                    'type' => e($inspection->livestock_type),
                    'breed' => e($inspection->breed ?: ($inspection->livestock_type === 'POULTRY' ? 'Chicken' : $inspection->animal_age)),
                    'age' => e($inspection->animal_age ?: '-'),
                    'weight' => $inspection->live_weight !== null ? number_format((float) $inspection->live_weight, 2).' kg' : '-',
                    'count' => $inspection->animal_count,
                    'schedule' => $inspection->scheduled_at->format('F d, Y | g:i A'),
                    'result' => e($this->inspectionResultLabel($inspection->inspection_result)),
                    'status' => '<span class="inspector-status inspector-status-'.strtolower($inspection->status).'">'.e(str($inspection->status)->title()).'</span>',
                    'action' => $action,
                ];
            }
        );
    }

    public function updateInspection(Request $request, LivestockInspection $inspection)
    {
        $data = $request->validate([
            'status' => ['required', 'in:APPROVED,DISAPPROVED,COMPLETED'],
            'scheduled_at' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $inspection->update([
            'status' => $data['status'],
            'scheduled_at' => $data['scheduled_at'] ?? $inspection->scheduled_at,
            'remarks' => $data['remarks'] ?? $inspection->remarks,
            'inspector_id' => $inspection->inspector_id,
            'inspected_at' => $data['status'] === 'COMPLETED' ? now() : $inspection->inspected_at,
        ]);

        if ($inspection->tenant_id) {
            MarketNotification::create([
                'user_id' => $inspection->tenant_id,
                'type' => 'INSPECTION',
                'title' => "Inspection {$data['status']}",
                'message' => "{$inspection->request_number} is now {$data['status']}.",
                'action_url' => route('tenant.inspections'),
            ]);
        }

        return $request->expectsJson()
            ? response()->json(['status' => 'success', 'message' => 'Inspection request updated.'])
            : back()->with('success', 'Inspection request updated.');
    }

    private function inspectionPayload(LivestockInspection $inspection): array
    {
        return [
            'id' => $inspection->id,
            'request_number' => $inspection->request_number,
            'livestock_type' => $inspection->livestock_type,
            'owner_name' => $inspection->owner_name,
            'address' => $inspection->address,
            'contact_number' => $inspection->contact_number,
            'email' => $inspection->email,
            'scheduled_date' => $inspection->scheduled_at->format('Y-m-d'),
            'scheduled_time' => $inspection->scheduled_at->format('H:i'),
            'animal_count' => $inspection->animal_count,
            'status' => $inspection->status,
            'inspection_result' => $inspection->inspection_result,
            'remarks' => $inspection->remarks,
            'breed' => $inspection->breed,
            'animal_age' => $inspection->animal_age,
            'live_weight' => $inspection->live_weight,
            'source_location' => $inspection->source_location,
            'purpose' => $inspection->purpose,
            'ante_mortem_findings' => $inspection->ante_mortem_findings,
            'post_mortem_findings' => $inspection->post_mortem_findings,
            'update_url' => route('administrator.inspections.update', $inspection),
        ];
    }

    private function inspectionResultLabel(?string $result): string
    {
        return match ($result) {
            'PASSED' => 'Passed with Human Consumption',
            'CONDEMNED' => 'Condemned',
            'REINSPECTION' => 'For Further Examination',
            default => 'Pending Inspection',
        };
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
