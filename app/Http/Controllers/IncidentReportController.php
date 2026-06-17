<?php

namespace App\Http\Controllers;


use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class IncidentReportController extends Controller
{
    public function incidentreport_view()
    {
        return view('incidentreport.views.incidentreport');
    }
}
