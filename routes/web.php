<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdministratorController;
use App\Http\Controllers\ClerkController;
use App\Http\Controllers\InspectorController;
use App\Http\Controllers\MarketAccountController;
use App\Http\Controllers\MarketChatController;
use App\Http\Controllers\MarketDataTableController;
use App\Http\Controllers\MarketHomeController;
use App\Http\Controllers\MarketNotificationController;
use App\Http\Controllers\MarketRecordController;
use App\Http\Controllers\MarketReportController;
use App\Http\Controllers\PortalAuthController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TreasurerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketHomeController::class, 'index'])->name('home');
Route::get('/roles/{mode}', [MarketHomeController::class, 'roles'])->name('public.roles');
Route::post('/contact', [MarketHomeController::class, 'contact'])->name('contact.store');
Route::get('/public/{screen}', [MarketHomeController::class, 'service'])->name('public.service');
Route::post('/public/inspection-request', [MarketHomeController::class, 'publicInspection'])->name('public.inspection.store');
Route::post('/public/stall-application', [MarketHomeController::class, 'publicStallApplication'])->name('public.stall-application.store');
Route::get('/announcements/{announcement}/attachment', [MarketRecordController::class, 'announcementAttachment'])->name('announcements.attachment');
Route::redirect('/login', '/portal/tenant/login')->name('login');

