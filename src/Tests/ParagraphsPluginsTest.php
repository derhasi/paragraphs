<?php

namespace Drupal\paragraphs\Tests;

use Drupal\field_ui\Tests\FieldUiTestTrait;

/**
 * Tests paragraphs behavior plugins.
 *
 * @group paragraphs
 */
class ParagraphsPluginsTest extends ParagraphsTestBase {

  use FieldUiTestTrait;

  /**
   * Tests the behavior plugins for paragraphs.
   */
  public function testBehaviorPluginsFields() {
    $this->addParagraphedContentType('paragraphed_test', 'field_paragraphs');
    $this->loginAsAdmin(['create paragraphed_test content', 'edit any paragraphed_test content']);

    // Add a Paragraph type.
    $paragraph_type = 'text_paragraph';
    $this->addParagraphsType($paragraph_type);

    // Add a text field to the text_paragraph type.
    static::fieldUIAddNewField('admin/structure/paragraphs_type/' . $paragraph_type, 'text', 'Text', 'text_long', [], []);

    // Enable the test plugin.
    $edit = [
      'behavior_plugins[text_bold_text][enabled]' => TRUE,
      'behavior_plugins[test_text_color][enabled]' => TRUE,
    ];
    $this->drupalPostForm('admin/structure/paragraphs_type/' . $paragraph_type, $edit, t('Save'));

    // Create a node with a Paragraph.
    $this->drupalGet('node/add/paragraphed_test');
    $this->assertFieldByName('field_paragraphs[0][behavior_plugins][test_text_color][text_color]', 'blue');
    // Setting a not allowed value in the text color plugin text field.
    $plugin_text = 'green';
    $edit = [
      'title[0][value]' => 'paragraphs_plugins_test',
      'field_paragraphs[0][subform][field_text][0][value]' => 'amazing_plugin_test',
      'field_paragraphs[0][behavior_plugins][test_text_color][text_color]' => $plugin_text,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));
    // Asserting that the error message is shown.
    $this->assertText('The only allowed values are blue and red.');
    // Updating the text color to an allowed value.
    $plugin_text = 'red';
    $edit = [
      'field_paragraphs[0][behavior_plugins][test_text_color][text_color]' => $plugin_text,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));
    // Assert that the class has been added to the element.
    $this->assertRaw('class="red_plugin_text');

    $this->clickLink('Edit');
    // Assert the plugin fields populate the stored values.
    $this->assertFieldByName('field_paragraphs[0][behavior_plugins][test_text_color][text_color]', $plugin_text);

    // Update the value of both plugins.
    $updated_text = 'blue';
    $edit = [
      'field_paragraphs[0][behavior_plugins][test_text_color][text_color]' => $updated_text,
      'field_paragraphs[0][behavior_plugins][text_bold_text][bold_text]' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and keep published'));
    $this->assertNoRaw('class="red_plugin_text');
    $this->assertRaw('class="blue_plugin_text bold_plugin_text');
    $this->clickLink('Edit');
    // Assert the plugin fields populate the stored values.
    $this->assertFieldByName('field_paragraphs[0][behavior_plugins][test_text_color][text_color]', $updated_text);
    $this->assertFieldByName('field_paragraphs[0][behavior_plugins][text_bold_text][bold_text]', TRUE);
  }

}
