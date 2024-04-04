<?php

namespace Drupal\ilo_base_theme_companion;

/**
 * Interface for the components locator service.
 */
interface ComponentsLocatorInterface {

  /**
   * Returns location of the components.
   *
   * @return string
   *   Full path to components directory.
   */
  public function getComponentDirectory(): string;

}
