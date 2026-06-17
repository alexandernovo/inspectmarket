<?php

namespace App\Http\Controllers;


use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ProgressReportController extends Controller
{
    public function progressreport_view()
    {
        return view('progressreport.views.progressreport');
    }
}
