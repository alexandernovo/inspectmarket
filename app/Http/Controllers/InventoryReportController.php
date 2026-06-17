<?php

namespace App\Http\Controllers;


use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryReportController extends Controller
{
    public function inventoryreport_view()
    {
        return view('inventoryreport.views.inventoryreport');
    }
}
