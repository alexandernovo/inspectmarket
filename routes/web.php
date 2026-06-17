<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IncidentReportController;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressReportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SituationalReportController;
use App\Http\Controllers\StaffReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WasteCollectController;
use App\Http\Controllers\ForgotPasswordController;

Route::get('/', [HomeController::class, 'home'])->name('home');
Route::get('/login', [HomeController::class, 'login'])->name('login');
Route::get('/signup', [HomeController::class, 'signup'])->name('signup');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/contact_message', [HomeController::class, 'contact_message'])->name('contact_message');
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
Route::post('/user/save_new_user', [UserController::class, 'save_new_user'])->name('save_new_user');
Route::get('/forgot-password', [ForgotPasswordController::class, 'viewForgotPassword'])->name('forgot.password');
Route::post('/forgot-password/send', [ForgotPasswordController::class, 'sendForgotPassword'])->name('forgot.password.send');
Route::post('/forgot-password/verify', [ForgotPasswordController::class, 'verifyCode'])->name('forgot.password.verify');
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('forgot.password.reset');


Route::middleware(["userchecker"])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard_view'])->name('dashboard');
    Route::post('/dashboard/getIncidentReport', [DashboardController::class, 'getIncidentReport'])->name('dashboard.getIncidentReport');
    //waste collection
    Route::get('/wastecollect/view', [WasteCollectController::class, 'wastecollect_view'])->name('wastecollect_view');
    Route::post('/wastecollect/save_new_wastecollect', [WasteCollectController::class, 'save_new_wastecollect'])->name('save_new_wastecollect');
    Route::post('/wastecollect/getwastecollects', [WasteCollectController::class, 'getwastecollects'])->name('getwastecollect');
    Route::post('/wastecollect/deletewastecollect', [WasteCollectController::class, 'deletewastecollect'])->name('deletewastecollect');
    Route::post('/admin/sidebarCounts', [StaffReportController::class, 'sidebarCounts'])->name('sidebarCounts');
    Route::post('/admin/updateCountsActive', [StaffReportController::class, 'updateCountsActive'])->name('updateCountsActive');


    //incident report
    Route::get('/incidentreport/view', [IncidentReportController::class, 'incidentreport_view'])->name('incidentreport_view');

    //situational report
    Route::get('/situationalreport/view', [SituationalReportController::class, 'situationalreport_view'])->name('situationalreport_view');

    //progress report
    Route::get('/progressreport/view', [ProgressReportController::class, 'progressreport_view'])->name('progressreport_view');

    //inventory report
    Route::get('/inventoryreport/view', [InventoryReportController::class, 'inventoryreport_view'])->name('inventoryreport_view');

    //staff report
    Route::get('/staffreport/view', [StaffReportController::class, 'staffreport_view'])->name('staffreport_view');
    Route::get('/staffreport/archive', [StaffReportController::class, 'archive_view'])->name('archive_view');
    Route::get('/staffreport/submitreportdashboard', [StaffReportController::class, 'submitreportdashboard'])->name('submitreportdashboard');
    Route::get('/staffreport/submitreportdashboardadmin', [StaffReportController::class, 'submitreportdashboardadmin'])->name('submitreportdashboardadmin');
    Route::get('/staffreport/incidentreport_staff', [StaffReportController::class, 'incidentreport_staff'])->name('incidentreport_staff');
    Route::get('/staffreport/situationalreport_staff', [StaffReportController::class, 'situationalreport_staff'])->name('situationalreport_staff');
    Route::get('/staffreport/progressreport_staff', [StaffReportController::class, 'progressreport_staff'])->name('progressreport_staff');
    Route::get('/staffreport/inventoryreport_staff', [StaffReportController::class, 'inventoryreport_staff'])->name('inventoryreport_staff');
    Route::post('/staffreport/getstaffreports', [StaffReportController::class, 'getstaffreports'])->name('getstaffreports');
    Route::post('/staffreport/deleteRecord', [StaffReportController::class, 'deleteRecord'])->name('deleteRecord');
    Route::post('/staffreport/save_new_staffreport', [StaffReportController::class, 'save_new_staffreport'])->name('save_new_staffreport');
    Route::post('/staffreport/submitRemarks', [StaffReportController::class, 'submitRemarks'])->name('submitRemarks');

    //report
    Route::get('/report/view', [ReportController::class, 'report_view'])->name('report_view');
    Route::get('/report/incidentreportPrint', [ReportController::class, 'incidentreportPrint'])->name('incidentreportPrint');
    Route::get('/report/situationalreportPrint', [ReportController::class, 'situationalreportPrint'])->name('situationalreportPrint');
    Route::get('/report/progressreportPrint', [ReportController::class, 'progressreportPrint'])->name('progressreportPrint');
    Route::get('/report/inventoryreportPrint', [ReportController::class, 'inventoryreportPrint'])->name('inventoryreportPrint');

    //user
    Route::get('/user/view', [UserController::class, 'user_view'])->name('user_view');
    Route::post('/user/getusers', [UserController::class, 'getusers'])->name('getuser');
    Route::post('/user/activatedeactivate', [UserController::class, 'activatedeactivate'])->name('activatedeactivate');
    Route::post('/user/deletestaff', [UserController::class, 'deletestaff'])->name('deletestaff');

    //profile
    Route::get('/profile/view', [ProfileController::class, 'profile_view'])->name('profile_view');
    Route::post('/profile/updateProfile', [ProfileController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/profile/profileUpload', [ProfileController::class, 'profileUpload'])->name('profileUpload');
    Route::post('/profile/backgroundUpload', [ProfileController::class, 'backgroundUpload'])->name('backgroundUpload');
    Route::post('/profile/deleteCover', [ProfileController::class, 'deleteCover'])->name('deleteCover');

    //dashboard
    Route::post('/dashboard/getreport', [DashboardController::class, 'getreport'])->name('getreport');
});
