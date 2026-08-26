import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'api_client.dart';

class AnalyticsService {
  /// Tracks a Google Tag event from the mobile application.
  static Future<void> trackEvent(String eventName, {Map<String, dynamic>? params}) async {
    try {
      final finalParams = Map<String, dynamic>.from(params ?? {});
      finalParams['platform'] = 'mobile';

      final url = Uri.parse('$kBaseUrl/analytics/track-event');
      await http.post(
        url,
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'event_name': eventName,
          'params': finalParams,
        }),
      );

      if (kDebugMode) {
        print('Analytics Event Tracked: $eventName ($finalParams)');
      }
    } catch (e) {
      if (kDebugMode) {
        print('Analytics Track Error: $e');
      }
    }
  }
}
