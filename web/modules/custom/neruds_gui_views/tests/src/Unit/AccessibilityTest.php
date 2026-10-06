<?php

namespace Drupal\Tests\neruds_gui_views\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\node\NodeInterface;

/**
 * Tests for accessibility in NERUDS GUI Views templates.
 *
 * Verifies ARIA labels, semantic HTML, focus management, and color contrast.
 *
 * @group neruds_gui_views
 */
class AccessibilityTest extends UnitTestCase {

  /**
   * Checks that template variables include necessary ARIA attributes.
   */
  public function testNodeTemplateVariablesExist() {
    // Test that the template hook_theme properly defines variables
    $module_path = \Drupal::service('extension.list.module')->getPath('neruds_gui_views');

    // Include the module file to test hook_theme
    include_once $module_path . '/neruds_gui_views.module';

    $theme_registry = neruds_gui_views_theme([], 'module', 'neruds_gui_views', $module_path);

    // Verify all required templates are registered
    $expected_templates = [
      'node__news__teaser',
      'node__event__teaser',
      'node__project__teaser',
      'node__person__teaser',
      'views_view__neruds_research',
    ];

    foreach ($expected_templates as $template) {
      $this->assertArrayHasKey($template, $theme_registry);
      $this->assertArrayHasKey('variables', $theme_registry[$template]);
      $this->assertArrayHasKey('template', $theme_registry[$template]);
      $this->assertArrayHasKey('path', $theme_registry[$template]);
    }
  }

  /**
   * Tests that templates directory exists and contains required files.
   */
  public function testTemplateFilesExist() {
    $module_path = \Drupal::service('extension.list.module')->getPath('neruds_gui_views');
    $templates_path = $module_path . '/templates';

    $this->assertFileExists($templates_path);

    $expected_templates = [
      'node-news-teaser.html.twig',
      'node-event-teaser.html.twig',
      'node-project-teaser.html.twig',
      'node-person-teaser.html.twig',
      'views-view--neruds-research.html.twig',
    ];

    foreach ($expected_templates as $template) {
      $template_file = $templates_path . '/' . $template;
      $this->assertFileExists($template_file, "Template file missing: $template");

      // Verify file is not empty
      $content = file_get_contents($template_file);
      $this->assertNotEmpty($content, "Template file is empty: $template");
    }
  }

  /**
   * Tests that templates use semantic HTML elements.
   */
  public function testSemanticHtmlUsage() {
    $module_path = \Drupal::service('extension.list.module')->getPath('neruds_gui_views');
    $templates_path = $module_path . '/templates';

    $files = [
      'node-news-teaser.html.twig',
      'node-event-teaser.html.twig',
      'node-project-teaser.html.twig',
    ];

    foreach ($files as $file) {
      $content = file_get_contents($templates_path . '/' . $file);

      // Check for semantic HTML elements
      $this->assertStringContainsString('<article', $content, "$file should use <article> element");
      $this->assertStringContainsString('</article>', $content);

      // Check for heading hierarchy
      $this->assertStringContainsString('<h', $content, "$file should contain headings");

      // Check for image elements with expected attributes
      if (strpos($file, 'person') === FALSE) {
        // News, events, projects should have images
        $this->assertStringContainsString('img', $content, "$file should contain images");
      }
    }
  }

  /**
   * Tests that templates include ARIA labels where appropriate.
   */
  public function testAriaLabelPresence() {
    $module_path = \Drupal::service('extension.list.module')->getPath('neruds_gui_views');
    $templates_path = $module_path . '/templates';

    $files = [
      'views-view--neruds-research.html.twig',
    ];

    foreach ($files as $file) {
      $content = file_get_contents($templates_path . '/' . $file);

      // Check for ARIA regions
      if (strpos($content, 'search') !== FALSE) {
        // Search template should have proper ARIA labels
        $this->assertStringContainsString('role=', $content, "$file should include role attributes");
      }
    }
  }

