<?php

namespace App\Http\Controllers;


use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class SituationalReportController extends Controller
{
    public function situationalreport_view()
    {
        return view('situationalreport.views.situationalreport');
    }
}
