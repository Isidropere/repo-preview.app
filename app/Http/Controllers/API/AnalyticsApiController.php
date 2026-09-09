<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AnalyticsApiController extends Controller
{
    /**
     * POST /api/analytics/track-event
     * Receives event payloads from mobile app and dispatches them to Google Tag / Analytics.
     */
    public function trackEvent(Request $request)
    {
        $data = $request->validate([
            'event_name' => 'required|string|max:100',
            'params'     => 'nullable|array',
        ]);

        $eventName = $data['event_name'];
        $params    = $data['params'] ?? [];
        $params['platform'] = $params['platform'] ?? 'mobile';

        try {
            $conversionLabel = 'AW-18379974826';
            if (in_array($eventName, ['user_registration', 'publish_product_success', 'publish_talent_success'])) {
                $conversionLabel = 'AW-18379974826/wL3QCNjWq-QcEKrRoLxE';
            } elseif (in_array($eventName, ['home_public_view', 'home_logged_in_view', 'login_page_view'])) {
                $conversionLabel = 'AW-18379974826/xBm2CJTgkuEcEKrRoLxE';
            }

            $params['send_to'] = $conversionLabel;

            // Forward event hit to Google Analytics / Google Tag Measurement Protocol
            Http::timeout(3)->post('https://www.google-analytics.com/g/collect', array_merge([
                'v'   => '2',
                'tid' => 'AW-18379974826',
                'cid' => $request->header('X-Client-ID') ?? (string) Str::uuid(),
                'en'  => $eventName,
            ], $this->formatGoogleParams($params)));

            Log::info('Google Tag event dispatched from mobile API', [
                'event_name' => $eventName,
                'send_to'    => $conversionLabel,
                'params'     => $params,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Error sending Google Tag event from mobile API', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success'    => true,
            'event_name' => $eventName,
            'message'    => 'Event tracked successfully',
        ]);
    }

    private function formatGoogleParams(array $params): array
    {
        $formatted = [];
        foreach ($params as $key => $value) {
            $formatted["ep.{$key}"] = is_array($value) ? json_encode($value) : (string) $value;
        }
        return $formatted;
    }
}
