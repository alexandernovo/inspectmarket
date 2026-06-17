<?php

namespace App\Http\Controllers;


use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function report_view()
    {
        return view('report.views.report');
    }

    public function incidentreportPrint(Request $request)
    {
        $monthyear = $request->query('monthyear');
        $result = $this->getPrintQuery($monthyear, "INCIDENTREPORT");

        $data = [
            'data' => $result
        ];

        return view('report.views.incidentreportPrint', $data);
    }

    public function situationalreportPrint(Request $request)
    {
        $monthyear = $request->query('monthyear');
        $result = $this->getPrintQuery($monthyear, "SITUATIONALREPORT");

        $data = [
            'data' => $result
        ];

        return view('report.views.situationalreportPrint', $data);
    }

    public function progressreportPrint(Request $request)
    {
        $monthyear = $request->query('monthyear');
        $result = $this->getPrintQuery($monthyear, "PROGRESSREPORT");

        $data = [
            'data' => $result
        ];

        return view('report.views.progressreportPrint', $data);
    }

    public function inventoryreportPrint(Request $request)
    {
        $monthyear = $request->query('monthyear');
        $result = $this->getPrintQuery($monthyear, "INVENTORYREPORT");

        $data = [
            'data' => $result
        ];

        return view('report.views.inventoryreportPrint', $data);
    }

    private function getPrintQuery($monthyear, $type)
    {
        $query = DB::table('records')
            ->leftJoin('users', 'records.staff_id', '=', 'users.id')
            ->select(
                'records.*',
                'users.*',
                DB::raw("
                    CONCAT(
                        users.firstname, ' ',
                        CASE 
                            WHEN users.middlename IS NOT NULL AND users.middlename <> '' 
                            THEN LEFT(users.middlename, 1) + '. ' 
                            ELSE '' 
                        END,
                        users.lastname
                    ) AS fullname
                ")
            )
            ->where("records.typeOfRecord", $type);

        if (!empty($monthyear)) {

            $query->whereRaw("FORMAT(records.created_at, 'yyyy-MM') = ?", [$monthyear]);
        }

        $result = $query->get();

        return $result;
    }
}
