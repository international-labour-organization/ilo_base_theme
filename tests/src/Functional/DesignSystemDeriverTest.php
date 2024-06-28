<?php

declare(strict_types=1);

namespace Drupal\Tests\ilo_base_theme\Functional;

use Drupal\Core\Render\Markup;
use Drupal\ilo_base_theme_companion\ComponentsLocatorInterface;
use Drupal\ilo_base_theme_companion\Plugin\Deriver\DesignSystemDeriver;

/**
 * Test deriver for ILO design system.
 */
class DesignSystemDeriverTest extends BrowserTestBase {

  /**
   * Tests deriver.
   */
  public function testDeriver(): void {
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

    $deriver = DesignSystemDeriver::create($this->container, '');
    $patterns = $deriver->getPatterns();
    $this->assertCount(2, $patterns);

    // Assert "bar" component.
    $definition = $patterns[0]->toArray();
    $this->assertEquals($definition['libraries'], [
      [
        'bar' => [
          'dependencies' => [
            'ilo_base_theme_companion/global',
          ],
        ],
      ],
    ]);

    // Assert "foo" component.
    $definition = $patterns[1]->toArray();
    $this->assertEquals($definition['libraries'], [
      [
        'foo' => [
          'dependencies' => [
            'ilo_base_theme_companion/global',
            'core/drupal',
            'core/drupalSettings',
          ],
          'js' => [
            'foo.behavior.js' => NULL,
          ],
        ],
      ],
    ]);

    $this->assertEquals($definition['fields'], [
      'list' => [
        'type' => 'array',
        'label' => 'List',
        'preview' => [
          [
            'label' => 'Item 1',
            'content' => Markup::create('<span>content 1</span>'),
          ],
          [
            'label' => 'Item 2',
            'content' => Markup::create('<span>content 2</span>'),
          ],
        ],
        'name' => 'list',
        'description' => NULL,
        'escape' => TRUE,
        'additional' => [],
      ],
      'image' => [
        'type' => 'image',
        'label' => 'Image',
        'preview' => 'https://twig.ui.ilo.org/images/foo.png',
        'name' => 'image',
        'description' => NULL,
        'escape' => TRUE,
        'additional' => [],
      ],
    ]);
  }

}
