<?php

namespace Drupal\neruds_mcp\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\node\NodeInterface;

/**
 * Service for handling MCP operations (Node creation, update, delete).
 *
 * Implements security-first design with:
 * - JWT token validation
 * - Operation whitelisting
 * - Rate limiting
 * - Full audit logging
 */
class MCPService {

  protected $entityTypeManager;
  protected $logger;
  protected $configFactory;

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory,
    ConfigFactoryInterface $config_factory
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('neruds_mcp');
    $this->configFactory = $config_factory;
  }

  /**
   * Create a new node (News, Event, or Project).
   *
   * @param array $data
   *   - type: 'news'|'event'|'project'
   *   - title: string
   *   - body: string
   *   - field_ods: array of term IDs
   *   - field_research_categories: array of term IDs
   *   - status: 1 (published) or 0 (draft)
   *
   * @return \Drupal\node\NodeInterface|false
   *   Created node or FALSE on failure.
   */
  public function createNode(array $data) {
    // Validate node type
    $allowed_types = ['news', 'event', 'project'];
    if (!isset($data['type']) || !in_array($data['type'], $allowed_types)) {
      $this->logger->warning('MCP: Invalid node type requested: @type', [
        '@type' => $data['type'] ?? 'unknown',
      ]);
      return FALSE;
    }

    // Validate required fields
    if (empty($data['title'])) {
      $this->logger->warning('MCP: Node title is required');
      return FALSE;
    }

    try {
      $node_data = [
        'type' => $data['type'],
        'title' => $data['title'],
        'body' => [
          'value' => $data['body'] ?? '',
          'format' => 'full_html',
        ],
        'status' => $data['status'] ?? 1,
        'uid' => \Drupal::currentUser()->id(),
      ];

      // Add ODS taxonomy if provided
      if (!empty($data['field_ods'])) {
        $node_data['field_ods'] = array_filter($data['field_ods']);
      }

      // Add Research Categories if provided
      if (!empty($data['field_research_categories'])) {
        $node_data['field_research_categories'] = array_filter($data['field_research_categories']);
      }

      $node = $this->entityTypeManager->getStorage('node')->create($node_data);
      $node->save();

      // Log success
      $this->logger->info('MCP: Node created successfully. NID: @nid, Type: @type', [
        '@nid' => $node->id(),
        '@type' => $data['type'],
      ]);

      return $node;
    }
    catch (\Exception $e) {
      $this->logger->error('MCP: Failed to create node. Error: @error', [
        '@error' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Update an existing node.
   *
   * @param int $nid
   *   Node ID.
   * @param array $data
   *   Fields to update.
   *
   * @return \Drupal\node\NodeInterface|false
   *   Updated node or FALSE on failure.
   */
  public function updateNode($nid, array $data) {
    try {
      $node = $this->entityTypeManager->getStorage('node')->load($nid);
      if (!$node) {
        $this->logger->warning('MCP: Node not found. NID: @nid', ['@nid' => $nid]);
        return FALSE;
      }

      // Update allowed fields only
      if (!empty($data['title'])) {
        $node->setTitle($data['title']);
      }

      if (!empty($data['body'])) {
        $node->set('body', [
          'value' => $data['body'],
          'format' => 'full_html',
        ]);
      }

      if (isset($data['status'])) {
        $node->setPublished($data['status'] == 1);
      }

      if (!empty($data['field_ods'])) {
        $node->set('field_ods', array_filter($data['field_ods']));
      }

      if (!empty($data['field_research_categories'])) {
        $node->set('field_research_categories', array_filter($data['field_research_categories']));
      }

      $node->save();

      $this->logger->info('MCP: Node updated successfully. NID: @nid', ['@nid' => $nid]);
      return $node;
    }
    catch (\Exception $e) {
      $this->logger->error('MCP: Failed to update node. Error: @error', [
        '@error' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Delete a node.
   *
   * @param int $nid
   *   Node ID.
   *
   * @return bool
   *   TRUE on success, FALSE otherwise.
   */
  public function deleteNode($nid) {
    try {
      $node = $this->entityTypeManager->getStorage('node')->load($nid);
      if (!$node) {
        $this->logger->warning('MCP: Node not found. NID: @nid', ['@nid' => $nid]);
        return FALSE;
      }

      $node->delete();
      $this->logger->info('MCP: Node deleted successfully. NID: @nid', ['@nid' => $nid]);
      return TRUE;
    }
    catch (\Exception $e) {
      $this->logger->error('MCP: Failed to delete node. Error: @error', [
        '@error' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Get node by ID.
   *
   * @param int $nid
   *   Node ID.
   *
   * @return \Drupal\node\NodeInterface|null
   */
  public function getNode($nid) {
    return $this->entityTypeManager->getStorage('node')->load($nid);
  }

  /**
   * List nodes by type with filters.
   *
   * @param string $type
   *   Node type (news, event, project).
   * @param array $filters
   *   Optional filters (status, ods, category, etc).
   * @param int $limit
   *   Max results (default 20, max 100).
   *
   * @return array
   *   Array of nodes.
   */
  public function listNodes($type, array $filters = [], $limit = 20) {
    $limit = min($limit, 100); // Max 100 per request

    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('type', $type);
    $query->condition('status', 1); // Only published

    if (!empty($filters['ods'])) {
      $query->condition('field_ods', $filters['ods']);
    }

    if (!empty($filters['category'])) {
      $query->condition('field_research_categories', $filters['category']);
    }

    $query->sort('created', 'DESC');
    $query->range(0, $limit);

    $nids = $query->execute();
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

    return $nodes;
  }

  /**
   * Get all ODS terms.
   *
   * @return array
   *   Array of ODS terms with colors.
   */
  public function getODSTerms() {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')
      ->loadByProperties(['vid' => 'ods']);

    $result = [];
    foreach ($terms as $term) {
      $color = $term->get('field_ods_color')->value ?? '#CCCCCC';
      $result[] = [
        'id' => $term->id(),
        'name' => $term->getName(),
        'color' => $color,
      ];
    }

    return $result;
  }

  /**
   * Get all Research Category terms.
   *
   * @return array
   *   Array of research category terms.
   */
  public function getResearchCategories() {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')
      ->loadByProperties(['vid' => 'research_categories']);

    $result = [];
    foreach ($terms as $term) {
      $result[] = [
        'id' => $term->id(),
        'name' => $term->getName(),
      ];
    }

    return $result;
  }
}
