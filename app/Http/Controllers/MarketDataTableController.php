<?php

namespace App\Http\Controllers;

use App\Models\CashTicketAssignment;
use App\Models\CashTicketCollection;
use App\Models\LivestockInspection;
use App\Models\Payment;
use App\Models\StallApplication;
use App\Models\User;
use App\Support\ServerDataTable;
use Illuminate\Http\Request;

class MarketDataTableController extends Controller
{
    public function applications(Request $request)
    {
        $query = StallApplication::query()->with(['tenant', 'stall', 'documents']);

        if ($request->user()->isRole(User::ROLE_TENANT)) {
            $query->where('tenant_id', $request->user()->id);
        }
        if ($request->filled('section') && strtoupper($request->string('section')->toString()) !== 'ALL') {
            $query->where('preferred_section', strtoupper($request->string('section')->toString()));
        }
        if ($request->filled('status') && strtoupper($request->string('status')->toString()) !== 'ALL') {
            $query->where('status', strtoupper($request->string('status')->toString()));
        }
        if ($request->filled('dateFrom')) {
            $query->whereDate('created_at', '>=', $request->date('dateFrom'));
        }
        if ($request->filled('dateTo')) {
            $query->whereDate('created_at', '<=', $request->date('dateTo'));
        }

        return ServerDataTable::make(
            $request,
            $query,
            ['application_number', 'tin_number', 'business_name', 'business_category', 'preferred_section', 'status'],
            ['application_number', 'tin_number', 'business_name', 'preferred_section', 'preferred_stall_number', 'status', 'created_at'],
            function (StallApplication $application) use ($request) {
                $action = '<a class="table-action" href="'.route('stall-applications.show', $application).'" title="View"><i class="bi bi-eye-fill"></i></a>';

                if ($request->user()->isRole(User::ROLE_TENANT)) {
                    $action = '<button type="button" class="table-action js-view-application" data-url="'.route('stall-applications.show', $application).'" title="View"><i class="bi bi-eye-fill"></i></button>';
                }

                if ($request->user()->isRole(User::ROLE_TREASURER)) {
                    $action .= ' <button type="button" class="table-action approve js-review-application" data-id="'.$application->id.'" data-reference="'.e($application->application_number).'" title="Review"><i class="bi bi-pencil-square"></i></button>';
                }
                if ($request->user()->isRole(User::ROLE_TENANT) && $application->status === 'PENDING') {
                    $action .= ' <button type="button" class="table-action js-edit-application" data-url="'.route('tenant.applications.edit', $application).'" title="Edit"><i class="bi bi-pencil-fill"></i></button>';
                    $action .= ' <button type="button" class="table-action reject js-delete-application" data-url="'.route('tenant.applications.destroy', $application).'" data-reference="'.e($application->application_number).'" title="Delete"><i class="bi bi-trash-fill"></i></button>';
                }

                return [
                    'reference' => e($application->application_number),
                    'tin_number' => e($application->tin_number ?: 'Not provided'),
                    'owner_name' => e($application->business_owner ?: $application->tenant?->full_name),
                    'address' => e($application->business_address),
                    'contact' => e($application->contact_number),
                    'tenant' => '<strong>'.e($application->tenant?->full_name ?? $application->business_owner).'</strong><br><small>'.e($application->business_name).'</small>',
                    'section' => e($application->preferred_section),
                    'stall' => e($application->stall?->stall_number ?? $application->preferred_stall_number ?? 'Unassigned'),
                    'status' => $this->status($application->status),
                    'submitted' => $application->created_at->format('M d, Y'),
                    'action' => $action,
                ];
            }
        );
    }

