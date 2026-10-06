<?php

declare(strict_types=1);

namespace Drupal\neruds_google_integration\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides the hybrid publication discovery block.
 *
 * @Block(
 *   id = "neruds_publication_discovery",
 *   admin_label = @Translation("NERUDS publication discovery")
 * )
 */
final class PublicationDiscoveryBlock extends BlockBase {

  public function build(): array {
    $config = \Drupal::config('neruds_google_integration.settings');
    $cse_cx = getenv('NERUDS_GOOGLE_SEARCH_CX') ?: (string) ($config->get('google_programmable_search_cx') ?: '');

    return [
      '#theme' => 'neruds_publication_discovery',
      '#title' => $this->t('Descoberta academica'),
      '#attached' => [
        'library' => ['neruds_google_integration/publication_discovery'],
        'drupalSettings' => [
          'nerudsPublicationDiscovery' => [
            'searchEndpoint' => '/neruds/api/google-search',
            'metricsEndpoint' => '/neruds/api/publication-metrics',
            'cseCx' => $cse_cx,
          ],
        ],
      ],
      '#cache' => [
        'max-age' => 300,
        'tags' => ['node_list', 'neruds_google_integration'],
      ],
    ];
  }

}
