<?php

namespace App\Http\Controllers;

use App\Models\CashTicketAssignment;
use App\Models\CashTicketCollection;
use App\Models\Payment;
use App\Models\StallApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClerkController extends Controller
{
    public function dashboard()
    {
        $paymentRows = Payment::with(['tenant', 'stallApplication.stall'])->latest('due_date')->limit(8)->get();
        $cashTicketMonthly = collect(range(1, 12))->map(
            fn (int $month) => (float) CashTicketCollection::whereYear('collection_date', now()->year)
                ->whereMonth('collection_date', $month)
                ->sum('amount')
        );
        $stallRentalMonthly = collect(range(1, 12))->map(
            fn (int $month) => (float) Payment::whereYear('period_month', now()->year)
                ->whereMonth('period_month', $month)
                ->where('status', 'PAID')
                ->sum('amount')
        );
        $chartTotals = collect(range(1, 12))->map(fn (int $index) => $cashTicketMonthly[$index - 1] + $stallRentalMonthly[$index - 1]);
        $largestMonthlyTotal = max(1, $chartTotals->max());

        return view('market.portal.dashboard', [
            'pageTitle' => 'Clerk Dashboard',
            'stats' => [
                ['label' => 'Total Collected Fee', 'sublabel' => 'Cash Tickets', 'value' => number_format(CashTicketCollection::sum('amount'), 0), 'image' => 'HOMEPAGE/Market.png', 'tone' => 'blue'],
                ['label' => 'Total Collected Fee', 'sublabel' => 'Stall Rental', 'value' => number_format(Payment::where('status', 'PAID')->sum('amount'), 0), 'image' => 'HOMEPAGE/Stall.png', 'tone' => 'gold'],
                ['label' => 'Unpaid Tenants', 'sublabel' => 'Stall Rental', 'value' => Payment::whereIn('status', ['PENDING', 'OVERDUE', 'DISAPPROVED'])->distinct('tenant_id')->count('tenant_id'), 'image' => 'USERS/E-Male Tenant.png', 'tone' => 'red'],
                ['label' => 'Paid Tenants', 'sublabel' => 'Stall Rental', 'value' => Payment::where('status', 'PAID')->distinct('tenant_id')->count('tenant_id'), 'image' => 'USERS/F-Female Tenant.png', 'tone' => 'green'],
            ],
            'rows' => $paymentRows,
            'rowType' => 'payments',
            'chartTitle' => 'CASH TICKET AND STALL RENTAL DATA CHART',
            'chartTotals' => $chartTotals,
            'chartValues' => $chartTotals->map(fn ($total) => max(6, (int) round(((float) $total / $largestMonthlyTotal) * 100))),
            'chartSeries' => [
                'ALL' => $chartTotals,
                'CASH_TICKET' => $cashTicketMonthly,
                'STALL_RENTAL' => $stallRentalMonthly,
            ],
            'chartYears' => collect([now()->year])
                ->merge(CashTicketCollection::pluck('collection_date')->map(fn ($date) => optional($date)->year))
                ->merge(Payment::pluck('period_month')->map(fn ($date) => optional($date)->year))
                ->filter()
                ->unique()
                ->sortDesc()
                ->values(),
        ]);
    }

    public function collections()
    {
        return view('market.portal.collections', [
            'pageTitle' => 'Cash Ticket Collections',
            'collections' => CashTicketCollection::with('collector')->latest('collection_date')->get(),
            'assignments' => CashTicketAssignment::with('collector')->latest('assigned_date')->get(),
            'collectors' => User::where('usertype', User::ROLE_CLERK)->where('status', 'ACTIVE')->orderBy('firstname')->get(),
            'canCreate' => true,
        ]);
    }

    public function storeAssignment(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'assignments' => ['required', 'array', 'min:1'],
            'assignments.*.collector_id' => ['nullable'],
            'assignments.*.stall_section' => ['nullable', 'in:FISH,PORK,POULTRY,BEEF,MIXED'],
            'assignments.*.ticket_start' => ['nullable', 'integer', 'min:1'],
            'assignments.*.ticket_end' => ['nullable', 'integer', 'min:1'],
            'assignments.*.assigned_date' => ['nullable', 'date'],
            'assignments.*.remarks' => ['nullable', 'string', 'max:1000'],
            'cash_slip' => ['nullable', 'array'],
            'cash_slip.*.unit' => ['nullable', 'string', 'max:255'],
            'cash_slip.*.description' => ['nullable', 'string', 'max:255'],
            'cash_slip.*.stub' => ['nullable', 'integer', 'min:1'],
            'cash_slip.*.pcs' => ['nullable', 'integer', 'min:1'],
            'workflow_status' => ['required', 'in:REQUESTED,ASSIGNED'],
        ]);
        $openDialog = $request->input('open_dialog', $request->input('workflow_status') === 'ASSIGNED' ? 'cashTicketStep2' : 'cashTicketStep1');

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_dialog', $openDialog);
        }

        $data = $validator->validated();
        $collectorIds = collect($data['assignments'])
            ->flatMap(fn (array $assignment) => \Illuminate\Support\Arr::wrap($assignment['collector_id'] ?? []))
            ->filter()
            ->unique()
            ->values();

        if ($collectorIds->isNotEmpty() && User::whereKey($collectorIds)->count() !== $collectorIds->count()) {
            return back()->withErrors(['collector_id' => 'Please select valid collector/s.'])->withInput()->with('open_dialog', $openDialog);
        }

        $assignments = collect($data['assignments'])->filter(fn (array $assignment) => filled($assignment['collector_id'] ?? null)
            && filled($assignment['stall_section'] ?? null)
            && filled($assignment['ticket_start'] ?? null)
            && filled($assignment['ticket_end'] ?? null)
            && filled($assignment['assigned_date'] ?? null));

        if ($assignments->isEmpty()) {
            return back()->withErrors(['assignments' => 'Please complete at least one cash ticket row.'])->withInput()->with('open_dialog', $openDialog);
        }

        if ($data['workflow_status'] === 'REQUESTED') {
            $requestMonth = \Illuminate\Support\Carbon::parse($assignments->first()['assigned_date'])->startOfMonth();

            CashTicketAssignment::where('assigned_by', $request->user()->id)
                ->where('status', 'REQUESTED')
                ->whereBetween('assigned_date', [$requestMonth, $requestMonth->copy()->endOfMonth()])
                ->delete();
        }

        foreach ($assignments as $index => $assignment) {
            if ((int) $assignment['ticket_end'] < (int) $assignment['ticket_start']) {
                return back()->withErrors(['ticket_end' => 'Ending ticket must be greater than or equal to the starting ticket.'])->withInput()->with('open_dialog', $openDialog);
            }

            $slip = $data['cash_slip'][$index] ?? [];
            $slipRemarks = collect([
                filled($slip['unit'] ?? null) ? 'Unit: '.$slip['unit'] : null,
                filled($slip['description'] ?? null) ? 'Description: '.$slip['description'] : null,
                filled($slip['stub'] ?? null) ? 'Stub: '.$slip['stub'] : null,
                filled($slip['pcs'] ?? null) ? 'Pcs: '.$slip['pcs'] : null,
            ])->filter()->implode(' | ');

            foreach (\Illuminate\Support\Arr::wrap($assignment['collector_id']) as $collectorId) {
                $remarks = collect([
                    'RCC II: '.$request->user()->full_name,
                    $slipRemarks,
                    filled($assignment['remarks'] ?? null) ? 'Remarks: '.$assignment['remarks'] : null,
                ])->filter()->implode(' | ');

                CashTicketAssignment::create([
                    ...$assignment,
                    'collector_id' => $collectorId,
                    'assignment_number' => 'CTA-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                    'assigned_by' => $request->user()->id,
                    'ticket_quantity' => ($assignment['ticket_end'] - $assignment['ticket_start']) + 1,
                    'status' => $data['workflow_status'],
                    'remarks' => $remarks,
                ]);
            }
        }

        return back()->with('success', $data['workflow_status'] === 'REQUESTED'
            ? 'Cash ticket requisition and issue slip saved.'
            : 'Cash ticket collectors assigned.');
    }

    public function storeProgress(Request $request)
    {
        $data = $request->validate([
            'step' => ['required', 'in:REPORT,FINAL'],
            'month' => ['required', 'date'],
        ]);

        $month = \Illuminate\Support\Carbon::parse($data['month'])->startOfMonth();
        $status = $data['step'] === 'FINAL' ? 'SUBMITTED' : 'REPORTED';

        CashTicketCollection::whereBetween('collection_date', [$month, $month->copy()->endOfMonth()])
            ->whereIn('status', ['RECORDED', 'SUBMITTED', 'REPORTED'])
            ->update(['status' => $status]);

        return back()->with('success', $data['step'] === 'FINAL'
            ? 'Final cash ticket report submitted to treasurer.'
            : 'Cash ticket collection report saved.');
    }

    public function destroyWorkflow(Request $request)
    {
        $data = $request->validate([
            'month' => ['required', 'date'],
            'step' => ['required', 'integer', 'between:1,5'],
        ]);

        $month = \Illuminate\Support\Carbon::parse($data['month'])->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();

        DB::transaction(function () use ($data, $month, $monthEnd) {
            $collections = CashTicketCollection::query()
                ->whereBetween('collection_date', [$month, $monthEnd])
                ->where(fn ($query) => $query->whereNull('collection_number')->orWhere('collection_number', 'not like', 'CT-DEMO-%'));

            $assignments = CashTicketAssignment::query()
                ->whereBetween('assigned_date', [$month, $monthEnd])
                ->where(fn ($query) => $query->whereNull('assignment_number')->orWhere('assignment_number', 'not like', 'CTA-DEMO-%'));

            switch ((int) $data['step']) {
                case 1:
                    $collections->delete();
                    $assignments->delete();
                    break;
                case 2:
                    $collections->delete();
                    $assignments->whereIn('status', ['ASSIGNED', 'COMPLETED'])->delete();
                    break;
                case 3:
                    $collections->delete();
                    $assignments->where('status', 'COMPLETED')->update(['status' => 'ASSIGNED']);
                    break;
                case 4:
                    $collections->whereIn('status', ['REPORTED', 'SUBMITTED'])->update(['status' => 'RECORDED']);
                    break;
                case 5:
                    $collections->where('status', 'SUBMITTED')->update(['status' => 'REPORTED']);
                    break;
            }
        });

        return redirect()->route('clerk.collections', [
            'collection_month' => $month->month,
            'collection_year' => $month->year,
        ])->with('success', 'Cash ticket step '.$data['step'].' request deleted.');
    }

    public function storeCollection(Request $request)
    {
        if ($request->has('collections')) {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'collection_date' => ['required', 'date'],
                'collections' => ['required', 'array', 'min:1'],
                'update_progress' => ['nullable', 'boolean'],
                'collections.*.collector_id' => ['nullable', 'exists:users,id'],
                'collections.*.stall_section' => ['nullable', 'in:FISH,PORK,POULTRY,BEEF,MIXED'],
                'collections.*.ticket_quantity' => ['nullable', 'integer', 'min:1'],
                'collections.*.amount' => ['nullable', 'numeric', 'min:0'],
                'collections.*.cash_ticket_assignment_id' => ['nullable', 'exists:cash_ticket_assignments,id'],
                'collections.*.ticket_start' => ['nullable', 'integer', 'min:1'],
                'collections.*.ticket_end' => ['nullable', 'integer', 'min:1'],
                'collections.*.remarks' => ['nullable', 'string', 'max:1000'],
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with('open_dialog', $request->input('open_dialog', 'cashTicketStep3'));
            }

            $data = $validator->validated();
            $shouldUpdateProgress = (bool) ($data['update_progress'] ?? false);
            $rows = collect($data['collections'])->filter(fn (array $row) => filled($row['collector_id'] ?? null)
                && filled($row['stall_section'] ?? null)
                && filled($row['ticket_quantity'] ?? null));

            if ($rows->isEmpty()) {
                return back()->withErrors(['collections' => 'Please complete at least one collection row.'])->withInput()->with('open_dialog', $request->input('open_dialog', 'cashTicketStep3'));
            }

            foreach ($rows as $row) {
                $ticketQuantity = (int) $row['ticket_quantity'];
                $collection = CashTicketCollection::query()
                    ->whereDate('collection_date', $data['collection_date'])
                    ->where('stall_section', $row['stall_section'])
                    ->where(fn ($query) => $query->whereNull('collection_number')->orWhere('collection_number', 'not like', 'CT-DEMO-%'))
                    ->latest('id')
                    ->first() ?? new CashTicketCollection();

                if (! $collection->exists) {
                    $collection->collection_number = 'CT-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
                    $collection->recorded_by = $request->user()->id;
                }

                $collection->fill([
                    ...$row,
                    'recorded_by' => $collection->recorded_by ?: $request->user()->id,
                    'amount' => $row['amount'] ?? ($ticketQuantity * 10),
                    'collection_date' => $data['collection_date'],
                    'shortage_amount' => 0,
                    'status' => 'RECORDED',
                ])->save();

                if ($shouldUpdateProgress && $collection->assignment) {
                    $collection->assignment->update(['status' => 'COMPLETED']);
                }

                CashTicketCollection::query()
                    ->whereDate('collection_date', $data['collection_date'])
                    ->where('stall_section', $row['stall_section'])
                    ->where('id', '!=', $collection->id)
                    ->where(fn ($query) => $query->whereNull('collection_number')->orWhere('collection_number', 'not like', 'CT-DEMO-%'))
                    ->delete();
            }

            if ($request->expectsJson()) {
                $savedRows = CashTicketCollection::query()
                    ->whereDate('collection_date', $data['collection_date'])
                    ->where(fn ($query) => $query->whereNull('collection_number')->orWhere('collection_number', 'not like', 'CT-DEMO-%'))
                    ->get();
                $savedRowsBySection = $savedRows->sortByDesc('id')->groupBy('stall_section')->map(fn ($sectionRows) => [
                    'amount' => (float) $sectionRows->first()->amount,
                    'tickets' => (int) $sectionRows->first()->ticket_quantity,
                    'collector_id' => $sectionRows->first()->collector_id,
                ]);

                return response()->json([
                    'message' => 'Cash ticket collections saved.',
                    'date' => \Illuminate\Support\Carbon::parse($data['collection_date'])->toDateString(),
                    'totals' => [
                        'amount' => (float) $savedRowsBySection->sum('amount'),
                        'tickets' => (int) $savedRowsBySection->sum('tickets'),
                        'collectors' => $savedRowsBySection->pluck('collector_id')->filter()->unique()->count(),
                    ],
                    'rows' => $savedRowsBySection,
                ]);
            }

            if ($shouldUpdateProgress) {
                return redirect()
                    ->route('clerk.collections', [
                        'collection_month' => \Illuminate\Support\Carbon::parse($data['collection_date'])->month,
                        'collection_year' => \Illuminate\Support\Carbon::parse($data['collection_date'])->year,
                    ])
                    ->with('success', 'Cash ticket collections saved.');
            }

            return back()
                ->with('success', 'Cash ticket collections saved.')
                ->with('open_dialog', $request->input('open_dialog', 'cashTicketStep3'));
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'collector_id' => ['required', 'exists:users,id'],
            'stall_section' => ['required', 'in:FISH,PORK,POULTRY,BEEF,MIXED'],
            'ticket_quantity' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'numeric', 'min:0'],
            'collection_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'cash_ticket_assignment_id' => ['nullable', 'exists:cash_ticket_assignments,id'],
            'ticket_start' => ['nullable', 'integer', 'min:1'],
            'ticket_end' => ['nullable', 'integer', 'gte:ticket_start'],
            'shortage_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_dialog', $request->input('open_dialog', 'cashTicketStep3'));
        }

        $data = $validator->validated();

        $collection = CashTicketCollection::create([
            ...$data,
            'collection_number' => 'CT-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'recorded_by' => $request->user()->id,
            'status' => 'RECORDED',
        ]);

        if ($collection->assignment) {
            $collection->assignment->update(['status' => 'COMPLETED']);
        }

        return back()->with('success', 'Cash ticket collection saved.');
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
            'OVERDUE' => $filteredPayments->filter(fn (Payment $payment) => $this->clerkRentalStatus($payment) === 'OVERDUE')->count(),
            'PAID' => $filteredPayments->filter(fn (Payment $payment) => $this->clerkRentalStatus($payment) === 'PAID')->count(),
            'UNPAID' => $filteredPayments->filter(fn (Payment $payment) => $this->clerkRentalStatus($payment) === 'UNPAID')->count(),
        ];

        $payments = $filteredPayments->filter(function (Payment $payment) use ($request) {
            if (! $request->filled('status') || strtoupper($request->string('status')->toString()) === 'ALL') {
                return true;
            }

            return $this->clerkRentalStatus($payment) === strtoupper($request->string('status')->toString());
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
            'statusCounts' => $statusCounts,
        ]);
    }

    private function clerkRentalStatus(Payment $payment): string
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
}
