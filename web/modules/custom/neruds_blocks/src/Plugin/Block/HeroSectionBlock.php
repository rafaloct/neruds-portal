<?php

namespace Drupal\neruds_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a configurable Hero Section block with 4 layout variants.
 *
 * Variants:
 *  - centered: Full-width background + centered text
 *  - split: 50/50 image + text side by side
 *  - overlay: Image background + overlay text at bottom
 *  - gradient: Subtle gradient + icon/stats emphasis
 *
 * @Block(
 *   id = "neruds_hero_section",
 *   admin_label = @Translation("NERUDS Hero Section"),
 *   category = @Translation("NERUDS"),
 * )
 */
class HeroSectionBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'variant' => 'centered',
      'eyebrow' => '',
      'title' => '',
      'subtitle' => '',
      'description' => '',
      'cta_text' => '',
      'cta_url' => '',
      'cta_secondary_text' => '',
      'cta_secondary_url' => '',
      'image_url' => '',
      'image_alt' => '',
      'stats' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['variant'] = [
      '#type' => 'select',
      '#title' => $this->t('Layout Variant'),
      '#options' => [
        'centered' => $this->t('Centered — Full-width background, text centered'),
        'split' => $this->t('Split — 50/50 image + text side by side'),
        'overlay' => $this->t('Overlay — Image background with overlay text'),
        'gradient' => $this->t('Gradient — Gradient background with stats emphasis'),
      ],
      '#default_value' => $config['variant'],
      '#description' => $this->t('Choose the visual layout for this hero section.'),
    ];

    $form['eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow Text'),
      '#default_value' => $config['eyebrow'],
      '#description' => $this->t('Small text above the title (e.g., "Universidade Federal do Tocantins").'),
      '#maxlength' => 128,
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $config['title'],
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['subtitle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subtitle'),
      '#default_value' => $config['subtitle'],
      '#maxlength' => 255,
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $config['description'],
      '#rows' => 3,
    ];

    $form['cta_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Primary CTA Text'),
      '#default_value' => $config['cta_text'],
      '#maxlength' => 64,
    ];

    $form['cta_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Primary CTA URL'),
      '#default_value' => $config['cta_url'],
    ];

    $form['cta_secondary_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secondary CTA Text'),
      '#default_value' => $config['cta_secondary_text'],
      '#maxlength' => 64,
    ];

    $form['cta_secondary_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Secondary CTA URL'),
      '#default_value' => $config['cta_secondary_url'],
    ];

    $form['image_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Image URL'),
      '#default_value' => $config['image_url'],
      '#description' => $this->t('Path to the hero image (e.g., /sites/default/files/hero.jpg). Used by Split and Overlay variants.'),
      '#maxlength' => 512,
    ];

    $form['image_alt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Image Alt Text'),
      '#default_value' => $config['image_alt'],
      '#maxlength' => 255,
    ];

    $form['stats'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Statistics (one per line)'),
      '#default_value' => $config['stats'],
      '#description' => $this->t('Format: number|label (e.g., "12|Pesquisadores"). One stat per line. Used by Centered and Gradient variants.'),
      '#rows' => 4,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $fields = [
      'variant', 'eyebrow', 'title', 'subtitle', 'description',
      'cta_text', 'cta_url', 'cta_secondary_text', 'cta_secondary_url',
      'image_url', 'image_alt', 'stats',
    ];
    foreach ($fields as $field) {
      $this->configuration[$field] = $form_state->getValue($field);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    // Parse stats from "number|label" format.
    $stats = [];
    if (!empty($config['stats'])) {
      $lines = array_filter(array_map('trim', explode("\n", $config['stats'])));
      foreach ($lines as $line) {
        $parts = array_map('trim', explode('|', $line, 2));
        if (count($parts) === 2) {
          $stats[] = ['value' => $parts[0], 'label' => $parts[1]];
        }
      }
    }

    return [
      '#theme' => 'neruds_hero_section',
      '#variant' => $config['variant'],
      '#eyebrow' => $config['eyebrow'],
      '#title' => $config['title'],
      '#subtitle' => $config['subtitle'],
      '#description' => $config['description'],
      '#cta_text' => $config['cta_text'],
      '#cta_url' => $config['cta_url'],
      '#cta_secondary_text' => $config['cta_secondary_text'],
      '#cta_secondary_url' => $config['cta_secondary_url'],
      '#image_url' => $config['image_url'],
      '#image_alt' => $config['image_alt'],
      '#stats' => $stats,
      '#attached' => [
        'library' => ['neruds_gui/hero-variants'],
      ],
      '#cache' => [
        'max-age' => 3600,
      ],
    ];
  }

}
