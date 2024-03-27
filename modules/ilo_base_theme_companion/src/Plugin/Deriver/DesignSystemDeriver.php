<?php

namespace Drupal\ilo_base_theme_companion\Plugin\Deriver;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\ilo_base_theme_companion\ComponentsLocator;
use Drupal\ilo_base_theme_companion\ComponentsLocatorInterface;
use Drupal\ui_patterns\Plugin\Deriver\AbstractYamlPatternsDeriver;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Patterns deriver for the ILO design system.
 */
class DesignSystemDeriver extends AbstractYamlPatternsDeriver {

  /**
   * Components locator service.
   *
   * @var \Drupal\ilo_base_theme_companion\ComponentsLocatorInterface
   */
  protected ComponentsLocatorInterface $componentsLocator;

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
   * @param \Drupal\ilo_base_theme_companion\ComponentsLocator $components_locator
   *   Components locator service.
   */
  public function __construct($base_plugin_id, TypedDataManager $typed_data_manager, MessengerInterface $messenger, FileSystemInterface $file_system, ComponentsLocatorInterface $components_locator) {
    parent::__construct($base_plugin_id, $typed_data_manager, $messenger, $file_system);
    $this->componentsLocator = $components_locator;
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
      $container->get('ilo_base_theme_companion.components_locator')
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
    $directory = $this->componentsLocator->getComponentDirectory();
    foreach (array_keys($this->fileScanDirectory($directory)) as $file_path) {
      $content = file_get_contents($file_path);
      foreach (Yaml::decode($content) as $id => $definition) {
        $definition['id'] = $id;
        $definition['base path'] = dirname($file_path);
        $definition['file name'] = basename($file_path);
        $definition['provider'] = 'ilo_base_theme_companion';
        $definition['libraries'] = [];
        $definition['libraries'][0][$id]['dependencies'] = [
          'ilo_base_theme_companion/components',
        ];
        if (file_exists($definition['base path'] . DIRECTORY_SEPARATOR . $id . '.behavior.js')) {
          $definition['libraries'][0][$id]['js'] = [
            $id . '.behavior.js' => NULL,
          ];
          $definition['libraries'][0][$id]['dependencies'][] = 'core/drupal';
          $definition['libraries'][0][$id]['dependencies'][] = 'core/drupalSettings';
        }
        $this->removeWingsuitExtensions($definition);
        $patterns[] = $this->getPatternDefinition($definition);
      }
    }

    return $patterns;
  }

  /**
   * Removes Wingsuit YAML extensions.
   */
  private function removeWingsuitExtensions(&$definition) {
    if (isset($definition['fields'])) {
      $fields = &$definition['fields'];
      foreach ($fields as &$field) {
        if (isset($field['preview']['faker'])) {
          unset($field['preview']['faker']);
          $field['preview'] = 'Faked text';
          continue;
        }

        // Remove preview lists.
        if (isset($field['preview'][0]['id'])) {
          $field['preview'] = $field['preview'][0];
        }

        if (isset($field['preview']['id'])) {
          $field['preview']['theme'] = $field['preview']['id'];
        }

        foreach (['id', 'settings', 'fields', 'variant'] as $key) {
          if (isset($field['preview'][$key])) {
            unset($field['preview'][$key]);
          }
        }
      }
    }
  }

}
