<?php

namespace Drupal\ilo_base_theme_test\Controller;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;

/**
 * Controller that renders a pattern.
 */
class PatternPreviewController extends ControllerBase {

  /**
   * Render the pattern preview.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object containing the query parameters.
   *
   * @return array
   *   A render array containing the ID and decoded JSON fields.
   */
  public function renderPatternPreview(Request $request) {
    // Get the query parameters.
    $id = $request->query->get('id');
    $fields = $request->query->get('fields');

    // Decode the JSON string.
    $decoded_fields = json_decode(urldecode($fields), TRUE);

    // Check if JSON decoding was successful.
    if (json_last_error() !== JSON_ERROR_NONE) {
      return [
        '#markup' => $this->t('Invalid JSON string.'),
      ];
    }
    return [
      '#type' => 'pattern',
      '#id' => $id,
      '#fields' => $decoded_fields,
    ];
  }

}
