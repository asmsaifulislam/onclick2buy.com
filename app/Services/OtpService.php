<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class OtpService
{
    public function generate(string $phone): array
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(5);

        Session::put("otp.{$phone}", [
            'code' => $otp,
            'expires_at' => $expiresAt->timestamp,
            'verified' => false,
        ]);

        Log::info("OTP for {$phone}: {$otp}");

        return [
            'sent' => true,
            'message' => 'OTP sent to ' . substr_replace($phone, '****', -4),
            'otp' => $otp,
        ];
    }

    public function verify(string $phone, string $otp): bool
    {
        $data = Session::get("otp.{$phone}");

        if (!$data) {
            return false;
        }

        if (now()->timestamp > ($data['expires_at'] ?? 0)) {
            Session::forget("otp.{$phone}");
            return false;
        }

        if (($data['code'] ?? '') !== $otp) {
            return false;
        }

        Session::put("otp.{$phone}.verified", true);

        return true;
    }

    public function isVerified(string $phone): bool
    {
        return (bool) (Session::get("otp.{$phone}.verified") ?? false);
    }

    public function clear(string $phone): void
    {
        Session::forget("otp.{$phone}");
    }
}
