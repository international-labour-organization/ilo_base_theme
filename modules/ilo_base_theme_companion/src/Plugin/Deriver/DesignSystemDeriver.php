<?php

namespace Drupal\ilo_base_theme_companion\Plugin\Deriver;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Random;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Markup;
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
   * List of fields that must be considered as markup on previews.
   *
   * @var array
   */
  protected array $markupPreviewFields = [];

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
   * @param array $markup_preview_fields
   *   List of fields that must be considered as markup on previews.
   */
  public function __construct($base_plugin_id, TypedDataManager $typed_data_manager, MessengerInterface $messenger, FileSystemInterface $file_system, ComponentsLocatorInterface $components_locator, array $markup_preview_fields) {
    parent::__construct($base_plugin_id, $typed_data_manager, $messenger, $file_system);
    $this->componentsLocator = $components_locator;
    $this->markupPreviewFields = $markup_preview_fields;
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
      $container->get('ilo_base_theme_companion.components_locator'),
      $container->getParameter('ilo_base_theme_companion.preview_fields_as_markup')
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
        $this->processFields($definition);
        $patterns[] = $this->getPatternDefinition($definition);
      }
    }

    return $patterns;
  }

  /**
   * Process definition fields.
   */
  private function processFields(&$definition) {
    if (isset($definition['fields'])) {
      $fields = &$definition['fields'];
      foreach ($fields as &$field) {
        // Make sure we generate random test where necessary.
        if (isset($field['preview']['faker'])) {
          unset($field['preview']['faker']);
          $field['preview'] = (new Random())->sentences(3, TRUE);
        }
        $this->createPreviewMarkup($field['preview'], $this->markupPreviewFields);
      }
    }
  }

  /**
   * Make sure specific preview fields are considered as markup.
   *
   * @param mixed $preview
   *   Preview value.
   * @param array $fields
   *   List of fields that must be considered as markup on previews.
   */
  private function createPreviewMarkup(&$preview, array $fields) {
    foreach ($preview as $key => &$value) {
      if (is_array($value)) {
        $this->createPreviewMarkup($value, $fields);
      }
      elseif (in_array($key, $fields) && is_string($value)) {
        $value = Markup::create($value);
      }
    }
  }

}
