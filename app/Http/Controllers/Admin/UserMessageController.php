<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class UserMessageController extends Controller
{
    /**
     * Send message to one, custom list, or all users.
     * POST /admin/users/message
     */
    public function send(Request $request)
    {
        $request->validate([
            'subject'    => 'required|string|max:255',
            'body'       => 'required|string',
            'channel'    => 'required|in:email,sms,both',
            'recipients' => 'required|in:all,custom',
            'user_ids'   => 'required_if:recipients,custom|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $sendToAll = $request->recipients === 'all';

        // Resolve recipient users
        if ($sendToAll) {
            $users = User::select('id','name','email','mobile')->get();
        } else {
            $users = User::select('id','name','email','mobile')
                ->whereIn('id', $request->user_ids)
                ->get();
        }

        $totalSent   = 0;
        $totalFailed = 0;
        $channel     = $request->channel;

        foreach ($users as $user) {
            // ── Email ─────────────────────────────────────────────────
            if (in_array($channel, ['email', 'both']) && $user->email) {
                try {
                    Mail::send([], [], function (Message $msg) use ($user, $request) {
                        $msg->to($user->email, $user->name)
                            ->subject($request->subject)
                            ->html(
                                '<div style="font-family:Inter,sans-serif;max-width:600px;margin:auto;padding:24px;background:#fff;border-radius:12px;">'
                                . '<h2 style="color:#1a9e4a;margin-bottom:16px;">' . e($request->subject) . '</h2>'
                                . '<div style="font-size:15px;line-height:1.8;color:#374151;">' . nl2br(e($request->body)) . '</div>'
                                . '<hr style="margin:24px 0;border-color:#e2e8f0;">'
                                . '<p style="font-size:12px;color:#94a3b8;">Doel e-Services | এই ইমেইলের জবাব দেওয়ার প্রয়োজন নেই।</p>'
                                . '</div>'
                            );
                    });
                    $totalSent++;
                } catch (\Exception $e) {
                    $totalFailed++;
                }
            }

            // ── SMS via SSL Wireless / Infobip / Twilio ───────────────
            // Set SMS_PROVIDER, SMS_API_KEY, SMS_SENDER_ID in .env
            if (in_array($channel, ['sms', 'both']) && $user->mobile) {
                try {
                    $smsSent = $this->sendSms($user->mobile, $request->body);
                    $smsSent ? $totalSent++ : $totalFailed++;
                } catch (\Exception $smsEx) {
                    \Log::error('SMS send failed for ' . $user->mobile . ': ' . $smsEx->getMessage());
                    $totalFailed++;
                }
            }
        }

        // Persist log
        UserMessage::create([
            'subject'          => $request->subject,
            'body'             => $request->body,
            'channel'          => $channel,
            'recipient_user_ids' => $sendToAll ? null : $request->user_ids,
            'sent_to_all'      => $sendToAll,
            'sent_by'          => auth('admin')->id(),
            'total_sent'       => $totalSent,
            'total_failed'     => $totalFailed,
            'notes'            => $request->input('notes'),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Message sent to {$totalSent} recipient(s).",
            'sent'    => $totalSent,
            'failed'  => $totalFailed,
        ]);
    }

    /**
     * Get paginated user list for the message modal (AJAX).
     * GET /admin/users/list
     */
    public function userList(Request $request)
    {
        $search = $request->input('q', '');
        $query  = User::select('id', 'name', 'email', 'mobile');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name',   'like', "%{$search}%")
                  ->orWhere('email',  'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(50);

        return response()->json($users);
    }

    /**
     * Send SMS via configured provider.
     *
     * HOW TO CONFIGURE (in .env):
     * ─────────────────────────────────────────────────────────
     * SMS_PROVIDER=sslwireless   # or: infobip | twilio | custom
     * SMS_API_KEY=your_api_key_here
     * SMS_SENDER_ID=AmardoKar    # Approved sender ID from provider
     *
     * For SSL Wireless (Bangladesh):  https://sms.sslwireless.com
     * For Infobip:                    https://www.infobip.com
     * For Twilio:                     https://www.twilio.com
     * ─────────────────────────────────────────────────────────
     */
    private function sendSms(string $mobile, string $message): bool
    {
        $provider  = config('services.sms.provider', env('SMS_PROVIDER', 'sslwireless'));
        $apiKey    = env('SMS_API_KEY');
        $senderId  = env('SMS_SENDER_ID', 'AmardoKar');

        if (!$apiKey) {
            \Log::warning('SMS_API_KEY not set in .env — SMS skipped.');
            return false;
        }

        // Normalize BD number: ensure starts with 880
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        if (str_starts_with($mobile, '0')) {
            $mobile = '880' . substr($mobile, 1);
        } elseif (!str_starts_with($mobile, '880')) {
            $mobile = '880' . $mobile;
        }

        if ($provider === 'sslwireless') {
            // SSL Wireless REST API
            $response = \Illuminate\Support\Facades\Http::asForm()->post(
                'https://sms.sslwireless.com/pushapi/dynamic/server.php',
                [
                    'api_token' => $apiKey,
                    'sid'       => $senderId,
                    'msisdn'    => $mobile,
                    'sms'       => $message,
                    'csmsid'    => uniqid('amk_'),
                ]
            );
            return $response->successful();

        } elseif ($provider === 'infobip') {
            // Infobip REST API
            $baseUrl = env('SMS_INFOBIP_BASE_URL', 'https://api.infobip.com');
            $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                ->post("{$baseUrl}/sms/2/text/advanced", [
                    'messages' => [[
                        'from'         => $senderId,
                        'destinations' => [['to' => $mobile]],
                        'text'         => $message,
                    ]],
                ]);
            return $response->successful();

        } elseif ($provider === 'twilio') {
            // Twilio REST API
            $sid  = env('TWILIO_SID');
            $from = env('TWILIO_FROM');
            $response = \Illuminate\Support\Facades\Http::withBasicAuth($sid, $apiKey)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $from,
                    'To'   => '+' . $mobile,
                    'Body' => $message,
                ]);
            return $response->successful();
        }

        \Log::warning("Unknown SMS provider: {$provider}");
        return false;
    }

}