Route::middleware('guest')->group(function () {
    Route::get('/portal/{role}/login', [PortalAuthController::class, 'create'])->name('portal.login');
    Route::post('/portal/{role}/login', [PortalAuthController::class, 'store'])->name('portal.login.store');

    Route::get('/register/{role}', [AccountController::class, 'register'])->name('account.register');
    Route::post('/register/{role}/code', [AccountController::class, 'sendRegistrationCode'])->name('account.register.code');
    Route::get('/account/verify', [AccountController::class, 'verifyForm'])->name('account.verify.form');
    Route::post('/account/verify', [AccountController::class, 'verify'])->name('account.verify');
    Route::get('/account/password', [AccountController::class, 'passwordForm'])->name('account.password.form');
    Route::post('/account', [AccountController::class, 'createAccount'])->name('account.store');
    Route::get('/forgot-password', [AccountController::class, 'forgot'])->name('account.forgot');
    Route::post('/forgot-password/code', [AccountController::class, 'sendResetCode'])->name('account.reset.code');
    Route::get('/reset-password', [AccountController::class, 'resetForm'])->name('account.reset.form');
    Route::post('/reset-password', [AccountController::class, 'resetPassword'])->name('account.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [PortalAuthController::class, 'redirect'])->name('dashboard');
    Route::post('/logout', [PortalAuthController::class, 'destroy'])->name('auth.logout');

    Route::get('/profile', [MarketAccountController::class, 'profile'])->name('profile');
    Route::put('/profile', [MarketAccountController::class, 'updateProfile'])->name('profile.update');
    Route::get('/notifications', [MarketNotificationController::class, 'index'])->name('notifications.index');
    Route::put('/notifications/read-all', [MarketNotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{notification}', [MarketNotificationController::class, 'read'])->name('notifications.read');
    Route::get('/messages', [MarketChatController::class, 'index'])->name('chat.index');
    Route::post('/messages', [MarketChatController::class, 'store'])->name('chat.store');

    Route::get('/stall-applications/{application}', [MarketRecordController::class, 'application'])->name('stall-applications.show');
    Route::get('/stall-application-documents/{document}', [MarketRecordController::class, 'applicationDocument'])->name('stall-applications.documents');
    Route::get('/inspections/{inspection}', [MarketRecordController::class, 'inspection'])->name('inspections.show');
    Route::get('/payments/{payment}/receipt', [ReportExportController::class, 'paymentReceipt'])->name('payments.receipt');
    Route::get('/inspections/{inspection}/certificate', [ReportExportController::class, 'inspectionCertificate'])->name('inspections.certificate');
    Route::get('/cash-ticket-assignments/{assignment}/slip', [ReportExportController::class, 'assignmentSlip'])->name('assignments.slip');
    Route::get('/cash-ticket-collections/{collection}/report', [ReportExportController::class, 'collectionReport'])->name('collections.report');
    Route::get('/exports/{report}.csv', [ReportExportController::class, 'csv'])->name('reports.csv');
    Route::get('/reports/print', [MarketReportController::class, 'printable'])->name('reports.print');

    Route::prefix('tenant')->name('tenant.')->middleware('market.role:TENANT')->group(function () {
        Route::get('/dashboard', [TenantController::class, 'dashboard'])->name('dashboard');
        Route::get('/stall-map', [TenantController::class, 'stallMap'])->name('stall-map');
        Route::get('/stall-applications', [TenantController::class, 'applications'])->name('applications');
        Route::post('/stall-applications', [TenantController::class, 'storeApplication'])->name('applications.store');
        Route::get('/payments', [TenantController::class, 'payments'])->name('payments');
        Route::post('/payments/{payment}/receipt', [TenantController::class, 'submitPaymentReceipt'])->name('payments.receipt');
        Route::get('/inspection-requests', [TenantController::class, 'inspections'])->name('inspections');
        Route::post('/inspection-requests', [TenantController::class, 'storeInspection'])->name('inspections.store');
        Route::get('/datatable/applications', [MarketDataTableController::class, 'applications'])->name('datatable.applications');
        Route::get('/datatable/payments', [MarketDataTableController::class, 'payments'])->name('datatable.payments');
        Route::get('/datatable/inspections', [MarketDataTableController::class, 'inspections'])->name('datatable.inspections');
    });

    Route::prefix('inspector')->name('inspector.')->middleware('market.role:INSPECTOR')->group(function () {
        Route::get('/dashboard', [InspectorController::class, 'dashboard'])->name('dashboard');
        Route::get('/inspections', [InspectorController::class, 'inspections'])->name('inspections');
        Route::get('/reports', [MarketReportController::class, 'index'])->name('reports');
        Route::put('/inspections/{inspection}', [InspectorController::class, 'update'])->name('inspections.update');
        Route::get('/datatable/inspections', [MarketDataTableController::class, 'inspections'])->name('datatable.inspections');
    });

    Route::prefix('clerk')->name('clerk.')->middleware('market.role:CLERK')->group(function () {
        Route::get('/dashboard', [ClerkController::class, 'dashboard'])->name('dashboard');
        Route::get('/cash-ticket-collections', [ClerkController::class, 'collections'])->name('collections');
        Route::post('/cash-ticket-collections', [ClerkController::class, 'storeCollection'])->name('collections.store');
        Route::get('/stall-rentals', [ClerkController::class, 'rentals'])->name('rentals');
        Route::get('/reports', [MarketReportController::class, 'index'])->name('reports');
        Route::get('/datatable/collections', [MarketDataTableController::class, 'collections'])->name('datatable.collections');
        Route::get('/datatable/assignments', [MarketDataTableController::class, 'assignments'])->name('datatable.assignments');
        Route::get('/datatable/applications', [MarketDataTableController::class, 'applications'])->name('datatable.applications');
    });

    Route::prefix('treasurer')->name('treasurer.')->middleware('market.role:TREASURER')->group(function () {
        Route::get('/dashboard', [TreasurerController::class, 'dashboard'])->name('dashboard');
        Route::get('/announcements', [TreasurerController::class, 'announcements'])->name('announcements');
        Route::post('/announcements', [TreasurerController::class, 'storeAnnouncement'])->name('announcements.store');
        Route::get('/stall-rentals', [TreasurerController::class, 'rentals'])->name('rentals');
        Route::get('/stall-map', [TreasurerController::class, 'stallMap'])->name('stall-map');
        Route::put('/stall-map/{stall}', [TreasurerController::class, 'updateStall'])->name('stall-map.update');
        Route::put('/stall-rentals/{application}', [TreasurerController::class, 'reviewApplication'])->name('rentals.review');
        Route::get('/payments', [TreasurerController::class, 'payments'])->name('payments');
        Route::post('/payments', [TreasurerController::class, 'recordPayment'])->name('payments.store');
        Route::put('/payments/{payment}/verify', [TreasurerController::class, 'verifyPayment'])->name('payments.verify');
        Route::get('/cash-ticket-assignments', [TreasurerController::class, 'assignments'])->name('assignments');
        Route::post('/cash-ticket-assignments', [TreasurerController::class, 'storeAssignment'])->name('assignments.store');
        Route::get('/collectors', [TreasurerController::class, 'collectors'])->name('collectors');
        Route::post('/collectors', [TreasurerController::class, 'storeCollector'])->name('collectors.store');
        Route::put('/collectors/{collector}', [TreasurerController::class, 'updateCollector'])->name('collectors.update');
        Route::get('/reports', [MarketReportController::class, 'index'])->name('reports');
        Route::get('/datatable/applications', [MarketDataTableController::class, 'applications'])->name('datatable.applications');
        Route::get('/datatable/payments', [MarketDataTableController::class, 'payments'])->name('datatable.payments');
        Route::get('/datatable/assignments', [MarketDataTableController::class, 'assignments'])->name('datatable.assignments');
    });

    Route::prefix('administrator')->name('administrator.')->middleware('market.role:ADMINISTRATOR')->group(function () {
        Route::get('/dashboard', [AdministratorController::class, 'dashboard'])->name('dashboard');
        Route::get('/members/{role}', [AdministratorController::class, 'members'])->name('members');
        Route::get('/records/{role}/{area?}', [AdministratorController::class, 'roleRecords'])->name('records');
        Route::post('/members/{role}', [AdministratorController::class, 'storeMember'])->name('members.store');
        Route::put('/members/{user}', [AdministratorController::class, 'updateMember'])->name('members.update');
        Route::get('/reports', [MarketReportController::class, 'index'])->name('reports');
        Route::get('/stall-map', [AdministratorController::class, 'stallMap'])->name('stall-map');
        Route::put('/stall-map/{stall}', [AdministratorController::class, 'updateStall'])->name('stall-map.update');
        Route::get('/contacts', [AdministratorController::class, 'contacts'])->name('contacts');
        Route::put('/contacts/{contact}', [AdministratorController::class, 'readContact'])->name('contacts.read');
        Route::get('/settings', [AdministratorController::class, 'settings'])->name('settings');
        Route::put('/settings', [AdministratorController::class, 'updateSettings'])->name('settings.update');
        Route::get('/datatable/members/{role}', [MarketDataTableController::class, 'members'])->name('datatable.members');
        Route::get('/datatable/applications', [MarketDataTableController::class, 'applications'])->name('datatable.applications');
        Route::get('/datatable/payments', [MarketDataTableController::class, 'payments'])->name('datatable.payments');
        Route::get('/datatable/inspections', [MarketDataTableController::class, 'inspections'])->name('datatable.inspections');
        Route::get('/datatable/collections', [MarketDataTableController::class, 'collections'])->name('datatable.collections');
    });
});
