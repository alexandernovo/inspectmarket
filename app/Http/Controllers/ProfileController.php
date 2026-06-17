<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function profile_view()
    {
        return view('profile.views.profile');
    }

    public function updateProfile(Request $request)
    {
        try {
            DB::beginTransaction();
            $all = $request->all();
            $user = Auth::user();

            if (empty($all['password'])) {
                unset($all['password']);
            } else {
                $all['password'] = Hash::make($all['password']);
            }

            User::where('id', $user->id)->update($all);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => "Profile Updated Successfully!",
            ]);
        } catch (Exception $ex) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function profileUpload(Request $request)
    {
        if ($request->hasFile('profile') && $request->file('profile')->isValid()) {
            $filename = $this->moveFile($request->file('profile'), "profile");
            $user = Auth::user();
            $user->profile = $filename;
            $user->save();

            Auth::setUser($user->fresh());
        }


        return response()->json([
            'status' => 'success',
            'message' => "Profile Picture Updated Successfully!",
        ]);
    }

    public function backgroundUpload(Request $request)
    {
        if ($request->hasFile('background') && $request->file('background')->isValid()) {
            $filename = $this->moveFile($request->file('background'), "profile");
            $user = Auth::user();
            $user->background = $filename;
            $user->save();

            Auth::setUser($user->fresh());
        }


        return response()->json([
            'status' => 'success',
            'message' => "Cover Photo Updated Successfully!",
        ]);
    }

    public function deleteCover(Request $request)
    {
        $user = Auth::user();
        $user->background = null;
        $user->save();
        Auth::setUser($user->fresh());

        return response()->json([
            'status' => 'success',
            'message' => "Cover Photo Deleted Successfully!",
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
}
