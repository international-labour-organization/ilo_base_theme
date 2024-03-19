<?php

namespace Drupal\ilo_base_theme_companion\Plugin\UiPatterns\Pattern;

use Drupal\ui_patterns_library\Plugin\UiPatterns\Pattern\LibraryPattern;

/**
 * Pattern defined as an ILO design system component.
 *
 * @UiPattern(
 *   id = "ilo_design_system",
 *   label = @Translation("ILO Deisgn System"),
 *   description = @Translation("Pattern defined as an ILO design system component."),
 *   deriver = "\Drupal\ilo_base_theme_companion\Plugin\Deriver\DesignSystemDeriver"
 * )
 */
class DesignSystemPattern extends LibraryPattern {

  /**
   * {@inheritdoc}
   */
  protected function templateExists($directory, $template) {
    return file_exists($directory . DIRECTORY_SEPARATOR . $template . '.twig');
  }

}
