<?php

declare(strict_types=1);

namespace Drupal\neruds_google_integration\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides the NERUDS territorial timeline block.
 *
 * @Block(
 *   id = "neruds_territorial_timeline",
 *   admin_label = @Translation("NERUDS territorial timeline")
 * )
 */
final class TerritorialTimelineBlock extends BlockBase {

  public function build(): array {
    return [
      '#theme' => 'neruds_territorial_timeline',
      '#title' => $this->t('Agenda cientifica territorial'),
      '#attached' => [
        'library' => ['neruds_google_integration/territorial_timeline'],
        'drupalSettings' => [
          'nerudsTimeline' => [
            'endpoint' => '/neruds/api/calendar',
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
