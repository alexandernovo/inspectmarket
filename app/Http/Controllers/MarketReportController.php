<?php

namespace App\Http\Controllers;

use App\Models\CashTicketCollection;
use App\Models\LivestockInspection;
use App\Models\Payment;
use App\Models\StallApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MarketReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $allowed = match ($user->usertype) {
            User::ROLE_INSPECTOR => ['inspection'],
            User::ROLE_CLERK => ['cash-ticket', 'stall-rental'],
            User::ROLE_TREASURER, User::ROLE_ADMINISTRATOR => ['cash-ticket', 'stall-rental', 'inspection', 'payments'],
            default => [],
        };
        abort_if($allowed === [], 403);

        $report = $request->string('report', $allowed[0])->toString();
        abort_unless(in_array($report, $allowed, true), 403);
        $month = $request->date('month')?->startOfMonth();
        $section = strtoupper($request->string('section')->toString());
        $livestock = strtoupper($request->string('livestock')->toString());

        $rows = match ($report) {
            'cash-ticket' => CashTicketCollection::with('collector')
                ->when($user->isRole(User::ROLE_CLERK), fn (Builder $query) => $query->where(fn (Builder $nested) => $nested->where('collector_id', $user->id)->orWhere('recorded_by', $user->id)))
                ->when($month, fn (Builder $query) => $query->whereBetween('collection_date', [$month, $month->copy()->endOfMonth()]))
                ->when($section, fn (Builder $query) => $query->where('stall_section', $section))
                ->latest('collection_date')->get(),
            'inspection' => LivestockInspection::with(['tenant', 'inspector'])
                ->when($month, fn (Builder $query) => $query->whereBetween('scheduled_at', [$month, $month->copy()->endOfMonth()]))
                ->when($livestock, fn (Builder $query) => $query->where('livestock_type', $livestock))
                ->latest('scheduled_at')->get(),
            'payments' => Payment::with('tenant')
                ->when($month, fn (Builder $query) => $query->whereBetween('period_month', [$month, $month->copy()->endOfMonth()]))
                ->latest('due_date')->get(),
            default => StallApplication::with(['tenant', 'stall'])
                ->when($section, fn (Builder $query) => $query->where('preferred_section', $section))
                ->latest()->get(),
        };

        return view('market.portal.reports', [
            'pageTitle' => 'Market Reports',
            'report' => $report,
            'rows' => $rows,
            'allowedReports' => $allowed,
            'printMode' => false,
        ]);
    }

    public function printable(Request $request)
    {
        $response = $this->index($request);
        $response->with('printMode', true);

        return $response;
    }
}
