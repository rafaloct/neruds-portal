<?php

namespace Drupal\neruds_mcp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\neruds_mcp\Service\MCPService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * REST Controller for MCP operations.
 *
 * Endpoints:
 * - POST   /api/mcp/nodes       → Create node
 * - GET    /api/mcp/nodes/{nid} → Get node
 * - PATCH  /api/mcp/nodes/{nid} → Update node
 * - DELETE /api/mcp/nodes/{nid} → Delete node
 * - GET    /api/mcp/nodes       → List nodes (with filters)
 * - GET    /api/mcp/taxonomy/ods → Get ODS terms
 * - GET    /api/mcp/taxonomy/categories → Get categories
 */
class MCPController extends ControllerBase {

  protected $mcpService;

  public function __construct(MCPService $mcp_service) {
    $this->mcpService = $mcp_service;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('neruds_mcp.service')
    );
  }

  /**
   * Create a new node.
   */
  public function createNode(Request $request) {
    $data = json_decode($request->getContent(), TRUE);

    if (!$data) {
      return new JsonResponse([
        'error' => 'Invalid JSON',
      ], 400);
    }

    $node = $this->mcpService->createNode($data);

    if (!$node) {
      return new JsonResponse([
        'error' => 'Failed to create node',
      ], 400);
    }

    return new JsonResponse([
      'success' => TRUE,
      'nid' => $node->id(),
      'type' => $node->bundle(),
      'title' => $node->getTitle(),
      'url' => $node->toUrl('canonical', ['absolute' => TRUE])->toString(),
    ], 201);
  }

  /**
   * Get a single node.
   */
  public function getNode($nid) {
    $node = $this->mcpService->getNode($nid);

    if (!$node) {
      return new JsonResponse([
        'error' => 'Node not found',
      ], 404);
    }

    return new JsonResponse($this->serializeNode($node), 200);
  }

  /**
   * Update a node.
   */
  public function updateNode($nid, Request $request) {
    $data = json_decode($request->getContent(), TRUE);

    if (!$data) {
      return new JsonResponse([
        'error' => 'Invalid JSON',
      ], 400);
    }

    $node = $this->mcpService->updateNode($nid, $data);

    if (!$node) {
      return new JsonResponse([
        'error' => 'Failed to update node',
      ], 400);
    }

    return new JsonResponse([
      'success' => TRUE,
      'nid' => $node->id(),
      'message' => 'Node updated successfully',
    ], 200);
  }

  /**
   * Delete a node.
   */
  public function deleteNode($nid) {
    $result = $this->mcpService->deleteNode($nid);

    if (!$result) {
      return new JsonResponse([
        'error' => 'Failed to delete node',
      ], 400);
    }

    return new JsonResponse([
      'success' => TRUE,
      'message' => 'Node deleted successfully',
    ], 200);
  }

  /**
   * List nodes by type.
   */
  public function listNodes(Request $request) {
    $type = $request->query->get('type', 'news');
    $limit = (int) $request->query->get('limit', 20);
    $ods = $request->query->get('ods');
    $category = $request->query->get('category');

    $filters = [];
    if ($ods) {
      $filters['ods'] = $ods;
    }
    if ($category) {
      $filters['category'] = $category;
    }

    $nodes = $this->mcpService->listNodes($type, $filters, $limit);

    $items = [];
    foreach ($nodes as $node) {
      $items[] = $this->serializeNode($node);
    }

    return new JsonResponse([
      'type' => $type,
      'count' => count($items),
      'items' => $items,
    ], 200);
  }

  /**
   * Get all ODS terms.
   */
  public function getODSTerms() {
    $terms = $this->mcpService->getODSTerms();

    return new JsonResponse([
      'vocabulary' => 'ods',
      'count' => count($terms),
      'terms' => $terms,
    ], 200);
  }

  /**
   * Get all research categories.
   */
  public function getCategories() {
    $categories = $this->mcpService->getResearchCategories();

    return new JsonResponse([
      'vocabulary' => 'research_categories',
      'count' => count($categories),
      'terms' => $categories,
    ], 200);
  }

  /**
   * Helper: Serialize a node to array.
   */
  protected function serializeNode($node) {
    $ods = [];
    foreach ($node->get('field_ods') as $term_ref) {
      $ods[] = [
        'id' => $term_ref->target_id,
        'name' => $term_ref->entity->getName(),
      ];
    }

    $categories = [];
    foreach ($node->get('field_research_categories') as $term_ref) {
      $categories[] = [
        'id' => $term_ref->target_id,
        'name' => $term_ref->entity->getName(),
      ];
    }

    return [
      'nid' => $node->id(),
      'type' => $node->bundle(),
      'title' => $node->getTitle(),
      'body' => $node->get('body')->value ?? '',
      'status' => $node->isPublished() ? 1 : 0,
      'created' => $node->getCreatedTime(),
      'updated' => $node->getChangedTime(),
      'author' => $node->getOwner()->getAccountName(),
      'field_ods' => $ods,
      'field_research_categories' => $categories,
      'url' => $node->toUrl('canonical', ['absolute' => TRUE])->toString(),
    ];
  }
}
