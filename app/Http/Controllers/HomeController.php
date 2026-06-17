<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMessageMail;


class HomeController extends Controller
{
    public function home()
    {
        return view('home.views.home');
    }

    public function login()
    {
        return view('home.views.login');
    }

    public function signup()
    {
        return view('home.views.signup');
    }

    public function contact()
    {
        return view('home.views.contact');
    }

    public function contact_message(Request $request)
    {
        $message =
            "New Contact Form Submission\n\n" .
            "Name: {$request->name}\n" .
            "Address: {$request->address}\n" .
            "Contact: {$request->contact}\n" .
            "Message:\n{$request->message}";

        Mail::to(env('MAIL_USERNAME'))->send(new SendMessageMail($message));

        return response()->json([
            'status' => 'success'
        ]);
    }
}
