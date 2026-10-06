<?php

namespace Drupal\neruds_notifications\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for notifications unsubscribe action.
 */
class NotificationsController extends ControllerBase {

  /**
   * Unsubscribe user via token.
   */
  public function unsubscribe($token) {
    if (empty($token)) {
      return ['#markup' => $this->t('Token inválido.')];
    }

    $db = \Drupal::database();
    $row = $db->select('neruds_notification_prefs', 'n')
      ->fields('n', ['uid'])
      ->condition('n.token', $token)
      ->execute()
      ->fetchObject();

    if (!$row) {
      return ['#markup' => $this->t('Token inválido ou já processado.')];
    }

    $db->update('neruds_notification_prefs')
      ->fields([
        'notify_new_content' => 0,
        'notify_comments' => 0,
        'notify_digest' => 'none',
        'updated' => time(),
      ])
      ->condition('token', $token)
      ->execute();

    $this->messenger()->addMessage(
      $this->t('Você foi removido de todas as listas de notificação do NERUDS.')
    );

    return [
      '#markup' => '<div class="messages messages--status">' .
        $this->t('Inscrições canceladas com sucesso. Você não receberá mais notificações do NERUDS.') .
        '<br><a href="/">' . $this->t('Voltar ao portal') . '</a></div>',
    ];
  }

}
