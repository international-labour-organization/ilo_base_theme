<?php

declare(strict_types=1);

namespace Drupal\Tests\ilo_base_theme\Traits;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Helper rendering trait.
 */
trait RenderTrait {

  /**
   * Run various assertion on given HTML string via CSS selectors.
   *
   * Specifically:
   *
   * - 'count': assert how many times the given HTML elements occur.
   * - 'equals': assert content of given HTML elements.
   * - 'contains': assert content contained in given HTML elements.
   *
   * Assertions array has to be provided in the following format:
   *
   * [
   *   'count' => [
   *     '.page-header' => 1,
   *   ],
   *   'equals' => [
   *     '.page-header__tagline' => 'Lorem ipsum',
   *   ],
   *   'contains' => [
   *     'Lorem',
   *   ],
   * ]
   *
   * @param string $html
   *   A render array.
   * @param array $assertions
   *   Test assertions.
   * @param string $case
   *   Short description of current test case.
   */
  protected function assertRendering(string $html, array $assertions, string $case): void {
    $crawler = new Crawler($html);

    // Assert presence of given strings.
    if (isset($assertions['contains'])) {
      foreach ($assertions['contains'] as $string) {
        $message = "{$case}: String '{$string}' not found in:" . PHP_EOL . $html;
        $this->assertStringContainsString($string, $html, $message);
      }
    }

    // Assert occurrences of given elements.
    if (isset($assertions['count'])) {
      foreach ($assertions['count'] as $name => $expected) {
        $message = "{$case}: Wrong number of occurrences found for element '{$name}' in:" . PHP_EOL . $html;
        $this->assertCount($expected, $crawler->filter($name), $message);
      }
    }

    // Assert that a given element content equals a given string.
    if (isset($assertions['equals'])) {
      foreach ($assertions['equals'] as $name => $expected) {
        try {
          $actual = trim($crawler->filter($name)->html());
        }
        catch (\InvalidArgumentException $exception) {
          $this->fail(sprintf('%s: Element "%s" not found (exception: "%s") in: ' . PHP_EOL . ' %s', $case, $name, $exception->getMessage(), $html));
        }
        $this->assertEquals($expected, $actual);
      }
    }

  }

  /**
   * Renders final HTML given a structured array tree.
   *
   * @param array $elements
   *   The structured array describing the data to be rendered.
   *
   * @return string
   *   The rendered HTML.
   *
   * @throws \Exception
   *   When called from inside another renderRoot() call.
   *
   * @see \Drupal\Core\Render\RendererInterface::render()
   */
  protected function renderRoot(array &$elements): string {
    return (string) $this->container->get('renderer')->renderRoot($elements);
  }

}
