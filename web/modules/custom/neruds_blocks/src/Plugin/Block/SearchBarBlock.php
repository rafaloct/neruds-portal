<?php

namespace Drupal\neruds_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a premium Search Bar block with expandable overlay and category chips.
 *
 * Features:
 *  - Expandable search overlay on click
 *  - Category filter chips (configurable)
 *  - Search API Autocomplete integration
 *  - Fullscreen on mobile, overlay on desktop
 *  - Keyboard accessible (Esc to close, / to open)
 *
 * @Block(
 *   id = "neruds_search_bar",
 *   admin_label = @Translation("NERUDS Search Bar"),
 *   category = @Translation("NERUDS"),
 * )
 */
class SearchBarBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'placeholder' => 'Buscar pesquisadores, projetos, publicações...',
      'search_action' => '/busca',
      'show_categories' => TRUE,
      'categories' => "all|Todos\nperfil_pesquisador|Pesquisadores\nprojeto_pesquisa_extensao|Projetos\npublicacao_cientifica|Publicações\nevento_cientifico|Eventos\nnoticia|Notícias",
      'style' => 'inline',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['style'] = [
      '#type' => 'select',
      '#title' => $this->t('Display Style'),
      '#options' => [
        'inline' => $this->t('Inline — Compact bar for header/sidebar'),
        'hero' => $this->t('Hero — Large centered search for homepage'),
        'overlay' => $this->t('Overlay — Icon trigger that opens fullscreen search'),
      ],
      '#default_value' => $config['style'],
    ];

    $form['placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder Text'),
      '#default_value' => $config['placeholder'],
      '#maxlength' => 128,
    ];

    $form['search_action'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search Form Action URL'),
      '#default_value' => $config['search_action'],
      '#description' => $this->t('The URL to submit the search form to (e.g., /search or /busca).'),
      '#maxlength' => 255,
    ];

    $form['show_categories'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show category filter chips'),
      '#default_value' => $config['show_categories'],
    ];

    $form['categories'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Category Chips (one per line)'),
      '#default_value' => $config['categories'],
      '#description' => $this->t('Format: machine_name|Label (e.g., "perfil_pesquisador|Pesquisadores"). First item is the default.'),
      '#rows' => 6,
      '#states' => [
        'visible' => [
          ':input[name="settings[show_categories]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    foreach (['style', 'placeholder', 'search_action', 'show_categories', 'categories'] as $field) {
      $this->configuration[$field] = $form_state->getValue($field);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    // Parse categories.
    $categories = [];
    if ($config['show_categories'] && !empty($config['categories'])) {
      $lines = array_filter(array_map('trim', explode("\n", $config['categories'])));
      foreach ($lines as $line) {
        $parts = array_map('trim', explode('|', $line, 2));
        if (count($parts) === 2) {
          $categories[] = ['value' => $parts[0], 'label' => $parts[1]];
        }
      }
    }

    return [
      '#theme' => 'neruds_search_bar',
      '#style' => $config['style'],
      '#placeholder' => $config['placeholder'],
      '#search_action' => $config['search_action'],
      '#categories' => $categories,
      '#attached' => [
        'library' => ['neruds_gui/search-bar'],
      ],
      '#cache' => [
        'contexts' => ['url.query_args:keys', 'url.query_args:type'],
        'max-age' => 86400,
      ],
    ];
  }

}
