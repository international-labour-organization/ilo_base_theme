<?php

declare(strict_types=1);

namespace Drupal\Tests\ilo_base_theme\Functional;

use Drupal\Core\Render\Markup;
use Drupal\ilo_base_theme_companion\Plugin\Deriver\DesignSystemDeriver;

/**
 * Test pattern preview module.
 */
class PatternPreviewTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ilo_base_theme_preview',
    'ilo_base_theme_patterns_test',
  ];

  /**
   * Tests pattern preview route.
   */
  public function testPreviewRoute(): void {
    $session = $this->getSession();
    $session->visit('/pattern-preview');
    $this->assertEquals(200, $session->getStatusCode());

    $content = $this->drupalGet('pattern-preview', [
      'query' => [
        'id' => 'foo',
        'fields' => urlencode(json_encode([
          'label' => 'bar',
        ]))
      ],
    ]);
    $this->assertStringContainsString('Variant: none Label: bar', $content);

    $content = $this->drupalGet('pattern-preview', [
      'query' => [
        'id' => 'foo',
        'variant' => 'one',
        'fields' => urlencode(json_encode([
          'label' => 'bar',
        ]))
      ],
    ]);
    $this->assertStringContainsString('Variant: one Label: bar', $content);
  }

}