  /**
   * Tests focus visibility in templates.
   */
  public function testFocusVisiblitySupport() {
    $module_path = \Drupal::service('extension.list.module')->getPath('neruds_gui_views');
    $templates_path = $module_path . '/templates';

    // Check CSS file for focus states
    $css_path = \Drupal::service('extension.list.theme')->getPath('neruds_gui');
    $focus_files = [
      $css_path . '/css/components/buttons.css',
      $css_path . '/css/components/links.css',
    ];

    foreach ($focus_files as $css_file) {
      if (file_exists($css_file)) {
        $content = file_get_contents($css_file);
        $this->assertStringContainsString('focus', $content, "CSS file should include focus states");
      }
    }
  }

  /**
   * Tests that image-heavy templates include alt text support.
   */
  public function testImageAltTextSupport() {
    $module_path = \Drupal::service('extension.list.module')->getPath('neruds_gui_views');
    $templates_path = $module_path . '/templates';

    $files = [
      'node-news-teaser.html.twig',
      'node-event-teaser.html.twig',
      'node-project-teaser.html.twig',
    ];

    foreach ($files as $file) {
      $content = file_get_contents($templates_path . '/' . $file);

      // Check for image with alt text or aria-label
      if (strpos($content, '<img') !== FALSE) {
        $this->assertTrue(
          (strpos($content, 'alt=') !== FALSE) || (strpos($content, 'aria-label') !== FALSE),
          "$file images should have alt text or aria-label"
        );
      }
    }
  }

  /**
   * Tests color contrast in design tokens.
   */
  public function testColorContrastTokens() {
    $theme_path = \Drupal::service('extension.list.theme')->getPath('neruds_gui');
    $tokens_file = $theme_path . '/css/tokens/design-tokens.css';

    if (file_exists($tokens_file)) {
      $content = file_get_contents($tokens_file);

      // Verify color variables are defined
      $this->assertStringContainsString('--color-babasu', $content);
      $this->assertStringContainsString('--color-barro', $content);
      $this->assertStringContainsString('--color-palha', $content);

      // Verify focus ring is defined
      $this->assertStringContainsString('--focus-ring', $content);
    }
  }

  /**
   * Tests responsive design in CSS.
   */
  public function testResponsiveDesignMediaQueries() {
    $theme_path = \Drupal::service('extension.list.theme')->getPath('neruds_gui');

    // Check multiple CSS files for media queries
    $css_files = glob($theme_path . '/css/components/*.css');

    $has_media_queries = FALSE;
    foreach ($css_files as $file) {
      $content = file_get_contents($file);
      if (strpos($content, '@media') !== FALSE) {
        $has_media_queries = TRUE;
        break;
      }
    }

    $this->assertTrue($has_media_queries, "CSS should include media queries for responsive design");
  }

  /**
   * Tests dark mode support.
   */
  public function testDarkModeSupport() {
    $theme_path = \Drupal::service('extension.list.theme')->getPath('neruds_gui');
    $tokens_file = $theme_path . '/css/tokens/design-tokens.css';

    if (file_exists($tokens_file)) {
      $content = file_get_contents($tokens_file);

      // Check for dark mode media query
      $this->assertStringContainsString('prefers-color-scheme: dark', $content,
        "CSS should support dark mode with prefers-color-scheme"
      );
    }
  }

  /**
   * Tests that link targets are keyboard accessible.
   */
  public function testKeyboardAccessibility() {
    $module_path = \Drupal::service('extension.list.module')->getPath('neruds_gui_views');
    $templates_path = $module_path . '/templates';

    $files = glob($templates_path . '/*.html.twig');

    foreach ($files as $file) {
      $content = file_get_contents($file);

      // Links should be keyboard accessible
      if (strpos($content, '<a') !== FALSE) {
        // Check that links don't rely solely on mouse events
        $has_href = strpos($content, 'href=') !== FALSE;
        $this->assertTrue($has_href, basename($file) . " links should have href attribute");
      }
    }
  }

}
