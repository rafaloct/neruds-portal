<?php

declare(strict_types=1);

namespace Drupal\neruds_google_integration\Controller;

/**
 * Lightweight landing pages for portal components.
 */
final class NerudsPageController {

  public static function publications(): array {
    $config = \Drupal::config('neruds_google_integration.settings');
    $cse_cx = getenv('NERUDS_GOOGLE_SEARCH_CX') ?: (string) ($config->get('google_programmable_search_cx') ?: '');
    $source = (string) \Drupal::request()->query->get('source', 'neruds');
    if (!in_array($source, ['neruds', 'scientific'], TRUE)) {
      $source = 'neruds';
    }

    return [
      '#theme' => 'neruds_publication_discovery',
      '#title' => t('Descoberta acadêmica'),
      '#attached' => [
        'library' => ['neruds_google_integration/publication_discovery'],
        'drupalSettings' => [
          'nerudsPublicationDiscovery' => [
            'searchEndpoint' => '/neruds/api/google-search',
            'metricsEndpoint' => '/neruds/api/publication-metrics',
            'scientificEndpoint' => '/neruds/api/scientific-search',
            'cseCx' => $cse_cx,
            'defaultSource' => $source,
          ],
        ],
      ],
      '#cache' => [
        'max-age' => 300,
        'tags' => ['node_list', 'neruds_google_integration'],
      ],
    ];
  }

  public static function datasets(): array {
    return [
      '#theme' => 'neruds_dataset_discovery',
      '#title' => t('Descoberta de Datasets'),
      '#attached' => [
        'library' => ['neruds_google_integration/dataset_discovery'],
        'drupalSettings' => [
          'nerudsDatasetDiscovery' => [
            'datasetsEndpoint' => '/neruds/api/datasets',
            'datasetDetailEndpoint' => '/neruds/api/datasets',
            'samplesEndpoint' => '/neruds/api/audit/samples',
          ],
        ],
      ],
      '#cache' => [
        'max-age' => 300,
        'tags' => ['neruds_google_integration'],
      ],
    ];
  }

  public static function territory(string $territory): array {
    $label = ucwords(str_replace('-', ' ', $territory));
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['neruds-territory-landing']],
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h1',
        '#value' => t('@territory', ['@territory' => $label]),
      ],
      'intro' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => t('Conteudos relacionados ao territorio selecionado. Use as paginas de noticias, projetos e publicacoes para aprofundar a pesquisa.'),
      ],
      'links' => [
        '#theme' => 'item_list',
        '#items' => [
          ['#markup' => '<a href="/noticias?territorio=' . $territory . '">Noticias</a>'],
          ['#markup' => '<a href="/projetos?territorio=' . $territory . '">Projetos</a>'],
          ['#markup' => '<a href="/publicacoes-discovery?q=' . $territory . '">Publicacoes</a>'],
        ],
      ],
      '#cache' => [
        'max-age' => 300,
        'contexts' => ['url.path'],
      ],
    ];
  }

}
