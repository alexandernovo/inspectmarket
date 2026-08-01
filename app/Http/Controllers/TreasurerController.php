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

        return view('market.portal.dashboard', [
            'pageTitle' => 'Treasurer Dashboard',
            'stats' => [
                ['label' => 'Cash Tickets', 'sublabel' => 'Total Collection', 'value' => 'P'.number_format(CashTicketCollection::sum('amount'), 2), 'tone' => 'blue', 'icon' => 'bi-ticket-perforated-fill'],
                ['label' => 'Stall Rental', 'sublabel' => 'Total Payment', 'value' => 'P'.number_format(Payment::where('status', 'PAID')->sum('amount'), 2), 'tone' => 'green', 'icon' => 'bi-shop-window'],
                ['label' => 'Unpaid Rentals', 'sublabel' => 'Pending Payment', 'value' => Payment::where('status', 'PENDING')->count(), 'tone' => 'red', 'icon' => 'bi-wallet2'],
                ['label' => 'Active Stalls', 'sublabel' => 'Approved Tenants', 'value' => StallApplication::where('status', 'APPROVED')->count(), 'tone' => 'gold', 'icon' => 'bi-grid-3x3-gap-fill'],
            ],
            'rows' => StallApplication::with(['tenant', 'stall'])->latest()->limit(8)->get(),
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

    public function rentals()
    {
        return view('market.portal.stalls', [
            'pageTitle' => 'Stall Rental Management',
            'applications' => StallApplication::with(['tenant', 'stall', 'documents'])->latest()->get(),
            'stalls' => Stall::where('status', 'AVAILABLE')->orderBy('section')->orderBy('stall_number')->get(),
            'canApply' => false,
            'canReview' => true,
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

    public function payments()
    {
        return view('market.portal.payments', [
            'pageTitle' => 'Stall Rental Payments',
            'payments' => Payment::with(['tenant', 'stallApplication.stall'])->latest('due_date')->get(),
            'canRecord' => true,
            'tenants' => User::where('usertype', User::ROLE_TENANT)->where('status', 'ACTIVE')->orderBy('lastname')->get(),
            'applications' => StallApplication::with(['tenant', 'stall'])->where('status', 'APPROVED')->latest()->get(),
        ]);
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
        $collector->update($request->validate([
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
            'designation' => ['required', 'string', 'max:150'],
        ]));

        return back()->with('success', 'Collector record updated.');
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
