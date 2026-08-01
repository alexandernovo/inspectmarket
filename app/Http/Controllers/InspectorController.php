<?php

namespace App\Http\Controllers;

use App\Models\LivestockInspection;
use App\Models\MarketNotification;
use App\Support\ServerDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InspectorController extends Controller
{
    public function dashboard()
    {
        $year = now()->year;
        $yearInspections = LivestockInspection::query()
            ->whereYear('scheduled_at', $year)
            ->get(['livestock_type', 'scheduled_at', 'animal_count']);
        $monthlyTotals = $yearInspections
            ->groupBy('livestock_type')
            ->map(fn ($rows) => $rows
                ->groupBy(fn ($inspection) => $inspection->scheduled_at->month)
                ->map(fn ($monthRows) => $monthRows->sum('animal_count')));

        return view('market.inspector.dashboard', [
            'pageTitle' => 'Inspector Dashboard',
            'stats' => [
                ['label' => 'Total Requests', 'sublabel' => 'Slaughtered Inspection', 'value' => LivestockInspection::where('request_source', '!=', 'INSPECTOR')->count(), 'tone' => 'blue', 'icon' => 'bi-people-fill'],
                ['label' => 'Total Inspect', 'sublabel' => 'Poultry Slaughtered', 'value' => LivestockInspection::where('livestock_type', 'POULTRY')->whereIn('status', ['APPROVED', 'COMPLETED'])->sum('animal_count'), 'tone' => 'green', 'icon' => 'bi-egg-fried'],
                ['label' => 'Total Inspect', 'sublabel' => 'Pork Slaughtered', 'value' => LivestockInspection::where('livestock_type', 'PORK')->whereIn('status', ['APPROVED', 'COMPLETED'])->sum('animal_count'), 'tone' => 'red', 'icon' => 'bi-piggy-bank-fill'],
                ['label' => 'Total Inspect', 'sublabel' => 'Beef Slaughtered', 'value' => LivestockInspection::where('livestock_type', 'BEEF')->whereIn('status', ['APPROVED', 'COMPLETED'])->sum('animal_count'), 'tone' => 'gold', 'icon' => 'bi-heart-pulse-fill'],
            ],
            'monthlyTotals' => $monthlyTotals,
            'chartYears' => LivestockInspection::query()
                ->get(['scheduled_at'])
                ->map(fn ($inspection) => $inspection->scheduled_at->year)
                ->unique()
                ->sortDesc()
                ->values(),
            'pendingCount' => LivestockInspection::where('status', 'PENDING')->where('request_source', '!=', 'INSPECTOR')->count(),
        ]);
    }

    public function inspections(Request $request)
    {
        $type = strtoupper($request->string('type')->toString());

        if (in_array($type, ['POULTRY', 'PORK', 'BEEF'], true)) {
            return view('market.inspector.records', [
                'pageTitle' => ucfirst(strtolower($type)),
                'livestockType' => $type,
            ]);
        }

        $month = now()->startOfMonth();
        $reservationRows = LivestockInspection::query()
            ->whereIn('status', ['PENDING', 'APPROVED', 'COMPLETED'])
            ->orderBy('scheduled_at')
            ->get();
        $reservations = $reservationRows
            ->groupBy(fn ($inspection) => $inspection->scheduled_at->format('Y-m'))
            ->map(fn ($monthRows) => $monthRows->groupBy(fn ($inspection) => (string) $inspection->scheduled_at->day)
            ->map(fn ($rows) => $rows->map(fn ($inspection) => [
                'time' => $inspection->scheduled_at->format('g:i A'),
                'owner' => $inspection->owner_name,
                'type' => $inspection->livestock_type,
                'status' => $inspection->status,
            ])->values()));

        return view('market.inspector.requests', [
            'pageTitle' => 'Request Inspection',
            'inspectionMonth' => $month,
            'reservations' => $reservations,
            'calendarYears' => collect(range(now()->year - 1, now()->year + 2))
                ->merge($reservationRows->pluck('scheduled_at')->map->year)
                ->unique()
                ->sort()
                ->values(),
            'pendingCount' => LivestockInspection::where('status', 'PENDING')->where('request_source', '!=', 'INSPECTOR')->count(),
        ]);
    }

    public function dataTable(Request $request)
    {
        $mode = $request->string('mode', 'requests')->toString();
        $query = LivestockInspection::query()->with(['tenant', 'inspector']);

        if ($mode === 'records') {
            $query->whereIn('status', ['APPROVED', 'COMPLETED']);
            if ($request->filled('type')) {
                $query->where('livestock_type', strtoupper($request->string('type')->toString()));
            }
        } else {
            $query->where('request_source', '!=', 'INSPECTOR');
            if ($request->filled('status') && strtoupper($request->string('status')->toString()) !== 'ALL') {
                $query->where('status', strtoupper($request->string('status')->toString()));
            }
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
            function (LivestockInspection $inspection) use ($mode) {
                $record = e(json_encode($this->recordPayload($inspection)));
                $view = '<button type="button" class="inspector-table-action view js-inspector-view" data-record="'.$record.'" title="View"><i class="bi bi-eye-fill"></i></button>';

                if ($mode !== 'records') {
                    $action = '<button type="button" class="inspector-table-action view js-request-view" data-record="'.$record.'" title="View request"><i class="bi bi-eye-fill"></i></button>';
                    if ($inspection->status === 'PENDING') {
                        $action .= ' <button type="button" class="inspector-table-action approve js-request-decision" data-status="APPROVED" data-record="'.$record.'" title="Approve request"><i class="bi bi-check-lg"></i></button>'
                            .' <button type="button" class="inspector-table-action disapprove js-request-decision" data-status="DISAPPROVED" data-record="'.$record.'" title="Disapprove request"><i class="bi bi-x-lg"></i></button>';
                    }
                } else {
                    $action = $view
                        .' <button type="button" class="inspector-table-action edit js-inspector-edit" data-record="'.$record.'" title="Edit"><i class="bi bi-pencil-fill"></i></button>'
                        .' <button type="button" class="inspector-table-action delete js-inspector-delete" data-url="'.route('inspector.inspections.destroy', $inspection).'" data-reference="'.e($inspection->request_number).'" title="Delete"><i class="bi bi-trash-fill"></i></button>';
                }

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
                    'result' => e($this->resultLabel($inspection->inspection_result)),
                    'status' => $this->statusBadge($inspection->status),
                    'action' => $action,
                ];
            }
        );
    }

    public function store(Request $request)
    {
        $data = $this->validateInspection($request);
        $inspection = LivestockInspection::create([
            ...$data,
            'request_number' => 'INSP-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'tenant_id' => null,
            'inspector_id' => $request->user()->id,
            'request_source' => 'INSPECTOR',
            'status' => 'COMPLETED',
            'inspected_at' => now(),
            'certificate_number' => 'CERT-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "{$inspection->livestock_type} inspection record added.",
        ]);
    }

    public function update(Request $request, LivestockInspection $inspection)
    {
        $data = $request->validate([
            'status' => ['required', 'in:APPROVED,DISAPPROVED,COMPLETED'],
            'inspection_result' => ['nullable', 'in:PASSED,CONDEMNED,REINSPECTION'],
            'owner_name' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'required', 'string', 'max:255'],
            'contact_number' => ['sometimes', 'required', 'string', 'max:30'],
            'scheduled_at' => ['sometimes', 'required', 'date'],
            'animal_count' => ['sometimes', 'required', 'integer', 'min:1', 'max:10000'],
            'findings' => ['nullable', 'string', 'max:2000'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'breed' => ['nullable', 'string', 'max:100'],
            'sex' => ['nullable', 'in:MALE,FEMALE,MIXED'],
            'animal_age' => ['nullable', 'string', 'max:100'],
            'live_weight' => ['nullable', 'numeric', 'min:0'],
            'carcass_weight' => ['nullable', 'numeric', 'min:0'],
            'source_location' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'ante_mortem_findings' => ['nullable', 'array'],
            'post_mortem_findings' => ['nullable', 'array'],
        ]);

        $inspection->update([
            ...$data,
            'inspector_id' => $request->user()->id,
            'inspected_at' => $data['status'] === 'COMPLETED' ? now() : $inspection->inspected_at,
            'certificate_number' => $data['status'] === 'COMPLETED'
                ? ($inspection->certificate_number ?: 'CERT-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)))
                : $inspection->certificate_number,
        ]);

        $this->notifyTenant($inspection, "Inspection {$data['status']}", "{$inspection->request_number} is now {$data['status']}.");

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Inspection record updated.']);
        }

        return back()->with('success', 'Inspection record updated.');
    }

    public function destroy(Request $request, LivestockInspection $inspection)
    {
        $reference = $inspection->request_number;
        $this->notifyTenant($inspection, 'Inspection record removed', "{$reference} was removed by the sanitary inspector.");
        $inspection->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Inspection record deleted.']);
        }

        return back()->with('success', 'Inspection record deleted.');
    }

    public function exportReport(Request $request, string $format)
    {
        $type = strtoupper($request->string('livestock')->toString());
        abort_unless(in_array($type, ['POULTRY', 'PORK', 'BEEF'], true), 422);
        $month = $request->date('month')?->startOfMonth();
        abort_unless($month, 422);

        $rows = LivestockInspection::query()
            ->where('livestock_type', $type)
            ->where('status', 'COMPLETED')
            ->whereBetween('scheduled_at', [$month, $month->copy()->endOfMonth()])
            ->oldest('scheduled_at')
            ->get();
        $contentType = $format === 'doc' ? 'application/msword' : 'application/vnd.ms-excel';
        $filename = strtolower($type).'-inspection-'.$month->format('Y-m').'.'.$format;

        return response()
            ->view('market.inspector.report-export', compact('rows', 'type', 'month'))
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    private function validateInspection(Request $request): array
    {
        return $request->validate([
            'livestock_type' => ['required', 'in:POULTRY,PORK,BEEF'],
            'owner_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:30'],
            'scheduled_at' => ['required', 'date'],
            'animal_count' => ['required', 'integer', 'min:1', 'max:10000'],
            'inspection_result' => ['required', 'in:PASSED,CONDEMNED,REINSPECTION'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'breed' => ['nullable', 'string', 'max:100'],
            'animal_age' => ['nullable', 'string', 'max:100'],
            'live_weight' => ['nullable', 'numeric', 'min:0'],
            'source_location' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'ante_mortem_findings' => ['nullable', 'array'],
            'post_mortem_findings' => ['nullable', 'array'],
        ]);
    }

    private function recordPayload(LivestockInspection $inspection): array
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
            'update_url' => route('inspector.inspections.update', $inspection),
        ];
    }

    private function notifyTenant(LivestockInspection $inspection, string $title, string $message): void
    {
        if (! $inspection->tenant_id) {
            return;
        }

        MarketNotification::create([
            'user_id' => $inspection->tenant_id,
            'type' => 'INSPECTION',
            'title' => $title,
            'message' => $message,
            'action_url' => route('tenant.inspections'),
        ]);
    }

    private function resultLabel(?string $result): string
    {
        return match ($result) {
            'PASSED' => 'Passed with Human Consumption',
            'CONDEMNED' => 'Condemned',
            'REINSPECTION' => 'For Further Examination',
            default => 'Pending Inspection',
        };
    }

    private function statusBadge(string $status): string
    {
        $label = $status === 'APPROVED' ? 'Accepted' : ucfirst(strtolower($status));

        return '<span class="inspector-status inspector-status-'.strtolower($status).'">'.e($label).'</span>';
    }
}
