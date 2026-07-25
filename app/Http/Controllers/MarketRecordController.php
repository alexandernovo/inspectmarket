<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\LivestockInspection;
use App\Models\StallApplication;
use App\Models\StallApplicationDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarketRecordController extends Controller
{
    public function application(Request $request, StallApplication $application)
    {
        $this->authorizeApplication($request, $application);

        return view('market.portal.application-detail', [
            'pageTitle' => 'Stall Application Details',
            'application' => $application->load(['tenant', 'stall', 'reviewer', 'documents', 'payments']),
        ]);
    }

    public function applicationDocument(Request $request, StallApplicationDocument $document)
    {
        $this->authorizeApplication($request, $document->application);

        abort_unless(Storage::disk('public')->exists($document->path), 404);

        return Storage::disk('public')->download($document->path, $document->original_name);
    }

    public function applicationDocumentPreview(Request $request, StallApplicationDocument $document)
    {
        $this->authorizeApplication($request, $document->application);

        abort_unless(Storage::disk('public')->exists($document->path), 404);

        return Storage::disk('public')->response($document->path, $document->original_name, [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.$document->original_name.'"',
        ]);
    }

    public function inspection(Request $request, LivestockInspection $inspection)
    {
        abort_if(
            $request->user()->isRole(User::ROLE_TENANT) && $inspection->tenant_id !== $request->user()->id,
            403
        );

        return view('market.portal.inspection-detail', [
            'pageTitle' => 'Livestock Inspection Details',
            'inspection' => $inspection->load(['tenant', 'inspector']),
        ]);
    }

    public function announcementAttachment(Announcement $announcement)
    {
        abort_unless($announcement->attachment_path && Storage::disk('public')->exists($announcement->attachment_path), 404);

        return Storage::disk('public')->download($announcement->attachment_path, $announcement->attachment_name);
    }

    private function authorizeApplication(Request $request, StallApplication $application): void
    {
        abort_if(
            $request->user()->isRole(User::ROLE_TENANT) && $application->tenant_id !== $request->user()->id,
            403
        );
    }
}
