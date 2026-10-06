<?php

namespace Drupal\neruds_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a responsive Card Grid block with dynamic or manual content.
 *
 * Features:
 *  - Pull items from a content type automatically (latest N items)
 *  - Manual mode with custom card data
 *  - Configurable columns (2, 3, 4)
 *  - Card variants: default, glass, minimal, featured
 *  - Optional section title and "View All" link
 *
 * @Block(
 *   id = "neruds_card_grid",
 *   admin_label = @Translation("NERUDS Card Grid"),
 *   category = @Translation("NERUDS"),
 * )
 */
class CardGridBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'section_title' => '',
      'section_subtitle' => '',
      'source' => 'dynamic',
      'content_type' => 'noticia',
      'item_count' => 6,
      'columns' => 3,
      'card_variant' => 'default',
      'view_all_url' => '',
      'view_all_text' => '',
      'manual_items' => '',
      'enable_animation' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['section_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Section Title'),
      '#default_value' => $config['section_title'],
      '#maxlength' => 255,
    ];

    $form['section_subtitle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Section Subtitle'),
      '#default_value' => $config['section_subtitle'],
      '#maxlength' => 255,
    ];

    $form['source'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Source'),
      '#options' => [
        'dynamic' => $this->t('Dynamic — Latest content from a content type'),
        'manual' => $this->t('Manual — Custom cards defined below'),
      ],
      '#default_value' => $config['source'],
    ];

    // Dynamic source options.
    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Type'),
      '#options' => [
        'noticia' => $this->t('Notícias'),
        'evento_cientifico' => $this->t('Eventos Científicos'),
        'projeto_pesquisa_extensao' => $this->t('Projetos de Pesquisa'),
        'perfil_pesquisador' => $this->t('Pesquisadores'),
        'publicacao_cientifica' => $this->t('Publicações Científicas'),
        'grupo_estudos' => $this->t('Grupos de Estudos'),
        'boletim_periodico' => $this->t('Boletins'),
        'article' => $this->t('Artigos'),
      ],
      '#default_value' => $config['content_type'],
      '#states' => [
        'visible' => [
          ':input[name="settings[source]"]' => ['value' => 'dynamic'],
        ],
      ],
    ];

    $form['item_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of Items'),
      '#default_value' => $config['item_count'],
      '#min' => 1,
      '#max' => 24,
      '#states' => [
        'visible' => [
          ':input[name="settings[source]"]' => ['value' => 'dynamic'],
        ],
      ],
    ];

    // Manual source options.
    $form['manual_items'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Manual Cards (one per line)'),
      '#default_value' => $config['manual_items'],
      '#description' => $this->t('Format: title|description|url|image_url (per line). Fields after title are optional.'),
      '#rows' => 6,
      '#states' => [
        'visible' => [
          ':input[name="settings[source]"]' => ['value' => 'manual'],
        ],
      ],
    ];

    // Display options.
    $form['columns'] = [
      '#type' => 'select',
      '#title' => $this->t('Grid Columns'),
      '#options' => [
        2 => $this->t('2 columns'),
        3 => $this->t('3 columns'),
        4 => $this->t('4 columns'),
      ],
      '#default_value' => $config['columns'],
    ];

    $form['card_variant'] = [
      '#type' => 'select',
      '#title' => $this->t('Card Style'),
      '#options' => [
        'default' => $this->t('Default — White card with shadow'),
        'glass' => $this->t('Glass — Glassmorphism effect'),
        'minimal' => $this->t('Minimal — No border, subtle shadow'),
        'featured' => $this->t('Featured — Accent border highlight'),
      ],
      '#default_value' => $config['card_variant'],
    ];

    $form['view_all_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('"View All" URL'),
      '#default_value' => $config['view_all_url'],
      '#maxlength' => 512,
    ];

    $form['view_all_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('"View All" Button Text'),
      '#default_value' => $config['view_all_text'],
      '#maxlength' => 64,
    ];

    $form['enable_animation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable scroll animations'),
      '#default_value' => $config['enable_animation'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $fields = [
      'section_title', 'section_subtitle', 'source', 'content_type',
      'item_count', 'columns', 'card_variant', 'view_all_url',
      'view_all_text', 'manual_items', 'enable_animation',
    ];
    foreach ($fields as $field) {
      $this->configuration[$field] = $form_state->getValue($field);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $items = [];

    if ($config['source'] === 'dynamic') {
      $items = $this->loadDynamicItems($config['content_type'], (int) $config['item_count']);
    }
    else {
      $items = $this->parseManualItems($config['manual_items']);
    }

    if (empty($items)) {
      return [];
    }

    return [
      '#theme' => 'neruds_card_grid',
      '#title' => $config['section_title'],
      '#subtitle' => $config['section_subtitle'],
      '#columns' => (int) $config['columns'],
      '#card_variant' => $config['card_variant'],
      '#items' => $items,
      '#view_all_url' => $config['view_all_url'],
      '#view_all_text' => $config['view_all_text'],
      '#enable_animation' => (bool) $config['enable_animation'],
      '#attached' => [
        'library' => ['neruds_gui/card-grid'],
      ],
      '#cache' => [
        'contexts' => ['url'],
        'max-age' => 1800,
      ],
    ];
  }

  /**
   * Load latest published nodes of a given content type.
   */
  protected function loadDynamicItems(string $content_type, int $count): array {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('type', $content_type);
    $query->condition('status', 1);
    $query->sort('created', 'DESC');
    $query->range(0, $count);
    $query->accessCheck(TRUE);

    $nids = $query->execute();
    if (empty($nids)) {
      return [];
    }

    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    $items = [];

    foreach ($nodes as $node) {
      $item = [
        'title' => $node->getTitle(),
        'url' => $node->toUrl('canonical')->toString(),
        'description' => '',
        'image_url' => '',
        'image_alt' => $node->getTitle(),
        'date' => \Drupal::service('date.formatter')->format($node->getCreatedTime(), 'medium'),
        'content_type' => $node->bundle(),
        'badges' => [],
      ];

      // Extract body/description.
      $body_fields = ['body', 'field_descricao', 'field_resumo', 'field_bio'];
      foreach ($body_fields as $field) {
        if ($node->hasField($field) && !$node->get($field)->isEmpty()) {
          $value = $node->get($field)->first()->getValue();
          $text = $value['summary'] ?? $value['value'] ?? '';
          $item['description'] = mb_strimwidth(strip_tags($text), 0, 180, '...');
          break;
        }
      }

      // Extract image.
      $image_fields = ['field_imagem', 'field_image', 'field_foto', 'field_featured_image'];
      foreach ($image_fields as $field) {
        if ($node->hasField($field) && !$node->get($field)->isEmpty()) {
          $media_or_file = $node->get($field)->entity;
          if ($media_or_file) {
            // Handle Media entity.
            if (method_exists($media_or_file, 'hasField') && $media_or_file->hasField('field_media_image')) {
              $file = $media_or_file->get('field_media_image')->entity;
              if ($file) {
                $item['image_url'] = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
                $item['image_alt'] = $media_or_file->get('field_media_image')->first()->getValue()['alt'] ?? $node->getTitle();
              }
            }
            // Handle File entity directly.
            elseif (method_exists($media_or_file, 'getFileUri')) {
              $item['image_url'] = \Drupal::service('file_url_generator')->generateAbsoluteString($media_or_file->getFileUri());
            }
          }
          break;
        }
      }

      // Extract ODS badges if available.
      if ($node->hasField('field_ods') && !$node->get('field_ods')->isEmpty()) {
        foreach ($node->get('field_ods') as $term_ref) {
          if ($term_ref->entity) {
            $item['badges'][] = $term_ref->entity->label();
          }
        }
        $item['badges'] = array_slice($item['badges'], 0, 3);
      }

      $items[] = $item;
    }

    return $items;
  }

  /**
   * Parse manual items from "title|description|url|image_url" format.
   */
  protected function parseManualItems(string $raw): array {
    if (empty($raw)) {
      return [];
    }

    $items = [];
    $lines = array_filter(array_map('trim', explode("\n", $raw)));

    foreach ($lines as $line) {
      $parts = array_map('trim', explode('|', $line));
      if (empty($parts[0])) {
        continue;
      }
      $items[] = [
        'title' => $parts[0],
        'description' => $parts[1] ?? '',
        'url' => $parts[2] ?? '',
        'image_url' => $parts[3] ?? '',
        'image_alt' => $parts[0],
        'date' => '',
        'content_type' => '',
        'badges' => [],
      ];
    }

    return $items;
  }

}
