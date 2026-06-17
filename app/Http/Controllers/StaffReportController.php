<?php

namespace App\Http\Controllers;


use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StaffReportController extends Controller
{
    public function staffreport_view()
    {
        return view('staffreport.views.staffreport');
    }
    public function archive_view()
    {
        return view('staffreport.views.archive');
    }

    public function submitreportdashboard()
    {
        $incidentReport = Record::where('typeOfRecord', "INCIDENTREPORT");
        $situationReport = Record::where('typeOfRecord', "SITUATIONALREPORT");
        $progressReport = Record::where('typeOfRecord', "PROGRESSREPORT");

        $userData = Auth::user();

        if ($userData->usertype == "STAFF") {
            $incidentReport->where('staff_id', $userData->id);
            $situationReport->where('staff_id', $userData->id);
            $progressReport->where('staff_id', $userData->id);
        }

        $incidentReportCount = $incidentReport->count();
        $situationReportCount = $situationReport->count();
        $progressReportCount = $progressReport->count();

        $data = [
            "incidentReportCount" => $incidentReportCount,
            "situationReportCount" => $situationReportCount,
            "progressReportCount" => $progressReportCount,
        ];
        return view('staffreport.views.submitreportdashboard', $data);
    }

    public function submitreportdashboardadmin()
    {
        $incidentReport = Record::where('typeOfRecord', "INCIDENTREPORT");
        $situationReport = Record::where('typeOfRecord', "SITUATIONALREPORT");
        $progressReport = Record::where('typeOfRecord', "PROGRESSREPORT");

        $userData = Auth::user();

        if ($userData->usertype == "STAFF") {
            $incidentReport->where('staff_id', $userData->id);
            $situationReport->where('staff_id', $userData->id);
            $progressReport->where('staff_id', $userData->id);
        }

        $incidentReportCount = $incidentReport->count();
        $situationReportCount = $situationReport->count();
        $progressReportCount = $progressReport->count();

        $data = [
            "incidentReportCount" => $incidentReportCount,
            "situationReportCount" => $situationReportCount,
            "progressReportCount" => $progressReportCount,
        ];
        return view('staffreport.views.submitreportdashboardadmin', $data);
    }

    public function incidentreport_staff()
    {
        return view('staffreport.views.incidentreport');
    }

    public function situationalreport_staff()
    {
        return view('staffreport.views.situationalreport');
    }

    public function progressreport_staff()
    {
        return view('staffreport.views.progressreport');
    }

    public function inventoryreport_staff()
    {
        return view('staffreport.views.inventoryreport');
    }

    public function save_new_staffreport(Request $request)
    {
        try {
            DB::beginTransaction();
            $all = $request->all();
            $record_id = $all['record_id'];
            unset($all['record_id']);

            if (!empty($all['datetimeoccurence'])) {
                $all['datetimeoccurence'] = date('Y-m-d h:i:s', strtotime($all['datetimeoccurence']));
            }

            if (!empty($all['dateacquired'])) {
                $all['dateacquired'] = date('Y-m-d', strtotime($all['dateacquired']));
            }

            if ($request->hasFile('filesubmitted') && $request->file('filesubmitted')->isValid()) {
                $filename = $this->moveFile($request->file('filesubmitted'), "filesubmitted");
                $all['filesubmitted'] = $filename;
            }

            if ($record_id == 0) {
                $all['status'] = "ACTIVE";
                $all['process_status'] = "Pending";
                Record::create($all);
            } else {
                Record::where("record_id", $record_id)->update($all);
            }

            DB::commit();

            $user = Auth::user();
            $message = $user->usertype == "ADMIN" ? "Report Saved Successfully" : "Report Saved Successfully, Please view your archive.";

            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (Exception $ex) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function getstaffreports(Request $request)
    {
        $length = $request->input('length');
        $start = $request->input('start');
        $searchValue = $request->input('search.value');
        $typeOfRecord = $request->input('typeOfRecord');
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');

        // Load fillable fields dynamically from model
        $record = new \App\Models\Record();
        $recordFields = $record->getFillable();

        $query = DB::table('records')
            ->leftJoin('users', 'records.staff_id', '=', 'users.id')
            ->select(
                'users.*',
                'records.*',
                DB::raw("
                CONCAT(
                    users.firstname, ' ',
                    CASE WHEN users.middlename IS NOT NULL AND users.middlename <> '' 
                        THEN LEFT(users.middlename, 1) + '. ' ELSE '' END,
                    users.lastname
                ) AS fullname
            ")
            );

        // 🔍 SEARCH QUERY
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('records.staff_id', 'like', "%{$searchValue}%")
                    ->orWhere('records.typeincident', 'like', "%{$searchValue}%")
                    ->orWhere('records.datetimeoccurence', 'like', "%{$searchValue}%")
                    ->orWhere('records.barangay', 'like', "%{$searchValue}%")
                    ->orWhere('records.specificlocation', 'like', "%{$searchValue}%")
                    ->orWhere('records.detaileddesc', 'like', "%{$searchValue}%")
                    ->orWhere('records.involvedinjured', 'like', "%{$searchValue}%")
                    ->orWhere('records.involveddead', 'like', "%{$searchValue}%")
                    ->orWhere('records.filesubmitted', 'like', "%{$searchValue}%")
                    ->orWhere('records.status', 'like', "%{$searchValue}%")
                    ->orWhere('records.affectedfamilies', 'like', "%{$searchValue}%")
                    ->orWhere('records.individuals', 'like', "%{$searchValue}%")
                    ->orWhere('records.evacuationfamilies', 'like', "%{$searchValue}%")
                    ->orWhere('records.evacuationindividuals', 'like', "%{$searchValue}%")
                    ->orWhere('records.remarks', 'like', "%{$searchValue}%")
                    ->orWhere('records.clearingoperations', 'like', "%{$searchValue}%")
                    ->orWhere('records.quantity', 'like', "%{$searchValue}%")
                    ->orWhere('records.unit', 'like', "%{$searchValue}%")
                    ->orWhere('records.description', 'like', "%{$searchValue}%")
                    ->orWhere('records.propertyno', 'like', "%{$searchValue}%")
                    ->orWhere('records.dateacquired', 'like', "%{$searchValue}%")
                    ->orWhere('records.amount', 'like', "%{$searchValue}%")
                    ->orWhere('records.typeOfRecord', 'like', "%{$searchValue}%")
                    ->orWhere('records.cause', 'like', "%{$searchValue}%")
                    // users table
                    ->orWhere('users.firstname', 'like', "%{$searchValue}%")
                    ->orWhere('users.middlename', 'like', "%{$searchValue}%")
                    ->orWhere('users.lastname', 'like', "%{$searchValue}%")
                    ->orWhere('users.username', 'like', "%{$searchValue}%")
                    ->orWhere('users.designation', 'like', "%{$searchValue}%")
                    ->orWhere('users.email', 'like', "%{$searchValue}%")
                    ->orWhere('users.address', 'like', "%{$searchValue}%")
                    ->orWhere('users.phone_num', 'like', "%{$searchValue}%")
                    ->orWhereRaw("(users.firstname + ' ' + users.lastname) LIKE ?", ["%{$searchValue}%"])
                    ->orWhereRaw("(users.lastname + ', ' + users.firstname) LIKE ?", ["%{$searchValue}%"]);
            });
        }


        // Filter type
        if (!empty($typeOfRecord)) {
            $query->where("records.typeOfRecord", $typeOfRecord);
        }

        // Filter date
        if (!empty($dateFrom) && !empty($dateTo)) {
            $dateFrom = date("Y-m-d", strtotime($dateFrom));
            $dateTo = date("Y-m-d", strtotime($dateTo));
            $query->whereBetween(DB::raw("CAST(records.created_at AS DATE)"), [$dateFrom, $dateTo]);
        }

        // Restrict STAFF
        $userData = Auth::user();
        if ($userData->usertype == "STAFF") {
            $query->where('staff_id', $userData->id);
        }

        $totalData = $query->count();

        $data = $query
            ->offset($start)
            ->limit($length)
            ->orderBy('records.created_at', 'DESC')
            ->get();

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalData,
            "data" => $data
        ]);
    }


    public function deleteRecord(Request $request)
    {
        $record_id = $request->record_id;
        Record::where('record_id', $record_id)->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }

    private function moveFile($file, $paths)
    {
        $newFileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $path = $file->move(public_path($paths), $newFileName);

        if ($path) {
            return $paths . '/' . $newFileName;
        }

        return false;
    }

    public function sidebarCounts()
    {
        $incidentReport = Record::where('typeOfRecord', "INCIDENTREPORT")->where('status', "ACTIVE")->count();
        $situationReport = Record::where('typeOfRecord', "SITUATIONALREPORT")->where('status', "ACTIVE")->count();
        $progressReport = Record::where('typeOfRecord', "PROGRESSREPORT")->where('status', "ACTIVE")->count();

        return response()->json([
            'status' => 'success',
            'incidentReport' => $incidentReport,
            'situationReport' => $situationReport,
            'progressReport' => $progressReport,
        ]);
    }

    public function updateCountsActive(Request $request)
    {
        $type = $request->type;
        Record::where('typeOfRecord', $type)->where('status', "ACTIVE")->update(["status" => "READ"]);
        return response()->json([
            'status' => 'success',
        ]);
    }

    public function submitRemarks(Request $request)
    {
        $record_id = $request->record_id;
        $remarksAdmin = $request->remarksAdmin;

        Record::where('record_id', $record_id)->update(['remarksAdmin' => $remarksAdmin]);
        
        return response()->json([
            'status' => 'success',
        ]);
    }
}
