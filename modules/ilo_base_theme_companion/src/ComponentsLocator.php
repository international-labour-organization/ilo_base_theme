<?php

namespace Drupal\ilo_base_theme_companion;

use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Service that returns the component location.
 */
class ComponentsLocator implements ComponentsLocatorInterface {

  /**
   * Relative path to the component directory.
   */
  const RELATIVE_PATH = 'dist/components';

  /**
   * Drupal root.
   *
   * @var string
   */
  protected string $root;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * Constructor.
   *
   * @param string $app_root
   *   Drupal root.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler service.
   */
  public function __construct(string $app_root, ModuleHandlerInterface $module_handler) {
    $this->root = $app_root;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function getComponentDirectory(): string {
    $directory = $this->root . DIRECTORY_SEPARATOR . $this->moduleHandler->getModule('ilo_base_theme_companion')->getPath() . DIRECTORY_SEPARATOR . self::RELATIVE_PATH;
    if (!is_dir($directory)) {
      throw new \RuntimeException("ILO design system components not found in {$directory}");
    }
    return $directory;
  }

}
