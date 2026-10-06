<?php

namespace Drupal\neruds_related_content\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Related Content block.
 *
 * @Block(
 *   id = "neruds_related_content",
 *   admin_label = @Translation("NERUDS Related Content"),
 *   category = @Translation("NERUDS"),
 * )
 */
class RelatedContentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $entityTypeManager;
  protected $routeMatch;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->routeMatch->getParameter('node');
    if (!$node) {
      return [];
    }

    $related = $this->getRelatedByODS($node);
    if (empty($related)) {
      $related = $this->getRelatedByCategory($node);
    }

    if (empty($related)) {
      return [];
    }

    return [
      '#theme' => 'neruds_related_content',
      '#current_node' => $node,
      '#related_nodes' => $related,
      '#cache' => [
        'contexts' => ['url'],
        'max-age' => 3600,
      ],
    ];
  }

  /**
   * Get related nodes by ODS taxonomy.
   */
  protected function getRelatedByODS($node) {
    if (!$node->hasField('field_ods') || $node->get('field_ods')->isEmpty()) {
      return [];
    }

    $ods_ids = array_column($node->get('field_ods')->getValue(), 'target_id');
    return $this->queryRelatedNodes($node, 'field_ods', $ods_ids, 4);
  }

  /**
   * Get related nodes by research category taxonomy.
   */
  protected function getRelatedByCategory($node) {
    $category_fields = ['field_research_categories', 'field_area_tematica', 'field_temas'];
    foreach ($category_fields as $field) {
      if ($node->hasField($field) && !$node->get($field)->isEmpty()) {
        $ids = array_column($node->get($field)->getValue(), 'target_id');
        $results = $this->queryRelatedNodes($node, $field, $ids, 4);
        if (!empty($results)) {
          return $results;
        }
      }
    }
    return [];
  }

  /**
   * Query related nodes by field and term IDs.
   */
  protected function queryRelatedNodes($node, $field, $term_ids, $limit = 4) {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('status', 1);
    $query->condition($field, $term_ids, 'IN');
    $query->condition('nid', $node->id(), '!=');
    $query->sort('created', 'DESC');
    $query->range(0, $limit);
    $query->accessCheck(TRUE);

    $nids = $query->execute();
    return $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
  }

}
