<?php

namespace App\Http\Controllers;

use App\Models\CashTicketAssignment;
use App\Models\CashTicketCollection;
use App\Models\Payment;
use App\Models\StallApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClerkController extends Controller
{
    public function dashboard()
    {
        return view('market.portal.dashboard', [
            'pageTitle' => 'Clerk Dashboard',
            'stats' => [
                ['label' => 'Tickets Collected', 'value' => CashTicketCollection::sum('ticket_quantity'), 'icon' => 'bi-ticket-perforated'],
                ['label' => 'Total Collection', 'value' => '₱'.number_format(CashTicketCollection::sum('amount'), 2), 'icon' => 'bi-cash-stack'],
                ['label' => 'Unpaid Rentals', 'value' => Payment::where('status', 'PENDING')->count(), 'icon' => 'bi-exclamation-circle'],
                ['label' => 'Active Tenants', 'value' => User::where('usertype', User::ROLE_TENANT)->where('status', 'ACTIVE')->count(), 'icon' => 'bi-people'],
            ],
            'rows' => CashTicketCollection::with('collector')->latest('collection_date')->limit(8)->get(),
            'rowType' => 'collections',
            'chartTitle' => 'Cash ticket and stall rental activity',
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

    public function storeCollection(Request $request)
    {
        $data = $request->validate([
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

        $collection = CashTicketCollection::create([
            ...$data,
            'collection_number' => 'CT-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'recorded_by' => $request->user()->id,
            'status' => 'SUBMITTED',
        ]);

        if ($collection->assignment) {
            $collection->assignment->update(['status' => 'COMPLETED']);
        }

        return back()->with('success', 'Cash ticket collection saved.');
    }

    public function rentals()
    {
        return view('market.portal.stalls', [
            'pageTitle' => 'Tenant Stall Rentals',
            'applications' => StallApplication::with(['tenant', 'stall'])->latest()->get(),
            'stalls' => collect(),
            'canApply' => false,
        ]);
    }
}
