<?php

declare(strict_types=1);

namespace Drupal\Tests\ilo_base_theme\Functional;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
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

    // "foo" includes "bar", which already depends on "foo": expect a warning.
    $logger = $this->createMock(LoggerChannelInterface::class);
    $logger->expects($this->once())
      ->method('warning')
      ->with($this->stringContains('Circular dependency skipped'), [
        '@id' => 'foo',
        '@dependency' => 'bar',
      ]);
    $logger_factory = $this->createMock(LoggerChannelFactoryInterface::class);
    $logger_factory->method('get')
      ->with('ilo_base_theme_companion')
      ->willReturn($logger);

    // Only give the mocked factory to the deriver.
    $original_logger_factory = $this->container->get('logger.factory');
    $this->container->set('logger.factory', $logger_factory);
    $deriver = DesignSystemDeriver::create($this->container, '');
    $this->container->set('logger.factory', $original_logger_factory);
    $patterns = $deriver->getPatterns();
    $this->assertCount(2, $patterns);

    // Assert "bar" component, which depends on "foo" since one of its
    // templates includes it. References to its own templates and to
    // components that are not patterns are ignored.
    $definition = $patterns[0]->toArray();
    $this->assertEquals($definition['libraries'], [
      [
        'bar' => [
          'dependencies' => [
            'ilo_base_theme_companion/global',
            'ui_patterns/foo.foo',
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
