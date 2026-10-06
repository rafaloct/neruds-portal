<?php

declare(strict_types=1);

namespace Drupal\neruds_google_integration\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public JSON endpoints for portal components.
 */
final class NerudsApiController {

  public static function dashboard(): JsonResponse {
    return self::json(\Drupal::service('neruds_google_integration.portal_data')->getDashboard());
  }

  public static function territories(): JsonResponse {
    return self::json(\Drupal::service('neruds_google_integration.portal_data')->getTerritories());
  }

  public static function calendar(): JsonResponse {
    return self::json(\Drupal::service('neruds_google_integration.portal_data')->getCalendarEvents());
  }

  public static function calendarIcs(): Response {
    $events = \Drupal::service('neruds_google_integration.portal_data')->getCalendarEvents();
    $lines = [
      'BEGIN:VCALENDAR',
      'VERSION:2.0',
      'PRODID:-//NERUDS//Agenda Territorial//PT-BR',
      'CALSCALE:GREGORIAN',
      'METHOD:PUBLISH',
    ];

    foreach ($events as $event) {
      $start = self::icsDate($event['start'] ?? 'now');
      $end = self::icsDate($event['end'] ?? ($event['start'] ?? 'now'), '+1 hour');
      $uid = preg_replace('/[^A-Za-z0-9@._-]/', '-', (string) ($event['id'] ?? md5(($event['title'] ?? '') . $start))) . '@neruds.org';
      $lines[] = 'BEGIN:VEVENT';
      $lines[] = 'UID:' . $uid;
      $lines[] = 'DTSTAMP:' . gmdate('Ymd\THis\Z');
      $lines[] = 'DTSTART:' . $start;
      $lines[] = 'DTEND:' . $end;
      $lines[] = 'SUMMARY:' . self::icsEscape((string) ($event['title'] ?? 'Evento NERUDS'));
      if (!empty($event['url'])) {
        $lines[] = 'URL:' . self::icsEscape((string) $event['url']);
      }
      $description = $event['extendedProps']['description'] ?? $event['extendedProps']['territory'] ?? '';
      if ($description !== '') {
        $lines[] = 'DESCRIPTION:' . self::icsEscape((string) $description);
      }
      $lines[] = 'END:VEVENT';
    }
    $lines[] = 'END:VCALENDAR';

    $response = new Response(implode("\r\n", $lines) . "\r\n");
    $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="neruds-agenda.ics"');
    $response->setPublic();
    $response->setMaxAge(300);
    return $response;
  }

  public static function publicationMetrics(Request $request): JsonResponse {
    return self::json(\Drupal::service('neruds_google_integration.portal_data')->getPublicationMetrics(
      (string) $request->query->get('q', ''),
      self::publicationFilters($request),
    ));
  }

  public static function auditCitations(Request $request): JsonResponse {
    return self::json(\Drupal::service('neruds_google_integration.audit')->citations([
      'project' => (string) $request->query->get('project', ''),
      'sigilo' => (string) $request->query->get('sigilo', ''),
      'q' => (string) $request->query->get('q', ''),
      'limit' => (int) $request->query->get('limit', 20),
      'offset' => (int) $request->query->get('offset', 0),
    ]));
  }

  public static function auditSamples(Request $request): JsonResponse {
    return self::json(\Drupal::service('neruds_google_integration.audit')->samples([
      'project' => (string) $request->query->get('project', ''),
      'sigilo' => (string) $request->query->get('sigilo', ''),
      'q' => (string) $request->query->get('q', ''),
      'limit' => (int) $request->query->get('limit', 20),
      'offset' => (int) $request->query->get('offset', 0),
    ]));
  }

  public static function auditSchema(): JsonResponse {
    return self::json(\Drupal::service('neruds_google_integration.audit')->schema());
  }

  public static function auditLookup(Request $request): JsonResponse {
    $id = (string) $request->query->get('id', '');
    return self::json(\Drupal::service('neruds_google_integration.audit')->citations([
      'q' => $id,
      'limit' => $id !== '' ? 1 : 20,
      'offset' => 0,
    ]));
  }

  public static function datasets(Request $request): JsonResponse {
    return self::json(\Drupal::service('neruds_google_integration.audit')->datasets([
      'project' => (string) $request->query->get('project', ''),
      'sigilo' => (string) $request->query->get('sigilo', ''),
      'q' => (string) $request->query->get('q', ''),
      'limit' => (int) $request->query->get('limit', 20),
      'offset' => (int) $request->query->get('offset', 0),
    ]));
  }

  public static function datasetDetail(Request $request, string $dataset_id): JsonResponse {
    return self::json(\Drupal::service('neruds_google_integration.audit')->datasetDetail(
      $dataset_id,
      (int) $request->query->get('sample_limit', 5),
    ));
  }

