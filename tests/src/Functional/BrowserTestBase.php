<?php

declare(strict_types=1);

namespace Drupal\Tests\ilo_base_theme\Functional;

use Drupal\Core\Render\Markup;
use Drupal\ilo_base_theme_companion\ComponentsLocatorInterface;
use Drupal\ilo_base_theme_companion\Plugin\Deriver\DesignSystemDeriver;
use Drupal\Tests\BrowserTestBase as DrupalBrowserTestBase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Test deriver for ILO design system.
 */
abstract class BrowserTestBase extends DrupalBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'system',
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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Tests are ran as root, here we make sure that Drupal can write as needed.
    $filesystem = new Filesystem();
    foreach ([
               DRUPAL_ROOT . '/sites/default/files',
               DRUPAL_ROOT . '/sites/simpletest',
             ] as $path) {
      $filesystem->chown($path, 'www-data', true);
      $filesystem->chgrp($path, 'www-data', true);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function useFixtureComponents(): void {
    // Mock locator service.
    $locator = new class implements ComponentsLocatorInterface {

      /**
       * {@inheritdoc}
       */
      public function getComponentDirectory(): string {
        return __DIR__ . '/fixtures/components';
      }

    };
    $this->container->set('ilo_base_theme_companion.components_locator', $locator);
  }

}
