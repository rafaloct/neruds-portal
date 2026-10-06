<?php

namespace Drupal\neruds_gui_views\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\Entity\Term;

/**
 * Presents confirmed research lines from the NERUDS taxonomy.
 */
class ResearchLinesController extends ControllerBase {

  /**
   * Builds the public research lines page.
   */
  public function page(): array {
    $terms = $this->entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('linhas_pesquisa', 0, NULL, FALSE);

    $items = [];
    foreach ($terms as $term_info) {
      $term = Term::load($term_info->tid);
      if (!$term || !$term->access('view')) {
        continue;
      }

      $items[] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['research-line-list__item'],
        ],
        'title' => [
          '#type' => 'link',
          '#title' => $term->label(),
          '#url' => $term->toUrl(),
          '#attributes' => [
            'class' => ['research-line-list__link'],
          ],
        ],
      ];
    }

    if (!$items) {
      $items[] = [
        '#markup' => '<p>As linhas de pesquisa serão publicadas após validação editorial.</p>',
      ];
    }

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['research-lines-page'],
      ],
      'intro' => [
        '#markup' => '<p class="research-lines-page__intro">Lista gerada a partir da taxonomia institucional aprovada no Portal NERUDS.</p>',
      ],
      'list' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['research-line-list'],
        ],
        'items' => $items,
      ],
      '#cache' => [
        'contexts' => ['languages:language_interface', 'user.permissions'],
        'tags' => ['taxonomy_term_list:linhas_pesquisa'],
      ],
    ];
  }

}
