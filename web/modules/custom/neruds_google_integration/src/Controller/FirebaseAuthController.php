<?php

declare(strict_types=1);

namespace Drupal\neruds_google_integration\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Placeholder endpoint for the future Flutter authentication bridge.
 */
final class FirebaseAuthController {

  public function exchangeToken(Request $request): JsonResponse {
    return new JsonResponse([
      'status' => 'not_configured',
      'message' => 'Firebase authentication exchange is reserved for the future Flutter app phase.',
    ], 501);
  }

}
