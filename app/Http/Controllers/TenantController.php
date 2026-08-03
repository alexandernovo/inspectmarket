<?php

namespace App\Http\Controllers;

use App\Models\LivestockInspection;
use App\Models\MarketNotification;
use App\Models\Payment;
use App\Models\Stall;
use App\Models\StallApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $monthlyRentals = collect(range(1, 12))->map(
            fn (int $month) => $user->stallApplications()
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', $month)
                ->count()
        );
        $largestMonthlyTotal = max(1, $monthlyRentals->max());

        return view('market.portal.dashboard', [
            'pageTitle' => 'Tenant Dashboard',
            'stats' => [
                ['label' => 'Total Tenants', 'value' => User::where('usertype', User::ROLE_TENANT)->where('status', 'ACTIVE')->count(), 'image' => 'USERS/5-Tenants.png', 'tone' => 'blue'],
                ['label' => 'Total Vacancy', 'value' => Stall::where('status', 'AVAILABLE')->count(), 'image' => 'HOMEPAGE/Stall.png', 'tone' => 'green'],
                ['label' => 'Total Occupied', 'value' => Stall::where('status', 'OCCUPIED')->count(), 'image' => 'HOMEPAGE/Fish Section.png', 'tone' => 'red'],
                ['label' => 'Total Payment Fee', 'value' => 'P '.number_format($user->payments()->where('status', 'PAID')->sum('amount'), 2), 'icon' => 'bi-cash-stack', 'tone' => 'gold'],
            ],
            'rowType' => 'inspections',
            'chartTitle' => 'STALL RENTAL DATA CHART',
            'chartTotals' => $monthlyRentals,
            'chartValues' => $monthlyRentals->map(
                fn (int $total) => max(4, (int) round(($total / $largestMonthlyTotal) * 100))
            ),
        ]);
    }

    public function applications(Request $request)
    {
        return view('market.tenant.applications.index', [
            'pageTitle' => 'Stall Rental Applications',
        ]);
    }

    public function createApplication(Request $request)
    {
        return view('market.tenant.applications.create', [
            'pageTitle' => 'New Stall Rental Application',
            'stalls' => Stall::query()->where('status', 'AVAILABLE')->orderBy('section')->orderBy('stall_number')->get(),
            'modal' => $request->boolean('modal'),
        ]);
    }

    public function editApplication(Request $request, StallApplication $application)
    {
        $this->authorizePendingApplication($request, $application);

        return view('market.tenant.applications.create', [
            'pageTitle' => 'Edit Stall Rental Application',
            'stalls' => Stall::query()->where('status', 'AVAILABLE')->orderBy('section')->orderBy('stall_number')->get(),
            'application' => $application->load('documents'),
            'modal' => $request->boolean('modal'),
        ]);
    }

    public function stallMap()
    {
        return view('market.portal.stall-map', [
            'pageTitle' => 'Public Market Stall Location',
            'stalls' => Stall::orderBy('section')->orderBy('stall_number')->get()->groupBy('section'),
            'canEdit' => false,
        ]);
    }

    public function storeApplication(Request $request)
    {
        $data = $this->validateApplication($request);

        $documents = $data['documents'] ?? [];
        unset($data['documents']);

        $application = $request->user()->stallApplications()->create([
            ...$data,
            'application_number' => 'APP-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'status' => 'PENDING',
        ]);

        $this->storeApplicationDocuments($application, $documents);

        User::where('usertype', User::ROLE_TREASURER)
            ->where('status', 'ACTIVE')
            ->each(function (User $user) use ($application) {
                MarketNotification::create([
                    'user_id' => $user->id,
                    'type' => 'STALL_APPLICATION',
                    'title' => 'New stall rental application',
                    'message' => "{$application->business_name} submitted {$application->application_number}.",
                    'action_url' => route('treasurer.rentals'),
                ]);
            });

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Your stall rental application was submitted.',
                'application_url' => route('stall-applications.show', $application),
            ]);
        }

        return redirect()->route('tenant.applications')->with('success', 'Your stall rental application was submitted.');
    }

    public function updateApplication(Request $request, StallApplication $application)
    {
        $this->authorizePendingApplication($request, $application);
        $application->load('documents');

        $data = $this->validateApplication($request, $application->documents->count());
        $documents = $data['documents'] ?? [];
        unset($data['documents']);

        $application->update($data);
        $this->storeApplicationDocuments($application, $documents);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Your stall rental application was updated.',
                'application_url' => route('stall-applications.show', $application),
            ]);
        }

        return redirect()->route('tenant.applications')->with('success', 'Your stall rental application was updated.');
    }

    private function validateApplication(Request $request, int $existingDocumentCount = 0): array
    {
        $remainingDocumentSlots = max(0, 5 - $existingDocumentCount);

        return $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_category' => ['required', 'string', 'max:100'],
            'business_address' => ['required', 'string', 'max:255'],
            'preferred_section' => ['required', 'in:FISH,PORK,POULTRY,BEEF,MIXED'],
            'preferred_stall_number' => ['nullable', 'integer', 'min:1'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'civil_status' => ['nullable', 'in:SINGLE,MARRIED,WIDOWED,SEPARATED'],
            'sex' => ['nullable', 'in:MALE,FEMALE'],
            'contact_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'business_owner' => ['required', 'string', 'max:255'],
            'tin_number' => ['required', 'string', 'max:15', 'regex:/^\d{3}-?\d{3}-?\d{3}(?:-?\d{3})?$/'],
            'business_nature' => ['nullable', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'permit_issued_at' => ['nullable', 'date'],
            'other_business' => ['nullable', 'string', 'max:255'],
            'documents' => ['nullable', 'array', 'max:'.$remainingDocumentSlots],
            'documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ]);
    }

    private function storeApplicationDocuments(StallApplication $application, array $documents): void
    {
        foreach ($documents as $document) {
            $application->documents()->create([
                'document_type' => 'APPLICATION_REQUIREMENT',
                'path' => $document->store("stall-applications/{$application->id}", 'public'),
                'original_name' => $document->getClientOriginalName(),
                'mime_type' => $document->getMimeType(),
                'size' => $document->getSize(),
            ]);
        }
    }

    private function authorizePendingApplication(Request $request, StallApplication $application): void
    {
        abort_unless($application->tenant_id === $request->user()->id, 403);
        abort_unless($application->status === 'PENDING', 422, 'Only pending applications can be edited.');
    }

    public function destroyApplication(Request $request, StallApplication $application)
    {
        abort_unless($application->tenant_id === $request->user()->id, 403);

        if ($application->status !== 'PENDING') {
            return back()->withErrors(['application' => 'Only pending applications can be deleted.']);
        }

        $application->load('documents');
        foreach ($application->documents as $document) {
            Storage::disk('public')->delete($document->path);
        }

        $application->documents()->delete();
        $application->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => 'The pending stall application was deleted.']);
        }

        return back()->with('success', 'The pending stall application was deleted.');
    }

    public function payments(Request $request)
    {
        $payments = $request->user()->payments()->with('stallApplication.stall')->latest('due_date')->get();
        $application = $request->user()->stallApplications()->with(['stall', 'documents'])->where('status', 'APPROVED')->latest()->first()
            ?? $request->user()->stallApplications()->with(['stall', 'documents'])->latest()->first();

        return view('market.portal.payments', [
            'pageTitle' => 'Stall Rental Payments',
            'payments' => $payments,
            'application' => $application,
        ]);
    }

    public function submitPaymentReceipt(Request $request, Payment $payment)
    {
        abort_unless($payment->tenant_id === $request->user()->id, 403);

        $data = $request->validate([
            'receipt' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'payment_method' => ['required', 'in:GCASH,BANK,CASH'],
        ]);

        if ($payment->receipt_path) {
            Storage::disk('public')->delete($payment->receipt_path);
        }

        $payment->update([
            'receipt_path' => $data['receipt']->store("payment-receipts/{$payment->id}", 'public'),
            'payment_method' => $data['payment_method'],
            'status' => 'SUBMITTED',
        ]);

        User::where('usertype', User::ROLE_TREASURER)
            ->where('status', 'ACTIVE')
            ->each(fn (User $user) => MarketNotification::create([
                'user_id' => $user->id,
                'type' => 'PAYMENT',
                'title' => 'Payment receipt submitted',
                'message' => "{$payment->reference_number} is ready for verification.",
                'action_url' => route('treasurer.payments'),
            ]));

        return back()->with('success', 'Payment receipt submitted for verification.');
    }

    public function inspections(Request $request)
    {
        return view('market.portal.inspections', [
            'pageTitle' => 'Slaughtered Livestock Inspection Requests',
            'inspections' => $request->user()->inspectionRequests()->latest('scheduled_at')->get(),
            'inspectionSchedule' => \App\Models\LivestockInspection::latest('scheduled_at')->get()->groupBy('livestock_type'),
            'canRequest' => true,
            'canReview' => false,
        ]);
    }

    public function storeInspection(Request $request)
    {
        $data = $this->validateInspection($request);

        $inspection = $request->user()->inspectionRequests()->create([
            ...$data,
            'request_number' => 'INSP-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'status' => 'PENDING',
        ]);

        $this->notifyInspectors($inspection);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Inspection request submitted for review.']);
        }

        return back()->with('success', 'Inspection request submitted for review.');
    }

    public function updateInspection(Request $request, LivestockInspection $inspection)
    {
        $this->authorizePendingInspection($request, $inspection);
        $inspection->update($this->validateInspection($request));

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Inspection request updated successfully.']);
        }

        return back()->with('success', 'Inspection request updated successfully.');
    }

    public function destroyInspection(Request $request, LivestockInspection $inspection)
    {
        $this->authorizePendingInspection($request, $inspection);
        $inspection->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Inspection request deleted successfully.']);
        }

        return back()->with('success', 'Inspection request deleted successfully.');
    }

    private function validateInspection(Request $request): array
    {
        return $request->validate([
            'livestock_type' => ['required', 'in:POULTRY,PORK,BEEF'],
            'owner_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:30'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'animal_count' => ['required', 'integer', 'min:1'],
            'breed' => ['nullable', 'string', 'max:100'],
            'sex' => ['nullable', 'in:MALE,FEMALE,MIXED'],
            'animal_age' => ['nullable', 'string', 'max:100'],
            'live_weight' => ['nullable', 'numeric', 'min:0'],
            'source_location' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function notifyInspectors(LivestockInspection $inspection): void
    {
        User::where('usertype', User::ROLE_INSPECTOR)
            ->where('status', 'ACTIVE')
            ->each(fn (User $user) => MarketNotification::create([
                'user_id' => $user->id,
                'type' => 'INSPECTION',
                'title' => 'New inspection request',
                'message' => "{$inspection->request_number} requests a {$inspection->livestock_type} inspection.",
                'action_url' => route('inspector.inspections'),
            ]));
    }

    private function authorizePendingInspection(Request $request, LivestockInspection $inspection): void
    {
        abort_unless($inspection->tenant_id === $request->user()->id, 403);
        abort_unless($inspection->status === 'PENDING', 422, 'Only pending inspection requests can be changed.');
    }
}