    public function payments(Request $request)
    {
        $query = Payment::query()->with(['tenant', 'stallApplication.stall']);

        if ($request->user()->isRole(User::ROLE_TENANT)) {
            $query->where('tenant_id', $request->user()->id);
        }

        return ServerDataTable::make(
            $request,
            $query,
            ['reference_number', 'status', 'payment_method', 'or_number'],
            ['reference_number', 'tenant_id', 'amount', 'period_month', 'due_date', 'paid_at', 'status'],
            function (Payment $payment) use ($request) {
                $action = '<a class="table-action" target="_blank" href="'.route('payments.receipt', $payment).'" title="Receipt"><i class="bi bi-receipt"></i></a>';
                if ($request->user()->isRole(User::ROLE_TREASURER) && $payment->status === 'SUBMITTED') {
                    $action .= ' <button type="button" class="table-action approve js-verify-payment" data-id="'.$payment->id.'" data-reference="'.e($payment->reference_number).'"><i class="bi bi-check-lg"></i></button>';
                }

                return [
                    'reference' => e($payment->reference_number),
                    'tenant' => e($payment->tenant?->full_name),
                    'stall' => e(trim(($payment->stallApplication?->stall?->section ?? '').' '.($payment->stallApplication?->stall?->stall_number ?? '')) ?: '—'),
                    'period' => $payment->period_month->format('F Y'),
                    'amount' => '₱'.number_format((float) $payment->amount, 2),
                    'due' => $payment->due_date->format('M d, Y'),
                    'paid' => $payment->paid_at?->format('M d, Y') ?? '—',
                    'status' => $this->status($payment->status),
                    'action' => $action,
                ];
            }
        );
    }

    public function inspections(Request $request)
    {
        $query = LivestockInspection::query()->with(['tenant', 'inspector']);

        if ($request->user()->isRole(User::ROLE_TENANT)) {
            $query->where('tenant_id', $request->user()->id);
        }
        if ($request->filled('type')) {
            $query->where('livestock_type', strtoupper($request->string('type')->toString()));
        }
        if ($request->filled('status') && strtoupper($request->string('status')->toString()) !== 'ALL') {
            $query->where('status', strtoupper($request->string('status')->toString()));
        }
        if ($request->filled('dateFrom')) {
            $query->whereDate('scheduled_at', '>=', $request->date('dateFrom'));
        }
        if ($request->filled('dateTo')) {
            $query->whereDate('scheduled_at', '<=', $request->date('dateTo'));
        }
        if ($request->user()->isRole(User::ROLE_TENANT) && ! $request->has('order.0.column')) {
            $request->merge(['order' => [['column' => 4, 'dir' => 'desc']]]);
        }

        return ServerDataTable::make(
            $request,
            $query,
            ['request_number', 'owner_name', 'address', 'contact_number', 'livestock_type', 'status', 'inspection_result'],
            ['request_number', 'owner_name', 'livestock_type', 'animal_count', 'scheduled_at', 'inspection_result', 'status'],
            function (LivestockInspection $inspection) use ($request) {
                $action = '<a class="table-action" href="'.route('inspections.show', $inspection).'"><i class="bi bi-eye"></i></a>';
                if ($request->user()->isRole(User::ROLE_TENANT)) {
                    $record = e(json_encode([
                        'id' => $inspection->id,
                        'livestock_type' => $inspection->livestock_type,
                        'owner_name' => $inspection->owner_name,
                        'address' => $inspection->address,
                        'contact_number' => $inspection->contact_number,
                        'scheduled_date' => $inspection->scheduled_at->format('Y-m-d'),
                        'scheduled_time' => $inspection->scheduled_at->format('H:i'),
                        'animal_count' => $inspection->animal_count,
                        'update_url' => route('tenant.inspections.update', $inspection),
                    ]));
                    $action = '<button type="button" class="table-action view js-view-inspection" data-record="'.$record.'" title="View"><i class="bi bi-eye-fill"></i></button>';
                    if ($inspection->status === 'PENDING') {
                        $action .= ' <button type="button" class="table-action edit js-edit-inspection" data-record="'.$record.'" title="Edit"><i class="bi bi-pencil-fill"></i></button>';
                        $action .= ' <button type="button" class="table-action reject js-delete-inspection" data-url="'.route('tenant.inspections.destroy', $inspection).'" data-reference="'.e($inspection->request_number).'" title="Delete"><i class="bi bi-trash-fill"></i></button>';
                    }
                }
                if ($request->user()->isRole(User::ROLE_INSPECTOR)) {
                    $action .= ' <button type="button" class="table-action approve js-review-inspection" data-id="'.$inspection->id.'" data-reference="'.e($inspection->request_number).'"><i class="bi bi-clipboard2-check"></i></button>';
                }
                if ($inspection->status === 'COMPLETED') {
                    $action .= ' <a class="table-action" target="_blank" href="'.route('inspections.certificate', $inspection).'"><i class="bi bi-printer"></i></a>';
                }

                return [
                    'reference' => e($inspection->request_number),
                    'owner_name' => e($inspection->owner_name),
                    'address' => e($inspection->address),
                    'contact' => e($inspection->contact_number),
                    'owner' => '<strong>'.e($inspection->owner_name).'</strong><br><small>'.e($inspection->contact_number).'</small>',
                    'type' => e($inspection->livestock_type),
                    'animals' => $inspection->animal_count,
                    'schedule' => $inspection->scheduled_at->format('M d, Y g:i A'),
                    'result' => e($inspection->inspection_result ?? '—'),
                    'status' => $this->status($inspection->status),
                    'action' => $action,
                ];
            }
        );
    }

