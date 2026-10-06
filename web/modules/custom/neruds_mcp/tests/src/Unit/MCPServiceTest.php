<?php

namespace Drupal\Tests\neruds_mcp\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\neruds_mcp\Service\MCPService;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Tests for MCPService.
 *
 * @group neruds_mcp
 * @coversDefaultClass \Drupal\neruds_mcp\Service\MCPService
 */
class MCPServiceTest extends UnitTestCase {

  /**
   * Mock entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Mock logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Mock config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The MCP service under test.
   *
   * @var \Drupal\neruds_mcp\Service\MCPService
   */
  protected $mcpService;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->loggerFactory = $this->createMock(LoggerChannelFactoryInterface::class);
    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);

    $this->mcpService = new MCPService(
      $this->entityTypeManager,
      $this->loggerFactory,
      $this->configFactory
    );
  }

  /**
   * Tests creating a node with valid data.
   *
   * @covers ::createNode
   */
  public function testCreateNodeWithValidData() {
    $data = [
      'type' => 'news',
      'title' => 'Test News',
      'body' => 'Test content',
      'status' => 1,
      'field_ods' => [1, 2, 3],
      'field_research_categories' => [1],
    ];

    $mockNode = $this->createMock(NodeInterface::class);
    $mockNode->expects($this->once())
      ->method('id')
      ->willReturn(123);
    $mockNode->expects($this->once())
      ->method('save');

    $mockStorage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $mockStorage->expects($this->once())
      ->method('create')
      ->willReturn($mockNode);

    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($mockStorage);

    $mockLogger = $this->createMock('Drupal\Core\Logger\LoggerChannelInterface');
    $this->loggerFactory->expects($this->once())
      ->method('get')
      ->with('neruds_mcp')
      ->willReturn($mockLogger);

    $result = $this->mcpService->createNode($data);
    $this->assertNotFalse($result);
    $this->assertEquals(123, $result->id());
  }

  /**
   * Tests creating a node with invalid type.
   *
   * @covers ::createNode
   */
  public function testCreateNodeWithInvalidType() {
    $data = [
      'type' => 'invalid_type',
      'title' => 'Test News',
    ];

    $mockLogger = $this->createMock('Drupal\Core\Logger\LoggerChannelInterface');
    $this->loggerFactory->expects($this->once())
      ->method('get')
      ->with('neruds_mcp')
      ->willReturn($mockLogger);

    $result = $this->mcpService->createNode($data);
    $this->assertFalse($result);
  }

  /**
   * Tests creating a node without title.
   *
   * @covers ::createNode
   */
  public function testCreateNodeMissingTitle() {
    $data = [
      'type' => 'news',
      'body' => 'Test content',
    ];

    $mockLogger = $this->createMock('Drupal\Core\Logger\LoggerChannelInterface');
    $this->loggerFactory->expects($this->once())
      ->method('get')
      ->with('neruds_mcp')
      ->willReturn($mockLogger);

    $result = $this->mcpService->createNode($data);
    $this->assertFalse($result);
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

    $mockStorage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $mockStorage->expects($this->once())
      ->method('load')
      ->with(123)
      ->willReturn($mockNode);

    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($mockStorage);

    $result = $this->mcpService->getNode(123);
    $this->assertNotNull($result);
    $this->assertEquals(123, $result->id());
  }

  /**
   * Tests getting a non-existent node.
   *
   * @covers ::getNode
   */
  public function testGetNodeNotFound() {
    $mockStorage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $mockStorage->expects($this->once())
      ->method('load')
      ->with(999)
      ->willReturn(NULL);

    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($mockStorage);

    $result = $this->mcpService->getNode(999);
    $this->assertNull($result);
  }

  /**
   * Tests updating a node.
   *
   * @covers ::updateNode
   */
  public function testUpdateNode() {
    $mockNode = $this->createMock(NodeInterface::class);
    $mockNode->expects($this->once())
      ->method('setTitle')
      ->with('Updated Title');
    $mockNode->expects($this->once())
      ->method('save');

    $mockStorage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $mockStorage->expects($this->once())
      ->method('load')
      ->with(123)
      ->willReturn($mockNode);

    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($mockStorage);

    $mockLogger = $this->createMock('Drupal\Core\Logger\LoggerChannelInterface');
    $this->loggerFactory->expects($this->once())
      ->method('get')
      ->with('neruds_mcp')
      ->willReturn($mockLogger);

    $data = ['title' => 'Updated Title'];
    $result = $this->mcpService->updateNode(123, $data);
    $this->assertNotFalse($result);
  }

  /**
   * Tests updating a non-existent node.
   *
   * @covers ::updateNode
   */
  public function testUpdateNodeNotFound() {
    $mockStorage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $mockStorage->expects($this->once())
      ->method('load')
      ->with(999)
      ->willReturn(NULL);

    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($mockStorage);

    $mockLogger = $this->createMock('Drupal\Core\Logger\LoggerChannelInterface');
    $this->loggerFactory->expects($this->once())
      ->method('get')
      ->with('neruds_mcp')
      ->willReturn($mockLogger);

    $result = $this->mcpService->updateNode(999, ['title' => 'Test']);
    $this->assertFalse($result);
  }

  /**
   * Tests deleting a node.
   *
   * @covers ::deleteNode
   */
  public function testDeleteNode() {
    $mockNode = $this->createMock(NodeInterface::class);
    $mockNode->expects($this->once())
      ->method('delete');

    $mockStorage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $mockStorage->expects($this->once())
      ->method('load')
      ->with(123)
      ->willReturn($mockNode);

    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($mockStorage);

    $mockLogger = $this->createMock('Drupal\Core\Logger\LoggerChannelInterface');
    $this->loggerFactory->expects($this->once())
      ->method('get')
      ->with('neruds_mcp')
      ->willReturn($mockLogger);

    $result = $this->mcpService->deleteNode(123);
    $this->assertTrue($result);
  }

  /**
   * Tests listing nodes by type.
   *
   * @covers ::listNodes
   */
  public function testListNodesByType() {
    $mockNode1 = $this->createMock(NodeInterface::class);
    $mockNode2 = $this->createMock(NodeInterface::class);

    $mockStorage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $mockStorage->expects($this->once())
      ->method('loadMultiple')
      ->with([1, 2])
      ->willReturn([1 => $mockNode1, 2 => $mockNode2]);

    $mockQuery = $this->createMock('Drupal\Core\Entity\Query\QueryInterface');
    $mockQuery->expects($this->exactly(3))
      ->method('condition')
      ->willReturnSelf();
    $mockQuery->expects($this->once())
      ->method('sort')
      ->willReturnSelf();
    $mockQuery->expects($this->once())
      ->method('range')
      ->willReturnSelf();
    $mockQuery->expects($this->once())
      ->method('execute')
      ->willReturn([1, 2]);

    $mockStorage->expects($this->once())
      ->method('getQuery')
      ->willReturn($mockQuery);

    $this->entityTypeManager->expects($this->exactly(2))
      ->method('getStorage')
      ->with('node')
      ->willReturn($mockStorage);

    $result = $this->mcpService->listNodes('news', [], 20);
    $this->assertCount(2, $result);
  }

  /**
   * Tests limit enforcement in listNodes.
   *
   * @covers ::listNodes
   */
  public function testListNodesMaxLimit() {
    $mockStorage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $mockQuery = $this->createMock('Drupal\Core\Entity\Query\QueryInterface');
    $mockQuery->expects($this->exactly(3))
      ->method('condition')
      ->willReturnSelf();
    $mockQuery->expects($this->once())
      ->method('sort')
      ->willReturnSelf();
    $mockQuery->expects($this->once())
      ->method('range')
      ->with(0, 100)
      ->willReturnSelf();
    $mockQuery->expects($this->once())
      ->method('execute')
      ->willReturn([]);

    $mockStorage->expects($this->once())
      ->method('getQuery')
      ->willReturn($mockQuery);
    $mockStorage->expects($this->once())
      ->method('loadMultiple')
      ->with([])
      ->willReturn([]);

    $this->entityTypeManager->expects($this->exactly(2))
      ->method('getStorage')
      ->with('node')
      ->willReturn($mockStorage);

    $result = $this->mcpService->listNodes('news', [], 150);
    $this->assertIsArray($result);
  }

  /**
   * Tests getting ODS terms.
   *
   * @covers ::getODSTerms
   */
  public function testGetODSTerms() {
    $mockTerm = $this->createMock(TermInterface::class);
    $mockTerm->expects($this->once())
      ->method('id')
      ->willReturn(1);
    $mockTerm->expects($this->once())
      ->method('getName')
      ->willReturn('ODS 1: No Poverty');
    $mockTerm->expects($this->once())
      ->method('get')
      ->with('field_ods_color')
      ->willReturn((object) ['value' => '#E5243B']);

    $mockStorage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $mockStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['vid' => 'ods'])
      ->willReturn([1 => $mockTerm]);

    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('taxonomy_term')
      ->willReturn($mockStorage);

    $result = $this->mcpService->getODSTerms();
    $this->assertCount(1, $result);
    $this->assertEquals('ODS 1: No Poverty', $result[0]['name']);
    $this->assertEquals('#E5243B', $result[0]['color']);
  }

  /**
   * Tests getting research categories.
   *
   * @covers ::getResearchCategories
   */
  public function testGetResearchCategories() {
    $mockTerm = $this->createMock(TermInterface::class);
    $mockTerm->expects($this->once())
      ->method('id')
      ->willReturn(1);
    $mockTerm->expects($this->once())
      ->method('getName')
      ->willReturn('Rural Studies');

    $mockStorage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $mockStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['vid' => 'research_categories'])
      ->willReturn([1 => $mockTerm]);

    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('taxonomy_term')
      ->willReturn($mockStorage);

    $result = $this->mcpService->getResearchCategories();
    $this->assertCount(1, $result);
    $this->assertEquals('Rural Studies', $result[0]['name']);
  }

}
