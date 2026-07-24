<?php

namespace App\Http\Controllers;


use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\BiddingApplication;
use App\Models\MarketStall;

class TreasurerController extends Controller
{
    public function treasurer_dashboard_view()
    {
        return view('treasurer.views.treasurer_dashboard');
    }
    public function bidding_request()
    {
        return view('treasurer.views.bidding_request');
    }
    public function biddingRequestData()
    {
        return response()->json(
            BiddingApplication::latest()->get()
        );
    }
    public function stallrental(Request $request)
    {
        $fishsection = MarketStall::where('section', 'Fish')
            ->orderBy('stall_no')
            ->get();

        $porksection = MarketStall::where('section', 'Pork')
            ->orderBy('stall_no')
            ->get();

        $poultrysection = MarketStall::where('section', 'Poultry')
            ->orderBy('stall_no')
            ->get();

        $beefsection = MarketStall::where('section', 'Beef')
            ->orderBy('stall_no')
            ->get();

        $mixedsection = MarketStall::where('section', 'Mixed')
            ->orderBy('stall_no')
            ->get();

        return view(
            'treasurer.views.stallrental',
            compact(
                'fishsection',
                'porksection',
                'poultrysection',
                'beefsection',
                'mixedsection'
            )
        );
    }

    public function storeStall(Request $request)
    {
        $validated = $request->validate([
            'section' => 'required|string|max:255',
            'stall_no' => 'required|integer',
        ]);

        $validated['status'] = 'available';
        MarketStall::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Stall added successfully.'
        ]);
    }

    public function deleteBilling(Request $request)
    {
        $id = $request->id;
        BiddingApplication::where('id', $id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Stall added successfully.'
        ]);
    }
}
