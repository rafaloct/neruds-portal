<?php

declare(strict_types=1);

namespace Drupal\neruds_google_integration\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides the NERUDS territory map block.
 *
 * @Block(
 *   id = "neruds_territory_map",
 *   admin_label = @Translation("NERUDS territory map")
 * )
 */
final class TerritoryMapBlock extends BlockBase {

  public function build(): array {
    return [
      '#theme' => 'neruds_territory_map',
      '#title' => $this->t('Territorios de pesquisa'),
      '#summary' => $this->t('Explore projetos, pesquisadores e publicacoes por territorio.'),
      '#attached' => [
        'library' => ['neruds_google_integration/territory_map'],
        'drupalSettings' => [
          'nerudsTerritoryMap' => [
            'endpoint' => '/neruds/api/territories',
          ],
        ],
      ],
      '#cache' => [
        'max-age' => 300,
        'tags' => ['node_list', 'taxonomy_term_list', 'neruds_google_integration'],
      ],
    ];
  }

}
