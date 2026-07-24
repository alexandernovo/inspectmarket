<?php

namespace App\Http\Controllers;

use App\Models\LivestockInspection;
use App\Models\MarketNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InspectorController extends Controller
{
    public function dashboard()
    {
        return view('market.portal.dashboard', [
            'pageTitle' => 'Inspector Dashboard',
            'stats' => [
                ['label' => 'Pending Requests', 'value' => LivestockInspection::where('status', 'PENDING')->count(), 'icon' => 'bi-hourglass-split'],
                ['label' => 'Approved Schedule', 'value' => LivestockInspection::where('status', 'APPROVED')->count(), 'icon' => 'bi-calendar2-check'],
                ['label' => 'Completed', 'value' => LivestockInspection::where('status', 'COMPLETED')->count(), 'icon' => 'bi-clipboard2-check'],
                ['label' => 'Total Animals', 'value' => LivestockInspection::sum('animal_count'), 'icon' => 'bi-list-ol'],
            ],
            'rows' => LivestockInspection::with('tenant')->latest('scheduled_at')->limit(8)->get(),
            'rowType' => 'inspections',
            'chartTitle' => 'Slaughtered livestock activity',
        ]);
    }

    public function inspections(Request $request)
    {
        $query = LivestockInspection::query()->with(['tenant', 'inspector']);

        if ($request->filled('type')) {
            $query->where('livestock_type', strtoupper($request->string('type')->toString()));
        }

        return view('market.portal.inspections', [
            'pageTitle' => 'Slaughtered Livestock Inspections',
            'inspections' => $query->latest('scheduled_at')->get(),
            'canRequest' => false,
            'canReview' => true,
        ]);
    }

    public function update(Request $request, LivestockInspection $inspection)
    {
        $data = $request->validate([
            'status' => ['required', 'in:APPROVED,DISAPPROVED,COMPLETED'],
            'inspection_result' => ['nullable', 'in:PASSED,CONDEMNED,REINSPECTION'],
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

        MarketNotification::create([
            'user_id' => $inspection->tenant_id,
            'type' => 'INSPECTION',
            'title' => "Inspection {$data['status']}",
            'message' => "{$inspection->request_number} is now {$data['status']}.",
            'action_url' => route('tenant.inspections'),
        ]);

        return back()->with('success', 'Inspection record updated.');
    }
}
