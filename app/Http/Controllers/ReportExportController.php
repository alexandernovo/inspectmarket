<?php

namespace App\Http\Controllers;

use App\Models\CashTicketAssignment;
use App\Models\CashTicketCollection;
use App\Models\LivestockInspection;
use App\Models\Payment;
use App\Models\StallApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function csv(Request $request, string $report): StreamedResponse
    {
        $user = $request->user();

        [$headers, $rows] = match ($report) {
            'cash-ticket' => [
                ['Reference', 'Collector', 'Section', 'Tickets', 'Amount', 'Date', 'Status'],
                tap(CashTicketCollection::with('collector'), function ($query) use ($user) {
                    abort_unless($user->isRole(User::ROLE_ADMINISTRATOR) || $user->isRole(User::ROLE_TREASURER) || $user->isRole(User::ROLE_CLERK), 403);
                    if ($user->isRole(User::ROLE_CLERK)) {
                        $query->where(fn ($nested) => $nested->where('collector_id', $user->id)->orWhere('recorded_by', $user->id));
                    }
                })->get()->map(fn ($row) => [
                    $row->collection_number,
                    $row->collector?->full_name,
                    $row->stall_section,
                    $row->ticket_quantity,
                    $row->amount,
                    $row->collection_date->toDateString(),
                    $row->status,
                ]),
            ],
            'inspection' => [
                ['Request', 'Owner', 'Livestock', 'Count', 'Schedule', 'Result', 'Status'],
                tap(LivestockInspection::query(), function ($query) use ($user) {
                    abort_unless($user->isRole(User::ROLE_ADMINISTRATOR) || $user->isRole(User::ROLE_INSPECTOR) || $user->isRole(User::ROLE_TENANT), 403);
                    if ($user->isRole(User::ROLE_TENANT)) {
                        $query->where('tenant_id', $user->id);
                    }
                })->get()->map(fn ($row) => [
                    $row->request_number,
                    $row->owner_name,
                    $row->livestock_type,
                    $row->animal_count,
                    $row->scheduled_at->toDateTimeString(),
                    $row->inspection_result,
                    $row->status,
                ]),
            ],
            'payments' => [
                ['Reference', 'Tenant', 'Amount', 'Period', 'Due', 'Paid', 'Status'],
                tap(Payment::with('tenant'), function ($query) use ($user) {
                    abort_unless($user->isRole(User::ROLE_ADMINISTRATOR) || $user->isRole(User::ROLE_TREASURER) || $user->isRole(User::ROLE_TENANT), 403);
                    if ($user->isRole(User::ROLE_TENANT)) {
                        $query->where('tenant_id', $user->id);
                    }
                })->get()->map(fn ($row) => [
                    $row->reference_number,
                    $row->tenant?->full_name,
                    $row->amount,
                    $row->period_month->format('Y-m'),
                    $row->due_date->toDateString(),
                    $row->paid_at?->toDateTimeString(),
                    $row->status,
                ]),
            ],
            default => [
                ['Application', 'Tenant', 'Business', 'Section', 'Stall', 'Status'],
                tap(StallApplication::with(['tenant', 'stall']), function ($query) use ($user) {
                    abort_unless(! $user->isRole(User::ROLE_INSPECTOR), 403);
                    if ($user->isRole(User::ROLE_TENANT)) {
                        $query->where('tenant_id', $user->id);
                    }
                })->get()->map(fn ($row) => [
                    $row->application_number,
                    $row->tenant?->full_name,
                    $row->business_name,
                    $row->preferred_section,
                    $row->stall?->stall_number,
                    $row->status,
                ]),
            ],
        };

        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, "einspect-{$report}-".now()->format('Ymd').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function paymentReceipt(Request $request, Payment $payment)
    {
        abort_unless(
            ! $request->user()->isRole('TENANT') || $payment->tenant_id === $request->user()->id,
            403
        );

        return view('market.print.payment-receipt', compact('payment'));
    }

    public function inspectionCertificate(Request $request, LivestockInspection $inspection)
    {
        abort_unless($inspection->status === 'COMPLETED', 404);
        abort_if(
            $request->user()->isRole(User::ROLE_TENANT) && $inspection->tenant_id !== $request->user()->id,
            403
        );

        return view('market.print.inspection-certificate', compact('inspection'));
    }

    public function assignmentSlip(Request $request, CashTicketAssignment $assignment)
    {
        abort_unless(in_array($request->user()->usertype, [
            User::ROLE_ADMINISTRATOR,
            User::ROLE_TREASURER,
            User::ROLE_CLERK,
        ], true), 403);
        abort_if($request->user()->isRole(User::ROLE_CLERK) && $assignment->collector_id !== $request->user()->id, 403);

        return view('market.print.cash-ticket-slip', compact('assignment'));
    }

    public function collectionReport(Request $request, CashTicketCollection $collection)
    {
        abort_unless(in_array($request->user()->usertype, [
            User::ROLE_ADMINISTRATOR,
            User::ROLE_TREASURER,
            User::ROLE_CLERK,
        ], true), 403);
        abort_if(
            $request->user()->isRole(User::ROLE_CLERK)
            && $collection->collector_id !== $request->user()->id
            && $collection->recorded_by !== $request->user()->id,
            403
        );

        return view('market.print.collection-report', compact('collection'));
    }
}
