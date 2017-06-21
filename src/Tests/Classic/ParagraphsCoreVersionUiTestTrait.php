<?php

namespace Drupal\paragraphs\Tests\Classic;

/**
 * Provides helper methods for Drupal 8.3.x and 8.4.x versions.
 */
trait ParagraphsCoreVersionUiTestTrait {

  /**
   * Executes a form submission depending on the Drupal Core version.
   *
   * @param \Drupal\Core\Url|string $path
   *   The location of the post form.
   * @param array|null $edit_d8_3
   *   The field data values in Drupal 8.3.x.
   * @param string $submit_d8_3
   *   The submit button value in Drupal 8.3.x.
   * @param string $submit_d8_4
   *   The submit button value in Drupal 8.4.x.
   * @param array|null $edit_d8_4
   *   (optional) The field data values in Drupal 8.4.x. Defaults to 8.3.x.
   */
  protected function drupalPostFormSave($path, $edit_d8_3, $submit_d8_3, $submit_d8_4, $edit_d8_4 = []) {
    if (substr(\Drupal::VERSION, 0, 3) == '8.3') {
      $this->drupalPostForm($path, $edit_d8_3, $submit_d8_3);
    }
    else {
      $this->drupalPostForm($path, !empty($edit_d8_4) ? $edit_d8_4 : $edit_d8_3, $submit_d8_4);
    }
  }

}
