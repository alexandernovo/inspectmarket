<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SMSMobileAPI\SMSMobileAPI;

class SMSController extends Controller
{
    public function sendSms()
    {
        $smsAPI = new SMSMobileAPI(
            env('SMS_MOBILE_API_KEY')
        );

        $response = $smsAPI->sendMessage(
            '639150218089',
            'Hello from Laravel!',
            sendWA: false,
            sendSMS: true
        );

        return response()->json($response);
    }
}
