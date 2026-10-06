<?php

/**
 * @file
 * Configurações do tema NERUDS-GUI — identidade visual, logo e favicon.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function neruds_gui_form_system_theme_settings_alter(array &$form, FormStateInterface $form_state): void {

  $theme = 'neruds_gui';

  // ── Preview da logo atual ────────────────────────────────────────────
  $logo_path = theme_get_setting('logo.path', $theme);
  if (!$logo_path) {
    $logo_path = '/' . \Drupal::service('extension.list.theme')->getPath($theme) . '/logo.svg';
  }

  // Insere o preview ANTES dos campos nativos de logo (weight -11)
  $form['logo_preview'] = [
    '#type'   => 'markup',
    '#markup' => '<div style="margin:1rem 0; padding:1rem; background:#f5f5f6; border-radius:8px; display:inline-block;">'
      . '<p style="margin:0 0 0.5rem; font-size:0.8rem; color:#555; font-weight:600;">Logo atual:</p>'
      . '<img src="' . htmlspecialchars($logo_path) . '" alt="Logo NERUDS atual"'
      . ' style="max-height:80px; max-width:300px; display:block; border-radius:4px;">'
      . '</div>',
    '#weight' => -11,
  ];

  // Mantém o campo nativo de logo NO SEU LUGAR (Drupal processa automaticamente)
  if (isset($form['logo'])) {
    $form['logo']['#title']       = t('Logo do Núcleo (NERUDS)');
    $form['logo']['#description'] = t('Formatos aceitos: JPG, PNG, SVG, WebP. Tamanho recomendado: 300×120 px ou proporcional.');
    $form['logo']['#weight']      = -10;
    $form['logo']['#open']        = TRUE;
  }

  // ── Preview do favicon atual ─────────────────────────────────────────
  $favicon_path = theme_get_setting('favicon.path', $theme);
  if (!$favicon_path) {
    $favicon_path = '/' . \Drupal::service('extension.list.theme')->getPath($theme) . '/favicon.ico';
  }

  $form['favicon_preview'] = [
    '#type'   => 'markup',
    '#markup' => '<div style="margin:1rem 0 0.5rem; padding:1rem; background:#f5f5f6; border-radius:8px; display:inline-block;">'
      . '<p style="margin:0 0 0.5rem; font-size:0.8rem; color:#555; font-weight:600;">Favicon atual:</p>'
      . '<img src="' . htmlspecialchars($favicon_path) . '" alt="Favicon atual"'
      . ' style="width:32px; height:32px; display:block; image-rendering:pixelated;">'
      . '</div>',
    '#weight' => 4,
  ];

  // Mantém o campo nativo de favicon NO SEU LUGAR
  if (isset($form['favicon'])) {
    $form['favicon']['#title']       = t('Favicon do Site');
    $form['favicon']['#description'] = t('Ícone exibido na aba do navegador. Formatos: ICO, PNG (32×32 px recomendado).');
    $form['favicon']['#weight']      = 5;
    $form['favicon']['#open']        = TRUE;
  }

  // ── SEÇÃO: CONFIGURAÇÕES VISUAIS ─────────────────────────────────────
  $form['neruds_visual'] = [
    '#type'   => 'details',
    '#title'  => t('Aparência e Esquema de Cores'),
    '#open'   => FALSE,
    '#weight' => 20,
  ];

  $form['neruds_visual']['color_scheme'] = [
    '#type'          => 'select',
    '#title'         => t('Esquema de cores'),
    '#default_value' => theme_get_setting('color_scheme') ?? 'light',
    '#options'       => [
      'light' => t('Claro (padrão)'),
      'dark'  => t('Escuro'),
      'auto'  => t('Automático (segue o sistema)'),
    ],
  ];

  $form['neruds_visual']['enable_glass_effects'] = [
    '#type'          => 'checkbox',
    '#title'         => t('Ativar efeitos de glassmorphism'),
    '#description'   => t('Habilita backdrop-filter nos elementos de vidro. Pode impactar performance em dispositivos lentos.'),
    '#default_value' => theme_get_setting('enable_glass_effects') ?? TRUE,
  ];
}
