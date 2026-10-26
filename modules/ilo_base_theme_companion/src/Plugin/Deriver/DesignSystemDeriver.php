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
use Drupal\ui_patterns\Definition\PatternDefinition;
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
  protected array $previewMarkupFields;

  /**
   * Base URL for image previews.
   *
   * @var string
   */
  protected string $previewImageBaseUrl;

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
   * @param string $preview_image_base_url
   *   Base URL for image previews.
   */
  public function __construct($base_plugin_id, TypedDataManager $typed_data_manager, MessengerInterface $messenger, FileSystemInterface $file_system, ComponentsLocatorInterface $components_locator, array $markup_preview_fields, string $preview_image_base_url) {
    parent::__construct($base_plugin_id, $typed_data_manager, $messenger, $file_system);
    $this->componentsLocator = $components_locator;
    $this->previewMarkupFields = $markup_preview_fields;
    $this->previewImageBaseUrl = $preview_image_base_url;
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
      $container->getParameter('ilo_base_theme_companion.preview_fields_as_markup'),
      $container->getParameter('ilo_base_theme_companion.preview_image_base_url')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFileExtensions() {
    return ['.component.yml'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPatterns() {
    $patterns = [];
    $definitions = [];
    $directory = $this->componentsLocator->getComponentDirectory();

    foreach (array_keys($this->fileScanDirectory($directory)) as $file_path) {
      $content = file_get_contents($file_path);

      foreach (Yaml::decode($content) as $id => $definition) {
        // Skip forms definitions as forms cannot be handled by UI Patterns.
        if ($definition['namespace'] == 'Components/Forms') {
          continue;
        }

        $definition['id'] = $id;
        $definition['base path'] = dirname($file_path);
        $definition['file name'] = basename($file_path);
        $definition['provider'] = 'ilo_base_theme_companion';
        $definitions[$id] = $definition;
      }
    }

    $dependencies = $this->getComponentDependencies($definitions);

    foreach ($definitions as $id => $definition) {
      // Ensure libraries key is initialized as an array if not already set.
      if (!isset($definition['libraries']) || !is_array($definition['libraries'])) {
        $definition['libraries'] = [];
      }

      // Build the new library structure.
      $new_library = [
        $id => [
          'dependencies' => ['ilo_base_theme_companion/global'],
        ],
      ];

      // If a behavior JS file exists, add it and extra dependencies.
      $behavior_path = $definition['base path'] . DIRECTORY_SEPARATOR . $id . '.behavior.js';
      if (file_exists($behavior_path)) {
        $new_library[$id]['js'] = [
          $id . '.behavior.js' => NULL,
        ];
        $new_library[$id]['dependencies'][] = 'core/drupal';
        $new_library[$id]['dependencies'][] = 'core/drupalSettings';
      }

      // Depend on the libraries of the components included in the templates.
      foreach ($dependencies[$id] as $dependency) {
        $new_library[$id]['dependencies'][] = PatternDefinition::LIBRARY_PREFIX . '/' . $dependency . '.' . $dependency;
      }

      // Append the new library definition.
      $definition['libraries'][] = $new_library;

      $this->processFields($definition);
      $patterns[] = $this->getPatternDefinition($definition);
    }

    return $patterns;
  }

  /**
   * Get the components included in each component's templates.
   *
   * @param array $definitions
   *   Component definitions.
   *
   * @return array
   *   IDs of the included components.
   */
  private function getComponentDependencies(array $definitions): array {
    // Map component directories to the IDs they define.
    $ids = [];
    foreach ($definitions as $id => $definition) {
      $ids[basename($definition['base path'])][] = $id;
    }

    $dependencies = [];
    foreach ($definitions as $id => $definition) {
      $directories = [];
      foreach (array_keys($this->fileSystem->scanDirectory($definition['base path'], '/\.twig$/')) as $template) {
        preg_match_all('/@components\/([\w-]+)\//', file_get_contents($template), $matches);
        $directories = array_merge($directories, $matches[1]);
      }
      $directories = array_diff(array_unique($directories), [basename($definition['base path'])]);

      $dependencies[$id] = [];
      foreach ($directories as $directory) {
        foreach ($ids[$directory] ?? [] as $dependency) {
          // Skip cycles: Drupal can't resolve circular library dependencies.
          if (!$this->dependsOn($dependencies, $dependency, $id)) {
            $dependencies[$id][] = $dependency;
          }
        }
      }
      sort($dependencies[$id]);
    }

    return $dependencies;
  }

  /**
   * Check whether a component is, or depends on, another component.
   *
   * @param array $dependencies
   *   IDs of the included components.
   * @param string $id
   *   Component ID.
   * @param string $other_id
   *   Other component ID.
   *
   * @return bool
   *   TRUE if the component is, or depends on, the other component.
   */
  private function dependsOn(array $dependencies, string $id, string $other_id): bool {
    if ($id === $other_id) {
      return TRUE;
    }
    foreach ($dependencies[$id] ?? [] as $dependency) {
      if ($this->dependsOn($dependencies, $dependency, $other_id)) {
        return TRUE;
      }
    }
    return FALSE;
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
        $this->createPreviewMarkup($field['preview'], $this->previewMarkupFields);
        $this->appendImageBaseUrl($field['preview'], $this->previewImageBaseUrl);
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
    if (!is_array($preview)) {
      return;
    }
    foreach ($preview as $key => &$value) {
      if (is_array($value)) {
        $this->createPreviewMarkup($value, $fields);
      }
      elseif (in_array($key, $fields) && is_string($value)) {
        $value = Markup::create($value);
      }
    }
  }

  /**
   * Append base URL to preview images.
   *
   * @param mixed $preview
   *   Preview value.
   * @param string $base_url
   *   Base URL.
   */
  private function appendImageBaseUrl(&$preview, string $base_url) {
    if (is_scalar($preview) && (str_starts_with($preview, '/images/') || str_starts_with($preview, '/brand-assets/'))) {
      $preview = $base_url . $preview;
    }
    elseif (is_array($preview)) {
      foreach ($preview as &$value) {
        $this->appendImageBaseUrl($value, $base_url);
      }
    }
  }

}
