<?php

namespace App\Http\Controllers;

use App\Services\OtpService;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    protected OtpService $otp;

    public function __construct(OtpService $otp)
    {
        $this->otp = $otp;
    }

    public function send(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $result = $this->otp->generate($request->phone);

        return response()->json($result);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'otp' => 'required|string|size:6',
        ]);

        $verified = $this->otp->verify($request->phone, $request->otp);

        if ($verified) {
            return response()->json(['verified' => true, 'message' => 'Phone verified successfully']);
        }

        return response()->json(['verified' => false, 'message' => 'Invalid or expired OTP'], 422);
    }
}