    public function collections(Request $request)
    {
        $query = CashTicketCollection::query()->with(['collector', 'assignment']);

        if ($request->user()->isRole(User::ROLE_CLERK)) {
            $query->where(fn ($builder) => $builder
                ->where('collector_id', $request->user()->id)
                ->orWhere('recorded_by', $request->user()->id));
        }

        return ServerDataTable::make(
            $request,
            $query,
            ['collection_number', 'stall_section', 'status'],
            ['collection_number', 'collector_id', 'stall_section', 'ticket_quantity', 'amount', 'collection_date', 'status'],
            fn (CashTicketCollection $collection) => [
                'reference' => e($collection->collection_number),
                'collector' => e($collection->collector?->full_name),
                'section' => e($collection->stall_section),
                'tickets' => number_format($collection->ticket_quantity),
                'amount' => '₱'.number_format((float) $collection->amount, 2),
                'date' => $collection->collection_date->format('M d, Y'),
                'status' => $this->status($collection->status),
            ]
        );
    }

    public function assignments(Request $request)
    {
        $query = CashTicketAssignment::query()->with('collector');

        if ($request->user()->isRole(User::ROLE_CLERK)) {
            $query->where('collector_id', $request->user()->id);
        }

        return ServerDataTable::make(
            $request,
            $query,
            ['assignment_number', 'stall_section', 'status'],
            ['assignment_number', 'collector_id', 'stall_section', 'ticket_start', 'ticket_quantity', 'assigned_date', 'status'],
            fn (CashTicketAssignment $assignment) => [
                'reference' => e($assignment->assignment_number),
                'collector' => e($assignment->collector?->full_name),
                'section' => e($assignment->stall_section),
                'range' => "{$assignment->ticket_start}–{$assignment->ticket_end}",
                'quantity' => number_format($assignment->ticket_quantity),
                'date' => $assignment->assigned_date->format('M d, Y'),
                'status' => $this->status($assignment->status),
            ]
        );
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

        $query = User::query()->where('usertype', $role);

        return ServerDataTable::make(
            $request,
            $query,
            ['firstname', 'middlename', 'lastname', 'username', 'email', 'designation', 'address', 'status'],
            ['lastname', 'username', 'designation', 'phone_num', 'address', 'status'],
            fn (User $user) => [
                'name' => '<strong>'.e($user->full_name).'</strong><br><small>'.e($user->email).'</small>',
                'username' => e($user->username),
                'designation' => e($user->designation),
                'contact' => e($user->phone_num),
                'address' => e($user->address),
                'status' => $this->status($user->status),
                'action' => '<form action="'.route('administrator.members.update', $user).'" method="POST"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PUT"><input type="hidden" name="status" value="'.($user->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE').'"><button class="table-action" title="Toggle status"><i class="bi bi-power"></i></button></form>',
            ]
        );
    }

    private function status(string $status): string
    {
        return '<span class="status status-'.strtolower(e($status)).'">'.e($status).'</span>';
    }
}
