<?php

namespace Drupal\neruds_analytics\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for NERUDS analytics dashboard.
 */
class AnalyticsDashboardController extends ControllerBase {

  protected $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('database'));
  }

  /**
   * Renders the analytics dashboard.
   */
  public function dashboard() {
    $stats = $this->getStats();

    return [
      '#theme' => 'neruds_analytics_dashboard',
      '#stats' => $stats,
      '#cache' => [
        'max-age' => 3600,
        'tags' => ['node_list'],
      ],
    ];
  }

  /**
   * Compile portal statistics.
   */
  protected function getStats() {
    $content_types = [
      'noticia' => 'Notícias',
      'publicacao_cientifica' => 'Publicações Científicas',
      'publicacao' => 'Publicações',
      'projeto_pesquisa_extensao' => 'Projetos',
      'evento_cientifico' => 'Eventos Científicos',
      'perfil_pesquisador' => 'Pesquisadores',
      'reuniao' => 'Reuniões',
      'relatorio' => 'Relatórios',
      'boletim' => 'Boletins',
      'grupo_estudos' => 'Grupos de Estudos',
    ];

    $now = time();
    $month_start = strtotime('first day of this month midnight');
    $year_start = strtotime('first day of january midnight');

    $totals = [];
    $recent = [];
    $monthly = [];

    foreach ($content_types as $type => $label) {
      // Total published
      $count = $this->database->query(
        "SELECT COUNT(*) FROM {node_field_data} WHERE type = :type AND status = 1",
        [':type' => $type]
      )->fetchField();

      // Recent this month
      $month_count = $this->database->query(
        "SELECT COUNT(*) FROM {node_field_data} WHERE type = :type AND status = 1 AND created >= :start",
        [':type' => $type, ':start' => $month_start]
      )->fetchField();

      if ((int)$count > 0) {
        $totals[] = [
          'type' => $type,
          'label' => $label,
          'total' => (int)$count,
          'month' => (int)$month_count,
        ];
      }
    }

    // Sort by total descending
    usort($totals, fn($a, $b) => $b['total'] - $a['total']);

    // Total content
    $total_all = $this->database->query(
      "SELECT COUNT(*) FROM {node_field_data} WHERE status = 1"
    )->fetchField();

    // Total researchers
    $total_researchers = $this->database->query(
      "SELECT COUNT(*) FROM {node_field_data} WHERE type = 'perfil_pesquisador' AND status = 1"
    )->fetchField();

    // Total users
    $total_users = $this->database->query(
      "SELECT COUNT(*) FROM {users_field_data} WHERE status = 1 AND uid > 0"
    )->fetchField();

    // Recent content (last 10)
    $recent_nodes = $this->database->query(
      "SELECT nid, title, type, created FROM {node_field_data} WHERE status = 1 ORDER BY created DESC LIMIT 10"
    )->fetchAll();

    // Content by month (last 6 months)
    $monthly_data = [];
    for ($i = 5; $i >= 0; $i--) {
      $month_ts = strtotime("-{$i} months", strtotime('first day of this month midnight'));
      $next_month_ts = strtotime('+1 month', $month_ts);
      $month_label = date('M/Y', $month_ts);
      $month_count = $this->database->query(
        "SELECT COUNT(*) FROM {node_field_data} WHERE status = 1 AND created >= :start AND created < :end",
        [':start' => $month_ts, ':end' => $next_month_ts]
      )->fetchField();
      $monthly_data[] = ['month' => $month_label, 'count' => (int)$month_count];
    }

    // ODS distribution
    $ods_data = $this->database->query(
      "SELECT t.name, COUNT(r.entity_id) as count
       FROM {taxonomy_term_field_data} t
       JOIN {node__field_ods} r ON r.field_ods_target_id = t.tid
       JOIN {node_field_data} n ON n.nid = r.entity_id AND n.status = 1
       WHERE t.vid = 'ods'
       GROUP BY t.tid, t.name
       ORDER BY count DESC
       LIMIT 10"
    )->fetchAll();

    return [
      'totals' => $totals,
      'total_all' => (int)$total_all,
      'total_researchers' => (int)$total_researchers,
      'total_users' => (int)$total_users,
      'recent_nodes' => $recent_nodes,
      'monthly_data' => $monthly_data,
      'ods_data' => $ods_data,
      'generated' => date('d/m/Y H:i'),
    ];
  }

}
