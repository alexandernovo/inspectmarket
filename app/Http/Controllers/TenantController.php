<?php

namespace App\Http\Controllers;

use App\Models\MarketNotification;
use App\Models\Payment;
use App\Models\Stall;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        return view('market.portal.dashboard', [
            'pageTitle' => 'Tenant Dashboard',
            'stats' => [
                ['label' => 'Applications', 'value' => $user->stallApplications()->count(), 'icon' => 'bi-file-earmark-text'],
                ['label' => 'Approved Stalls', 'value' => $user->stallApplications()->where('status', 'APPROVED')->count(), 'icon' => 'bi-shop'],
                ['label' => 'Pending Payments', 'value' => $user->payments()->where('status', 'PENDING')->count(), 'icon' => 'bi-wallet2'],
                ['label' => 'Inspection Requests', 'value' => $user->inspectionRequests()->count(), 'icon' => 'bi-clipboard2-pulse'],
            ],
            'rows' => $user->stallApplications()->with('stall')->latest()->limit(8)->get(),
            'rowType' => 'applications',
            'chartTitle' => 'Stall rental activity',
        ]);
    }

    public function applications(Request $request)
    {
        return view('market.portal.stalls', [
            'pageTitle' => 'Stall Rental Applications',
            'applications' => $request->user()->stallApplications()->with(['stall', 'documents'])->latest()->get(),
            'stalls' => Stall::query()->where('status', 'AVAILABLE')->orderBy('section')->orderBy('stall_number')->get(),
            'canApply' => true,
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
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_category' => ['required', 'string', 'max:100'],
            'business_address' => ['required', 'string', 'max:255'],
            'preferred_section' => ['required', 'in:FISH,PORK,POULTRY,BEEF,MIXED'],
            'preferred_stall_number' => ['nullable', 'integer', 'min:1'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'civil_status' => ['nullable', 'in:SINGLE,MARRIED,WIDOWED,SEPARATED'],
            'contact_number' => ['required', 'string', 'max:30'],
            'business_owner' => ['required', 'string', 'max:255'],
            'documents' => ['nullable', 'array', 'max:5'],
            'documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $documents = $data['documents'] ?? [];
        unset($data['documents']);

        $application = $request->user()->stallApplications()->create([
            ...$data,
            'application_number' => 'APP-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'status' => 'PENDING',
        ]);

        foreach ($documents as $document) {
            $application->documents()->create([
                'document_type' => 'APPLICATION_REQUIREMENT',
                'path' => $document->store("stall-applications/{$application->id}", 'public'),
                'original_name' => $document->getClientOriginalName(),
                'mime_type' => $document->getMimeType(),
                'size' => $document->getSize(),
            ]);
        }

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

        return back()->with('success', 'Your stall rental application was submitted.');
    }

    public function payments(Request $request)
    {
        return view('market.portal.payments', [
            'pageTitle' => 'Stall Rental Payments',
            'payments' => $request->user()->payments()->with('stallApplication.stall')->latest('due_date')->get(),
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
            'canRequest' => true,
            'canReview' => false,
        ]);
    }

    public function storeInspection(Request $request)
    {
        $data = $request->validate([
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

        $inspection = $request->user()->inspectionRequests()->create([
            ...$data,
            'request_number' => 'INSP-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'status' => 'PENDING',
        ]);

        User::where('usertype', User::ROLE_INSPECTOR)
            ->where('status', 'ACTIVE')
            ->each(fn (User $user) => MarketNotification::create([
                'user_id' => $user->id,
                'type' => 'INSPECTION',
                'title' => 'New inspection request',
                'message' => "{$inspection->request_number} requests a {$inspection->livestock_type} inspection.",
                'action_url' => route('inspector.inspections'),
            ]));

        return back()->with('success', 'Inspection request submitted for review.');
    }
}
