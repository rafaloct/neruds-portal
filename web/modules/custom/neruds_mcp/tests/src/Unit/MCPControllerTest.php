<?php

namespace Drupal\Tests\neruds_mcp\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\neruds_mcp\Controller\MCPController;
use Drupal\neruds_mcp\Service\MCPService;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for MCPController.
 *
 * @group neruds_mcp
 * @coversDefaultClass \Drupal\neruds_mcp\Controller\MCPController
 */
class MCPControllerTest extends UnitTestCase {

  /**
   * Mock MCP service.
   *
   * @var \Drupal\neruds_mcp\Service\MCPService
   */
  protected $mcpService;

  /**
   * The MCP controller under test.
   *
   * @var \Drupal\neruds_mcp\Controller\MCPController
   */
  protected $controller;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->mcpService = $this->createMock(MCPService::class);
    $this->controller = new MCPController($this->mcpService);
  }

  /**
   * Tests creating a node via POST request.
   *
   * @covers ::createNode
   */
  public function testCreateNode() {
    $mockNode = $this->createMock(NodeInterface::class);
    $mockNode->expects($this->once())
      ->method('id')
      ->willReturn(123);
    $mockNode->expects($this->once())
      ->method('bundle')
      ->willReturn('news');
    $mockNode->expects($this->once())
      ->method('getTitle')
      ->willReturn('Test News');
    $mockNode->expects($this->once())
      ->method('toUrl')
      ->willReturnSelf();
    $mockNode->expects($this->once())
      ->method('toString')
      ->willReturn('http://example.com/noticias/test-news');

    $this->mcpService->expects($this->once())
      ->method('createNode')
      ->willReturn($mockNode);

    $request = new Request([], [], [], [], [], [], json_encode([
      'type' => 'news',
      'title' => 'Test News',
    ]));

    $response = $this->controller->createNode($request);
    $this->assertEquals(201, $response->getStatusCode());

    $data = json_decode($response->getContent(), TRUE);
    $this->assertTrue($data['success']);
    $this->assertEquals(123, $data['nid']);
    $this->assertEquals('news', $data['type']);
  }

  /**
   * Tests POST request with invalid JSON.
   *
   * @covers ::createNode
   */
  public function testCreateNodeInvalidJson() {
    $request = new Request([], [], [], [], [], [], 'invalid json {');

    $response = $this->controller->createNode($request);
    $this->assertEquals(400, $response->getStatusCode());

    $data = json_decode($response->getContent(), TRUE);
    $this->assertArrayHasKey('error', $data);
  }

  /**
   * Tests POST request when node creation fails.
   *
   * @covers ::createNode
   */
  public function testCreateNodeFailure() {
    $this->mcpService->expects($this->once())
      ->method('createNode')
      ->willReturn(FALSE);

    $request = new Request([], [], [], [], [], [], json_encode([
      'type' => 'news',
      'title' => 'Test News',
    ]));

    $response = $this->controller->createNode($request);
    $this->assertEquals(400, $response->getStatusCode());

    $data = json_decode($response->getContent(), TRUE);
    $this->assertArrayHasKey('error', $data);
  }

  /**
   * Tests getting a single node.
   *
   * @covers ::getNode
   */
  public function testGetNode() {
    $mockNode = $this->createMock(NodeInterface::class);
    $mockNode->expects($this->once())
      ->method('id')
      ->willReturn(123);
    $mockNode->expects($this->once())
      ->method('bundle')
      ->willReturn('news');
    $mockNode->expects($this->once())
      ->method('getTitle')
      ->willReturn('Test News');
    $mockNode->expects($this->any())
      ->method('get')
      ->withAnyParameters()
      ->willReturnSelf();
    $mockNode->expects($this->any())
      ->method('value')
      ->willReturn('Test content');
    $mockNode->expects($this->once())
      ->method('isPublished')
      ->willReturn(TRUE);
    $mockNode->expects($this->once())
      ->method('getCreatedTime')
      ->willReturn(1234567890);
    $mockNode->expects($this->once())
      ->method('getChangedTime')
      ->willReturn(1234567890);
    $mockNode->expects($this->once())
      ->method('getOwner')
      ->willReturnSelf();
    $mockNode->expects($this->once())
      ->method('getAccountName')
      ->willReturn('admin');
    $mockNode->expects($this->once())
      ->method('toUrl')
      ->willReturnSelf();
    $mockNode->expects($this->once())
      ->method('toString')
      ->willReturn('http://example.com/noticias/test-news');

    $this->mcpService->expects($this->once())
      ->method('getNode')
      ->with(123)
      ->willReturn($mockNode);

    $response = $this->controller->getNode(123);
    $this->assertEquals(200, $response->getStatusCode());

    $data = json_decode($response->getContent(), TRUE);
    $this->assertEquals(123, $data['nid']);
    $this->assertEquals('news', $data['type']);
  }

  /**
   * Tests getting a non-existent node.
   *
   * @covers ::getNode
   */
  public function testGetNodeNotFound() {
    $this->mcpService->expects($this->once())
      ->method('getNode')
      ->with(999)
      ->willReturn(NULL);

    $response = $this->controller->getNode(999);
    $this->assertEquals(404, $response->getStatusCode());

    $data = json_decode($response->getContent(), TRUE);
    $this->assertArrayHasKey('error', $data);
  }

  /**
   * Tests updating a node.
   *
   * @covers ::updateNode
   */
  public function testUpdateNode() {
    $mockNode = $this->createMock(NodeInterface::class);
    $mockNode->expects($this->once())
      ->method('id')
      ->willReturn(123);

    $this->mcpService->expects($this->once())
      ->method('updateNode')
      ->with(123, ['title' => 'Updated Title'])
      ->willReturn($mockNode);

    $request = new Request([], [], [], [], [], [], json_encode([
      'title' => 'Updated Title',
    ]));

    $response = $this->controller->updateNode(123, $request);
    $this->assertEquals(200, $response->getStatusCode());

    $data = json_decode($response->getContent(), TRUE);
    $this->assertTrue($data['success']);
    $this->assertEquals(123, $data['nid']);
  }

  /**
   * Tests deleting a node.
   *
   * @covers ::deleteNode
   */
  public function testDeleteNode() {
    $this->mcpService->expects($this->once())
      ->method('deleteNode')
      ->with(123)
      ->willReturn(TRUE);

    $response = $this->controller->deleteNode(123);
    $this->assertEquals(200, $response->getStatusCode());

    $data = json_decode($response->getContent(), TRUE);
    $this->assertTrue($data['success']);
  }

  /**
   * Tests deleting a non-existent node.
   *
   * @covers ::deleteNode
   */
  public function testDeleteNodeNotFound() {
    $this->mcpService->expects($this->once())
      ->method('deleteNode')
      ->with(999)
      ->willReturn(FALSE);

    $response = $this->controller->deleteNode(999);
    $this->assertEquals(400, $response->getStatusCode());

    $data = json_decode($response->getContent(), TRUE);
    $this->assertArrayHasKey('error', $data);
  }

  /**
   * Tests listing nodes.
   *
   * @covers ::listNodes
   */
  public function testListNodes() {
    $mockNode1 = $this->createMock(NodeInterface::class);
    $mockNode1->expects($this->once())
      ->method('id')
      ->willReturn(1);
    $mockNode1->expects($this->any())
      ->method('get')
      ->withAnyParameters()
      ->willReturnSelf();
    $mockNode1->expects($this->any())
      ->method('bundle')
      ->willReturn('news');

    $this->mcpService->expects($this->once())
      ->method('listNodes')
      ->with('news', [], 20)
      ->willReturn([$mockNode1]);

    $request = new Request(['type' => 'news', 'limit' => '20']);
    $response = $this->controller->listNodes($request);
    $this->assertEquals(200, $response->getStatusCode());

    $data = json_decode($response->getContent(), TRUE);
    $this->assertEquals('news', $data['type']);
    $this->assertEquals(1, $data['count']);
  }

  /**
   * Tests getting ODS terms.
   *
   * @covers ::getODSTerms
   */
  public function testGetODSTerms() {
    $this->mcpService->expects($this->once())
      ->method('getODSTerms')
      ->willReturn([
        [
          'id' => 1,
          'name' => 'ODS 1: No Poverty',
          'color' => '#E5243B',
        ],
      ]);

    $response = $this->controller->getODSTerms();
    $this->assertEquals(200, $response->getStatusCode());

    $data = json_decode($response->getContent(), TRUE);
    $this->assertEquals('ods', $data['vocabulary']);
    $this->assertEquals(1, $data['count']);
    $this->assertCount(1, $data['terms']);
  }

  /**
   * Tests getting research categories.
   *
   * @covers ::getCategories
   */
  public function testGetCategories() {
    $this->mcpService->expects($this->once())
      ->method('getResearchCategories')
      ->willReturn([
        [
          'id' => 1,
          'name' => 'Rural Studies',
        ],
      ]);

    $response = $this->controller->getCategories();
    $this->assertEquals(200, $response->getStatusCode());

    $data = json_decode($response->getContent(), TRUE);
    $this->assertEquals('research_categories', $data['vocabulary']);
    $this->assertEquals(1, $data['count']);
  }

}