  public static function driveFolder(Request $request): JsonResponse {
    $folder = (string) $request->query->get('folder', '');
    return self::json(\Drupal::service('neruds_google_integration.portal_data')->getDriveFolder($folder));
  }

  public static function googleSearch(Request $request): JsonResponse {
    $query = (string) $request->query->get('q', '');
    $start = (int) $request->query->get('start', 1);
    return self::json(\Drupal::service('neruds_google_integration.portal_data')->googleSearch(
      $query,
      $start,
      self::publicationFilters($request),
    ));
  }

  public static function scientificSearch(Request $request): JsonResponse {
    $query = (string) $request->query->get('q', '');
    $start = (int) $request->query->get('start', 0);
    $count = (int) $request->query->get('count', 10);
    return self::json(\Drupal::service('neruds_google_integration.portal_data')->scientificSearch(
      $query,
      $start,
      $count,
    ));
  }

  public static function searchReadiness(): JsonResponse {
    return self::json(\Drupal::service('neruds_google_integration.portal_data')->getSearchReadiness());
  }

  public static function forumModerationStatus(): JsonResponse {
    return self::json(\Drupal::service('neruds_google_integration.portal_data')->getForumModerationStatus());
  }

  public static function chatSession(): JsonResponse {
    $sessionId = bin2hex(random_bytes(16));
    \Drupal::cache('data')->set(
      'neruds_chat_session_' . $sessionId,
      [
        'created' => time(),
        'messages' => [],
      ],
      \Drupal::time()->getRequestTime() + (24 * 3600),
    );
    return new JsonResponse(['session_id' => $sessionId]);
  }

  public static function chat(Request $request): JsonResponse {
    if ($request->getMethod() !== 'POST') {
      return new JsonResponse(['error' => 'Method not allowed'], 405);
    }

    $data = json_decode((string) $request->getContent(), TRUE);
    $message = (string) ($data['message'] ?? '');
    $sessionId = (string) ($data['session_id'] ?? '');

    if (empty($message) || empty($sessionId)) {
      return new JsonResponse(
        ['error' => 'Message and session_id required'],
        400,
      );
    }

    // Validate message
    $message = \Drupal::service('neruds_google_integration.portal_data')
      ->sanitizeInput($message, 500);

    try {
      $response = \Drupal::service('neruds_google_integration.portal_data')
        ->chatWithGemini($message, $sessionId);

      // Store in session cache
      $cacheKey = 'neruds_chat_session_' . $sessionId;
      $session = \Drupal::cache('data')->get($cacheKey);
      if ($session) {
        $messages = $session->data['messages'] ?? [];
        $messages[] = ['role' => 'user', 'content' => $message];
        $messages[] = ['role' => 'assistant', 'content' => $response['response']];
        \Drupal::cache('data')->set(
          $cacheKey,
          [
            'created' => $session->data['created'] ?? time(),
            'messages' => array_slice($messages, -20),
          ],
          \Drupal::time()->getRequestTime() + (24 * 3600),
        );
      }

      $jsonResponse = new JsonResponse($response);
      $jsonResponse->setPublic();
      $jsonResponse->setMaxAge(0);
      return $jsonResponse;
    } catch (\Exception $e) {
      \Drupal::logger('neruds_google_integration')
        ->error('Chat error: @error', ['@error' => $e->getMessage()]);
      return new JsonResponse(
        ['error' => 'Failed to process chat message'],
        500,
      );
    }
  }

  private static function publicationFilters(Request $request): array {
    return [
      'territory' => (string) $request->query->get('territory', ''),
      'ods' => (string) $request->query->get('ods', ''),
      'type' => (string) $request->query->get('type', ''),
      'year' => (string) $request->query->get('year', ''),
    ];
  }

  private static function json(array $data): JsonResponse {
    $response = new JsonResponse($data);
    $response->setPublic();
    $response->setMaxAge(300);
    $response->headers->set('X-Drupal-Cache-Tags', 'neruds_google_integration');
    return $response;
  }

  private static function icsDate(string $value, string $fallbackOffset = ''): string {
    $timestamp = strtotime($value);
    if ($timestamp === FALSE) {
      $timestamp = time();
    }
    if ($fallbackOffset !== '' && strtotime($value) !== FALSE && !str_contains($value, 'T')) {
      $timestamp = strtotime($fallbackOffset, $timestamp) ?: $timestamp;
    }
    return gmdate('Ymd\THis\Z', $timestamp);
  }

  private static function icsEscape(string $value): string {
    return str_replace(["\\", "\n", "\r", ',', ';'], ["\\\\", "\\n", '', "\\,", "\\;"], $value);
  }

}
