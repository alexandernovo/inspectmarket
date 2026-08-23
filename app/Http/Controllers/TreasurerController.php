<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\CashTicketAssignment;
use App\Models\CashTicketCollection;
use App\Models\MarketNotification;
use App\Models\Payment;
use App\Models\Stall;
use App\Models\StallApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TreasurerController extends Controller
{
    public function dashboard()
    {
        $year = now()->year;
        $cashTicketMonthly = CashTicketCollection::query()
            ->whereYear('collection_date', $year)
            ->get(['collection_date', 'amount'])
            ->groupBy(fn ($collection) => $collection->collection_date->month)
            ->map(fn ($rows) => $rows->sum('amount'));
        $stallRentalMonthly = Payment::query()
            ->where('status', 'PAID')
            ->whereYear('paid_at', $year)
            ->get(['paid_at', 'amount'])
            ->groupBy(fn ($payment) => $payment->paid_at->month)
            ->map(fn ($rows) => $rows->sum('amount'));
        $chartTotals = collect(range(1, 12))->map(fn ($month) => (float) ($cashTicketMonthly[$month] ?? 0) + (float) ($stallRentalMonthly[$month] ?? 0));
        $maxTotal = max(1, $chartTotals->max());
        $paidTenantPayments = Payment::with(['tenant', 'stallApplication.stall'])
            ->where('status', 'PAID')
            ->latest('paid_at')
            ->latest('due_date')
            ->get();
        $unpaidTenantPayments = Payment::with(['tenant', 'stallApplication.stall'])
            ->whereIn('status', ['PENDING', 'OVERDUE', 'DISAPPROVED'])
            ->latest('due_date')
            ->get();

        return view('market.portal.dashboard', [
            'pageTitle' => 'Treasurer Dashboard',
            'stats' => [
                ['label' => 'Cash Tickets', 'sublabel' => 'Total Collection', 'value' => 'P'.number_format(CashTicketCollection::sum('amount'), 2), 'tone' => 'blue', 'image' => 'CLERK/IMAGES/Cash Ticket.jpg'],
                ['label' => 'Stall Rental', 'sublabel' => 'Total Payment', 'value' => 'P'.number_format(Payment::where('status', 'PAID')->sum('amount'), 2), 'tone' => 'gold', 'image' => 'TREASURER/IMAGES/Requisition and Issue Slip.png'],
                ['label' => 'Unpaid Tenants', 'sublabel' => 'Stall Rental', 'value' => $unpaidTenantPayments->unique('tenant_id')->count(), 'tone' => 'red', 'image' => 'TREASURER/IMAGES/Unpaid Tenant.png'],
                ['label' => 'Paid Tenants', 'sublabel' => 'Stall Rental', 'value' => $paidTenantPayments->unique('tenant_id')->count(), 'tone' => 'green', 'image' => 'TREASURER/IMAGES/Paid Tenant.png'],
            ],
            'rows' => StallApplication::with(['tenant', 'stall'])->latest()->limit(8)->get(),
            'paidTenantPayments' => $paidTenantPayments,
            'unpaidTenantPayments' => $unpaidTenantPayments,
            'rowType' => 'applications',
            'chartTitle' => 'Revenue and stall activity',
            'chartTotals' => $chartTotals,
            'chartValues' => $chartTotals->map(fn ($value) => max(4, round(($value / $maxTotal) * 100))),
            'chartYears' => collect([$year])
                ->merge(CashTicketCollection::query()->get(['collection_date'])->map(fn ($row) => $row->collection_date->year))
                ->merge(Payment::query()->whereNotNull('paid_at')->get(['paid_at'])->map(fn ($row) => $row->paid_at->year))
                ->unique()
                ->sortDesc()
                ->values(),
        ]);

        return view('market.portal.dashboard', [
            'pageTitle' => 'Treasurer Dashboard',
            'stats' => [
                ['label' => 'Cash Tickets', 'value' => '₱'.number_format(CashTicketCollection::sum('amount'), 2), 'icon' => 'bi-ticket-perforated'],
                ['label' => 'Stall Rental', 'value' => '₱'.number_format(Payment::where('status', 'PAID')->sum('amount'), 2), 'icon' => 'bi-shop'],
                ['label' => 'Unpaid Rentals', 'sublabel' => 'Pending Payment', 'value' => Payment::where('status', 'PENDING')->count(), 'tone' => 'red', 'icon' => 'bi-wallet2'],
                ['label' => 'Active Stalls', 'sublabel' => 'Approved Tenants', 'value' => StallApplication::where('status', 'APPROVED')->count(), 'tone' => 'gold', 'icon' => 'bi-grid-3x3-gap-fill'],
            ],
            'rows' => StallApplication::with(['tenant', 'stall'])->latest()->limit(8)->get(),
            'rowType' => 'applications',
            'chartTitle' => 'Revenue and stall activity',
        ]);
    }

    public function announcements()
    {
        return view('market.portal.announcements', [
            'pageTitle' => 'Announcements',
            'announcements' => Announcement::with('author')->latest()->get(),
        ]);
    }

    public function storeAnnouncement(Request $request)
    {
        $data = $request->validate([
            'category' => ['required', 'in:SLAUGHTERED INSPECT,STALL RENTAL,BIDDING,MARKET ADVISORY,OTHERS'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
            'published_at' => ['nullable', 'date'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $attachment = $data['attachment'] ?? null;
        unset($data['attachment']);

        $announcement = Announcement::create([
            ...$data,
            'author_id' => $request->user()->id,
            'published_at' => $data['published_at'] ?? now(),
            'is_published' => true,
        ]);

        if ($attachment) {
            $announcement->update([
                'attachment_path' => $attachment->store('announcements', 'public'),
                'attachment_name' => $attachment->getClientOriginalName(),
            ]);
        }

        User::where('status', 'ACTIVE')
            ->whereKeyNot($request->user()->id)
            ->each(function (User $user) use ($announcement) {
                MarketNotification::create([
                    'user_id' => $user->id,
                    'type' => 'ANNOUNCEMENT',
                    'title' => $announcement->title,
                    'message' => Str::limit($announcement->content, 180),
                    'action_url' => route('home').'#announcements',
                ]);
            });

        return back()->with('success', 'Announcement published.');
    }

    public function updateAnnouncement(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'category' => ['required', 'in:SLAUGHTERED INSPECT,STALL RENTAL,BIDDING,MARKET ADVISORY,OTHERS'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
            'published_at' => ['nullable', 'date'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $attachment = $data['attachment'] ?? null;
        unset($data['attachment']);

        $announcement->update([
            ...$data,
            'published_at' => $data['published_at'] ?? $announcement->published_at ?? now(),
        ]);

        if ($attachment) {
            if ($announcement->attachment_path) {
                Storage::disk('public')->delete($announcement->attachment_path);
            }
            $announcement->update([
                'attachment_path' => $attachment->store('announcements', 'public'),
                'attachment_name' => $attachment->getClientOriginalName(),
            ]);
        }

        return back()->with('success', 'Announcement updated.');
    }

    public function rentals(Request $request)
    {
        $query = Payment::query()
            ->with(['tenant', 'stallApplication.stall'])
            ->latest('due_date');

        if ($request->filled('section') && strtoupper($request->string('section')->toString()) !== 'ALL') {
            $query->whereHas('stallApplication', fn ($builder) => $builder
                ->where('preferred_section', strtoupper($request->string('section')->toString()))
                ->orWhereHas('stall', fn ($stall) => $stall->where('section', strtoupper($request->string('section')->toString()))));
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

        $filteredPayments = $query->get();
        $statusCounts = [
            'ALL' => $filteredPayments->count(),
            'OVERDUE' => $filteredPayments->filter(fn (Payment $payment) => $this->rentalStatus($payment) === 'OVERDUE')->count(),
            'PAID' => $filteredPayments->filter(fn (Payment $payment) => $this->rentalStatus($payment) === 'PAID')->count(),
            'UNPAID' => $filteredPayments->filter(fn (Payment $payment) => $this->rentalStatus($payment) === 'UNPAID')->count(),
        ];

        $payments = $filteredPayments->filter(function (Payment $payment) use ($request) {
            if (! $request->filled('status') || strtoupper($request->string('status')->toString()) === 'ALL') {
                return true;
            }

            return $this->rentalStatus($payment) === strtoupper($request->string('status')->toString());
        })->values();

        $perPage = (int) $request->integer('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;
        $page = max(1, (int) $request->integer('page', 1));
        $visiblePayments = $payments->slice(($page - 1) * $perPage, $perPage)->values();
        $lastPage = max(1, (int) ceil($payments->count() / $perPage));

        return view('market.clerk.rentals', [
            'pageTitle' => 'Stall Rental',
            'payments' => $visiblePayments,
            'totalPayments' => $payments->count(),
            'perPage' => $perPage,
            'page' => $page,
            'lastPage' => $lastPage,
            'showTable' => $request->boolean('view'),
            'rentalRoute' => 'treasurer.rentals',
            'rentalHistoryRoute' => 'treasurer.rentals.history.update',
            'statusCounts' => $statusCounts,
        ]);
    }

    private function rentalStatus(Payment $payment): string
    {
        if ($payment->status === 'PAID') {
            return 'PAID';
        }

        if ($payment->status === 'OVERDUE' || ($payment->due_date?->isPast() ?? false)) {
            return 'OVERDUE';
        }

        return 'UNPAID';
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
                    return back()->withErrors([
                        'sections' => "Cannot reduce {$section} section because occupied stalls would be removed.",
                    ]);
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

    public function reviewApplication(Request $request, StallApplication $application)
    {
        $data = $request->validate([
            'status' => ['required', 'in:APPROVED,DISAPPROVED'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'stall_id' => ['nullable', 'exists:stalls,id'],
        ]);

        if ($data['status'] === 'APPROVED' && empty($data['stall_id'])) {
            return back()->withErrors(['stall_id' => 'Select an available stall before approving.']);
        }

        $previousStall = $application->stall;
        $application->update([
            ...$data,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        if ($previousStall && $previousStall->id !== (int) ($data['stall_id'] ?? 0)) {
            $previousStall->update(['status' => 'AVAILABLE']);
        }
        if ($data['status'] === 'APPROVED' && $application->stall) {
            $application->stall->update(['status' => 'OCCUPIED']);
        }

        MarketNotification::create([
            'user_id' => $application->tenant_id,
            'type' => 'STALL_APPLICATION',
            'title' => "Application {$data['status']}",
            'message' => "{$application->application_number} is now {$data['status']}.",
            'action_url' => route('tenant.applications'),
        ]);

        return back()->with('success', 'Stall application reviewed.');
    }

    public function payments(Request $request)
    {
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
        $sectionCounts = [
            'MIXED' => $filteredApplications->filter(fn (StallApplication $application) => strtoupper($application->stall?->section ?? $application->preferred_section ?? '') === 'MIXED')->count(),
            'BEEF' => $filteredApplications->filter(fn (StallApplication $application) => strtoupper($application->stall?->section ?? $application->preferred_section ?? '') === 'BEEF')->count(),
            'PORK' => $filteredApplications->filter(fn (StallApplication $application) => strtoupper($application->stall?->section ?? $application->preferred_section ?? '') === 'PORK')->count(),
            'POULTRY' => $filteredApplications->filter(fn (StallApplication $application) => strtoupper($application->stall?->section ?? $application->preferred_section ?? '') === 'POULTRY')->count(),
            'FISH' => $filteredApplications->filter(fn (StallApplication $application) => strtoupper($application->stall?->section ?? $application->preferred_section ?? '') === 'FISH')->count(),
        ];
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
        $applications = $filteredApplications;
        $visibleApplications = $applications->slice(($page - 1) * $perPage, $perPage)->values();
        $lastPage = max(1, (int) ceil($applications->count() / $perPage));

        return view('market.treasurer.tenant-stalls', [
            'pageTitle' => 'Stall Rental',
            'applications' => $visibleApplications,
            'totalApplications' => $applications->count(),
            'perPage' => $perPage,
            'page' => $page,
            'lastPage' => $lastPage,
            'statusCounts' => $statusCounts,
            'sectionCounts' => $sectionCounts,
            'stalls' => Stall::orderBy('section')->orderBy('stall_number')->get(),
        ]);
    }

    public function updateTenantStall(Request $request, StallApplication $application)
    {
        $data = $request->validate([
            'stall_id' => ['nullable', 'exists:stalls,id'],
            'status' => ['required', Rule::in(['PENDING', 'APPROVED', 'DISAPPROVED'])],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $previousStall = $application->stall;
        $application->update([
            'stall_id' => $data['stall_id'] ?? null,
            'status' => $data['status'],
            'remarks' => $data['remarks'] ?? $application->remarks,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        if ($previousStall && $previousStall->id !== (int) ($data['stall_id'] ?? 0)) {
            $previousStall->update(['status' => 'AVAILABLE']);
        }

        return back()->with('success', 'Tenant stall rental record updated.');
    }

    public function destroyTenantStall(StallApplication $application)
    {
        if ($application->stall) {
            $application->stall->update(['status' => 'AVAILABLE']);
        }

        $application->delete();

        return back()->with('success', 'Tenant stall rental record deleted.');
    }

    public function recordPayment(Request $request)
    {
        $data = $request->validate([
            'tenant_id' => ['required', 'exists:users,id'],
            'stall_application_id' => ['nullable', 'exists:stall_applications,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'period_month' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:CASH,GCASH,BANK'],
            'status' => ['required', 'in:PENDING,PAID,OVERDUE'],
            'or_number' => ['nullable', 'string', 'max:100'],
            'shortage_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $payment = Payment::create([
            ...$data,
            'reference_number' => 'PAY-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'period_month' => Carbon::parse($data['period_month'])->startOfMonth(),
            'recorded_by' => $request->user()->id,
            'paid_at' => $data['status'] === 'PAID' ? now() : null,
        ]);

        MarketNotification::create([
            'user_id' => $payment->tenant_id,
            'type' => 'PAYMENT',
            'title' => "Payment {$payment->status}",
            'message' => "{$payment->reference_number} was recorded for ₱".number_format($payment->amount, 2).'.',
            'action_url' => route('tenant.payments'),
        ]);

        return back()->with('success', 'Payment record saved.');
    }

    public function verifyPayment(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'status' => ['required', 'in:PAID,DISAPPROVED'],
            'or_number' => ['nullable', 'string', 'max:100'],
        ]);

        $payment->update([
            ...$data,
            'paid_at' => $data['status'] === 'PAID' ? now() : null,
            'recorded_by' => $request->user()->id,
        ]);

        MarketNotification::create([
            'user_id' => $payment->tenant_id,
            'type' => 'PAYMENT',
            'title' => "Payment {$data['status']}",
            'message' => "{$payment->reference_number} is now {$data['status']}.",
            'action_url' => route('tenant.payments'),
        ]);

        return back()->with('success', 'Payment verification updated.');
    }

    public function assignments()
    {
        return view('market.portal.assignments', [
            'pageTitle' => 'Cash Ticket Assignments',
            'assignments' => CashTicketAssignment::with('collector')->latest('assigned_date')->get(),
            'collections' => CashTicketCollection::with(['collector', 'assignment'])->latest('collection_date')->get(),
            'collectors' => User::where('usertype', User::ROLE_CLERK)->where('status', 'ACTIVE')->orderBy('firstname')->get(),
            'collectorRecords' => User::where('usertype', User::ROLE_CLERK)->latest()->get(),
        ]);
    }

    public function collectors()
    {
        return view('market.portal.collectors', [
            'pageTitle' => 'Cash Ticket Collectors',
            'collectors' => User::where('usertype', User::ROLE_CLERK)->orderBy('lastname')->orderBy('firstname')->get(),
        ]);
    }

    public function storeCollector(Request $request)
    {
        if ($request->filled('full_name')) {
            $data = $request->validate([
                'full_name' => ['required', 'string', 'max:200'],
                'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
                'phone_num' => ['required', 'string', 'max:30', 'unique:users,phone_num'],
                'address' => ['required', 'string', 'max:255'],
                'designation' => ['nullable', 'string', 'max:150'],
                'status' => ['required', 'in:ACTIVE,INACTIVE'],
                'profile_image' => ['nullable', 'image', 'max:4096'],
            ]);

            $nameParts = preg_split('/\s+/', trim($data['full_name']));
            $firstName = array_shift($nameParts) ?: 'Collector';
            $lastName = array_pop($nameParts) ?: 'Clerk';
            $middleName = trim(implode(' ', $nameParts)) ?: null;
            $usernameBase = Str::slug($firstName.'.'.$lastName) ?: 'collector';
            $username = $usernameBase;
            $suffix = 1;

            while (User::where('username', $username)->exists()) {
                $username = $usernameBase.$suffix++;
            }

            $profilePath = $request->file('profile_image')?->store('profiles/collectors', 'public');

            User::create([
                'firstname' => $firstName,
                'middlename' => $middleName,
                'lastname' => $lastName,
                'username' => $username,
                'email' => $data['email'] ?? null,
                'phone_num' => $data['phone_num'],
                'address' => $data['address'],
                'designation' => $data['designation'] ?? 'Revenue Collector Clerk',
                'password' => Str::random(14),
                'profile' => $profilePath,
                'usertype' => User::ROLE_CLERK,
                'status' => $data['status'],
                'phone_verified_at' => now(),
            ]);

            return back()->with('success', 'Collector account created.');
        }

        $data = $request->validate([
            'firstname' => ['required', 'string', 'max:100'],
            'middlename' => ['nullable', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone_num' => ['required', 'string', 'max:30', 'unique:users,phone_num'],
            'address' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            ...$data,
            'usertype' => User::ROLE_CLERK,
            'status' => 'ACTIVE',
            'phone_verified_at' => now(),
        ]);

        return back()->with('success', 'Collector account created.');
    }

    public function updateCollector(Request $request, User $collector)
    {
        abort_unless($collector->isRole(User::ROLE_CLERK), 404);
        $data = $request->validate([
            'firstname' => ['nullable', 'string', 'max:100'],
            'middlename' => ['nullable', 'string', 'max:100'],
            'lastname' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($collector->id)],
            'phone_num' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone_num')->ignore($collector->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
            'designation' => ['required', 'string', 'max:150'],
            'profile_image' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('profile_image')) {
            if ($collector->profile) {
                Storage::disk('public')->delete($collector->profile);
            }

            $data['profile'] = $request->file('profile_image')->store('profiles/collectors', 'public');
        }

        unset($data['profile_image']);

        $collector->update($data);

        return back()->with('success', 'Collector record updated.');
    }

    public function destroyCollector(User $collector)
    {
        abort_unless($collector->isRole(User::ROLE_CLERK), 404);

        $collector->update(['status' => 'INACTIVE']);

        return back()->with('success', 'Collector account deactivated.');
    }

    public function storeAssignment(Request $request)
    {
        $data = $request->validate([
            'collector_id' => ['required', 'exists:users,id'],
            'stall_section' => ['required', 'in:FISH,PORK,POULTRY,BEEF,MIXED'],
            'ticket_start' => ['required', 'integer', 'min:1'],
            'ticket_end' => ['required', 'integer', 'gte:ticket_start'],
            'assigned_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $assignment = CashTicketAssignment::create([
            ...$data,
            'assignment_number' => 'CTA-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'assigned_by' => $request->user()->id,
            'ticket_quantity' => ($data['ticket_end'] - $data['ticket_start']) + 1,
            'status' => 'ASSIGNED',
        ]);

        MarketNotification::create([
            'user_id' => $assignment->collector_id,
            'type' => 'CASH_TICKET',
            'title' => 'New cash ticket assignment',
            'message' => "{$assignment->stall_section}: tickets {$assignment->ticket_start}–{$assignment->ticket_end}.",
            'action_url' => route('clerk.collections'),
        ]);

        return back()->with('success', 'Cash ticket assignment saved.');
    }
}
