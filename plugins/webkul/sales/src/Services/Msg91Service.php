<?php

namespace Webkul\Sale\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Msg91Service
{
    public function send(string $mobile, string $message): bool
    {
        if (empty($mobile) || empty(config('services.msg91.auth_key'))) {
            return false;
        }

        $response = Http::get('https://api.msg91.com/api/sendhttp.php', [
            'authkey' => config('services.msg91.auth_key'),
            'mobiles' => $mobile,
            'message' => $message,
            'sender'  => config('services.msg91.sender_id'),
            'route'   => config('services.msg91.route'),
        ]);

        if (! $response->successful()) {
            Log::error('MSG91 SMS failed', [
                'mobile' => substr((string) $mobile, 0, 3).str_repeat('*', max(0, strlen((string) $mobile) - 5)).substr((string) $mobile, -2),
                'status' => $response->status(),
            ]);

            return false;
        }

        return true;
    }
}
