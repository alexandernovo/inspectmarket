<?php

namespace App\Http\Controllers;

use App\Mail\SendUpdateMail;
use App\Models\Record;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Exception;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function user_view()
    {
        return view('user.views.user');
    }

    public function save_new_user(Request $request)
    {
        try {
            DB::beginTransaction();
            if ($request->post('username')) {
                $find = User::where('username', $request->post('username'))->first();
                if ($find) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => "This username is already taken."
                    ]);
                }
            }
            if ($request->post('email')) {
                $find = User::where('email', $request->post('email'))->first();
                if ($find) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => "This email is already taken."
                    ]);
                }
            }
            $all = $request->all();
            $user_id = $all['id'];
            unset($all['id']);

            if ($user_id == 0) {
                if (empty($all['status'])) {
                    $all['status'] = "ACTIVE";
                }
                $all['usertype'] = "STAFF";
                $all['password'] = Hash::make($all['password']);
                User::create($all);
            } else {
                if (empty($all['password'])) {
                    unset($all['password']);
                } else {
                    $all['password'] = Hash::make($all['password']);
                }

                User::where("id", $user_id)->update($all);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Staff saved successfully"
            ]);
        } catch (Exception $ex) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function getusers(Request $request)
    {
        $length = $request->input('length');
        $start = $request->input('start');
        $searchValue = $request->input('search.value');

        $query = User::select(
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
            ->where("users.usertype", "STAFF");

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('users.address', 'like', "%{$searchValue}%")
                    ->orWhere('users.firstname', 'like', "%{$searchValue}%")
                    ->orWhere('users.middlename', 'like', "%{$searchValue}%")
                    ->orWhere('users.lastname', 'like', "%{$searchValue}%")
                    ->orWhereRaw("(users.firstname + ' ' + users.middlename + ' ' + users.lastname) LIKE ?", ["%{$searchValue}%"])
                    ->orWhereRaw("(users.firstname + ' ' + users.lastname) LIKE ?", ["%{$searchValue}%"])
                    ->orWhereRaw("(users.lastname + ', ' + users.firstname) LIKE ?", ["%{$searchValue}%"])
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

        $totalData = $query->count();

        $data = $query
            ->orderBy("created_at", "DESC")
            ->offset($start)
            ->limit($length)
            ->get();

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalData,
            "data" => $data
        ]);
    }

    public function deletestaff(Request $request)
    {
        $id = $request->id;
        User::where('id', $id)->delete();
        return response()->json([
            'status' => 'success'
        ]);
    }

    public function activatedeactivate(Request $request)
    {
        $id = $request->id;
        $status = $request->status == "ACTIVE" ? "INACTIVE" : "ACTIVE";

        User::where('id', $id)->update(['status' => $status]);
        $user = User::where('id', $id)->first();
        $message = "Dear {$user['firstname']} {$user['lastname']},\n Your Account in Tibiao MDRRMO Portal has been "
            . ($status == "ACTIVE" ? "Activated" : "Deactivated") . ".";

        Mail::to([$user['email']])->send(new SendUpdateMail($message));

        return response()->json([
            'status' => 'success'
        ]);
    }
}
