<?php

namespace Drupal\ilo_base_theme_companion\Plugin\Deriver;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\ui_patterns\Plugin\Deriver\AbstractYamlPatternsDeriver;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Patterns deriver for the ILO design system.
 */
class DesignSystemDeriver extends AbstractYamlPatternsDeriver {

  /**
   * The app root.
   *
   * @var string
   */
  protected $root;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Constructor.
   *
   * @param string $base_plugin_id
   *   The base plugin ID.
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   Typed data manager service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   File system service.
   * @param string $root
   *   Application root directory.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   Theme handler service.
   */
  public function __construct($base_plugin_id, TypedDataManager $typed_data_manager, MessengerInterface $messenger, FileSystemInterface $file_system, $root, ThemeHandlerInterface $theme_handler) {
    parent::__construct($base_plugin_id, $typed_data_manager, $messenger, $file_system);
    $this->root = $root;
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('typed_data_manager'),
      $container->get('messenger'),
      $container->get('file_system'),
      $container->getParameter('app.root'),
      $container->get('theme_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFileExtensions() {
    return ['.wingsuit.yml'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPatterns() {
    $patterns = [];
    $provider = 'ilo_base_theme';
    $directory = $this->root . DIRECTORY_SEPARATOR . $this->themeHandler->getTheme($provider)->getPath() . DIRECTORY_SEPARATOR . 'dist';
    foreach (array_keys($this->fileScanDirectory($directory)) as $file_path) {
      $content = file_get_contents($file_path);
      foreach (Yaml::decode($content) as $id => $definition) {
        $definition['id'] = $id;
        $definition['base path'] = dirname($file_path);
        $definition['file name'] = basename($file_path);
        $definition['provider'] = $provider;
        $patterns[] = $this->getPatternDefinition($definition);
      }
    }

    return $patterns;
  }

}
