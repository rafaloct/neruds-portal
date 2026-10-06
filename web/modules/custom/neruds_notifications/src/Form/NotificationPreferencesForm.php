<?php

namespace Drupal\neruds_notifications\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;

/**
 * User notification preferences form.
 */
class NotificationPreferencesForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'neruds_notification_preferences';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, UserInterface $user = NULL) {
    if (!$user) {
      return [];
    }

    $uid = $user->id();
    $current_user = \Drupal::currentUser();

    // Only allow user themselves or admin
    if ($current_user->id() != $uid && !$current_user->hasPermission('administer users')) {
      return ['#markup' => $this->t('Acesso negado.')];
    }

    $prefs = \Drupal::database()->select('neruds_notification_prefs', 'n')
      ->fields('n')
      ->condition('n.uid', $uid)
      ->execute()
      ->fetchObject();

    $form['#tree'] = TRUE;

    $form['intro'] = [
      '#type' => 'markup',
      '#markup' => '<p class="notification-prefs__intro">' . $this->t('Configure quais notificações você deseja receber do portal NERUDS.') . '</p>',
    ];

    $form['notifications'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Tipo de Notificações'),
      '#attributes' => ['class' => ['notification-prefs__fieldset']],
    ];

    $form['notifications']['notify_new_content'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Novas publicações nos meus temas de interesse'),
      '#default_value' => $prefs ? $prefs->notify_new_content : 1,
    ];

    $form['notifications']['notify_comments'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Comentários nas minhas publicações'),
      '#default_value' => $prefs ? $prefs->notify_comments : 1,
    ];

    $form['digest'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Frequência de Resumo'),
      '#attributes' => ['class' => ['notification-prefs__fieldset']],
    ];

    $form['digest']['notify_digest'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enviar resumo de atividades'),
      '#options' => [
        'none' => $this->t('Nunca'),
        'daily' => $this->t('Diariamente'),
        'weekly' => $this->t('Semanalmente'),
      ],
      '#default_value' => $prefs ? $prefs->notify_digest : 'weekly',
    ];

    // ODS filter
    $ods_terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties(['vid' => 'ods']);

    $ods_options = [];
    foreach ($ods_terms as $term) {
      $ods_options[$term->id()] = $term->getName();
    }

    if (!empty($ods_options)) {
      $form['ods_filter'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Filtrar por ODS (Objetivos de Desenvolvimento Sustentável)'),
        '#description' => $this->t('Deixe em branco para receber notificações de todos os ODS.'),
        '#attributes' => ['class' => ['notification-prefs__fieldset']],
      ];

      $user_ods = $prefs && $prefs->ods_filter ? unserialize($prefs->ods_filter) : [];
      $form['ods_filter']['ods_ids'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('ODS de Interesse'),
        '#options' => $ods_options,
        '#default_value' => $user_ods,
        '#attributes' => ['class' => ['ods-filter-checkboxes']],
      ];
    }

    // Content type filter
    $form['type_filter'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Tipos de Conteúdo'),
      '#description' => $this->t('Deixe em branco para receber notificações de todos os tipos.'),
      '#attributes' => ['class' => ['notification-prefs__fieldset']],
    ];

    $type_options = [
      'noticia' => $this->t('Notícias'),
      'publicacao_cientifica' => $this->t('Publicações Científicas'),
      'publicacao' => $this->t('Publicações'),
      'projeto_pesquisa_extensao' => $this->t('Projetos de Pesquisa'),
      'evento_cientifico' => $this->t('Eventos Científicos'),
    ];

    $user_types = $prefs && $prefs->content_types ? unserialize($prefs->content_types) : [];
    $form['type_filter']['content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Tipos de Conteúdo'),
      '#options' => $type_options,
      '#default_value' => $user_types,
    ];

    $form['user_id'] = [
      '#type' => 'value',
      '#value' => $uid,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Salvar Preferências'),
      '#attributes' => ['class' => ['btn', 'btn--primary']],
    ];

    $form['#attached']['library'][] = 'neruds_notifications/preferences';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $uid = $form_state->getValue('user_id');
    $values = $form_state->getValues();

    $ods_ids = array_filter($values['ods_filter']['ods_ids'] ?? []);
    $content_types = array_filter($values['type_filter']['content_types'] ?? []);

    // Get or create token
    $existing = \Drupal::database()->select('neruds_notification_prefs', 'n')
      ->fields('n', ['token'])
      ->condition('n.uid', $uid)
      ->execute()
      ->fetchObject();

    $token = $existing->token ?? bin2hex(random_bytes(32));

    \Drupal::database()->merge('neruds_notification_prefs')
      ->key('uid', $uid)
      ->fields([
        'notify_new_content' => (int) ($values['notifications']['notify_new_content'] ?? 0),
        'notify_comments' => (int) ($values['notifications']['notify_comments'] ?? 0),
        'notify_digest' => $values['digest']['notify_digest'] ?? 'weekly',
        'ods_filter' => serialize(array_values($ods_ids)),
        'content_types' => serialize(array_values($content_types)),
        'token' => $token,
        'updated' => time(),
        'created' => time(),
      ])
      ->execute();

    $this->messenger()->addMessage($this->t('Preferências de notificações salvas com sucesso!'));
  }

}
