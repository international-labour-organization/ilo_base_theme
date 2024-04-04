<?php

declare(strict_types=1);

namespace Drupal\Tests\ilo_base_theme\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Smoke test asserting that components are actually loaded, or correctly ignored.
 */
class SmokeTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'system',
    'ilo_base_theme_companion',
    'components',
    'ui_patterns_library',
    'ui_patterns_settings',
    'ilo_base_theme_companion',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that components are correctly loaded, or correctly ignored.
   */
  public function testComponents(): void {
    // Create a user that can access the patterns page.
    $user = $this->drupalCreateUser([
      'access patterns page',
    ]);

    $this->drupalLogin($user);
    $session = $this->getSession();
    $session->visit('/patterns');
    $this->assertEquals(200, $session->getStatusCode());

    $content = $session->getPage()->getContent();
    // Assert that global index.css is correctly loaded.
    $this->assertStringContainsString('<link rel="stylesheet" media="all" href="/themes/custom/ilo_base_theme/modules/ilo_base_theme_companion/dist/index.css', $content);
    // Assert that "ilo" prefix and "theme" (dark/light) are correctly handled.
    $this->assertStringContainsString('<div class="ilo--card ilo--card__type__feature ilo--card__action ilo--card__theme__dark ilo--card__size__standard', $content);
    // Assert that forms are not loaded as patterns.
    $session->visit('/patterns/checkbox');
    $this->assertEquals(500, $session->getStatusCode());
  }

}
