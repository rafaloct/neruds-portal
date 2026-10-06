<?php

declare(strict_types=1);

namespace Drupal\neruds_google_integration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\neruds_google_integration\Service\PortalDataService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the NERUDS live dashboard block.
 *
 * @Block(
 *   id = "neruds_live_dashboard",
 *   admin_label = @Translation("NERUDS live dashboard")
 * )
 */
final class LiveDashboardBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, private readonly PortalDataService $portalData) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self($configuration, $plugin_id, $plugin_definition, $container->get('neruds_google_integration.portal_data'));
  }

  public function build(): array {
    $dashboard = $this->portalData->getDashboard();
    return [
      '#theme' => 'neruds_live_dashboard',
      '#title' => $this->t('NERUDS em numeros'),
      '#metrics' => $dashboard['metrics'] ?? [],
      '#attached' => [
        'library' => ['neruds_google_integration/live_dashboard'],
        'drupalSettings' => [
          'nerudsLiveDashboard' => [
            'endpoint' => '/neruds/api/dashboard',
          ],
        ],
      ],
      '#cache' => [
        'max-age' => 300,
        'tags' => ['node_list', 'user_list', 'taxonomy_term_list', 'neruds_google_integration'],
      ],
    ];
  }

}
