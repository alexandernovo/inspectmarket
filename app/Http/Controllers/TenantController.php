<?php

namespace App\Http\Controllers;


use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\BiddingApplication;


class TenantController extends Controller
{
    public function tenant_dashboard_view()
    {
        return view('tenant.views.tenant_dashboard');
    }
    public function bidding_request(Request $request)
    {
        $application_id = $request->query('application_id') ?? "";
        $application = null;
        
        if ($application_id) {
            $application = BiddingApplication::find($application_id);
        }
        return view('tenant.views.bidding_request', compact('application'));
    }

    public function bidding_request_table()
    {
        return view('tenant.views.bidding_request_table');
    }
    public function storeBiddingApplication(Request $request)
    {
        $validated = $request->validate([
            'name_owner' => 'required|string|max:255',
            'tin' => 'nullable|string|max:255',
            'address' => 'required|string',
            'cellphone_no' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'nature_business' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'business_trade_name' => 'nullable|string|max:255',
            'stall_applied' => 'nullable|string|max:255',
            'alternative_stall' => 'nullable|string|max:255',
            'other_business' => 'nullable|string|max:255',
            'date_filed' => 'nullable|date',
            'received_by' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'signature_name' => 'nullable|string|max:255',
        ]);

        if ($request->filled('id')) {

            $application = BiddingApplication::findOrFail($request->id);

            $application->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Bidding application updated successfully.'
            ]);
        }

        BiddingApplication::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Bidding application submitted successfully.'
        ]);
    }

    public function getBiddingApplications()
    {
        $applications = BiddingApplication::latest()->get();

        return response()->json($applications);
    }
}
