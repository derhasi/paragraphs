<?php

namespace Drupal\paragraphs\Tests\Experimental;

use Drupal\field_ui\Tests\FieldUiTestTrait;

/**
 * Tests paragraphs behavior plugins.
 *
 * @group paragraphs
 */
class ParagraphsExperimentalBehaviorsTest extends ParagraphsExperimentalTestBase {

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

    // Check default configuration.
    $this->drupalGet('admin/structure/paragraphs_type/' . $paragraph_type);
    $this->assertFieldByName('behavior_plugins[test_text_color][settings][default_color]', 'blue');

    $this->assertText('Behavior plugins are only supported by the EXPERIMENTAL paragraphs widget');
    // Enable the test plugins, with an invalid configuration value.
    $edit = [
      'behavior_plugins[test_bold_text][enabled]' => TRUE,
      'behavior_plugins[test_text_color][enabled]' => TRUE,
      'behavior_plugins[test_text_color][settings][default_color]' => 'red',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText('Red can not be used as the default color.');

    // Ensure the form can be saved with an invalid configuration value when
    // the plugin is not selected.
    $edit = [
      'behavior_plugins[test_bold_text][enabled]' => TRUE,
      'behavior_plugins[test_text_color][enabled]' => FALSE,
      'behavior_plugins[test_text_color][settings][default_color]' => 'red',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText('Saved the text_paragraph Paragraphs type.');

    // Ensure it can be saved with a valid value and that the defaults are
    // correct.
    $this->drupalGet('admin/structure/paragraphs_type/' . $paragraph_type);
    $this->assertFieldChecked('edit-behavior-plugins-test-bold-text-enabled');
    $this->assertNoFieldChecked('edit-behavior-plugins-test-text-color-enabled');
    $this->assertFieldByName('behavior_plugins[test_text_color][settings][default_color]', 'blue');

    $edit = [
      'behavior_plugins[test_bold_text][enabled]' => TRUE,
      'behavior_plugins[test_text_color][enabled]' => TRUE,
      'behavior_plugins[test_text_color][settings][default_color]' => 'green',
    ];
    $this->drupalPostForm('admin/structure/paragraphs_type/' . $paragraph_type, $edit, t('Save'));
    $this->assertText('Saved the text_paragraph Paragraphs type.');

    // Create a node with a Paragraph.
    $this->drupalGet('node/add/paragraphed_test');
    $this->assertFieldByName('field_paragraphs[0][behavior_plugins][test_text_color][text_color]', 'green');
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
      'field_paragraphs[0][behavior_plugins][test_bold_text][bold_text]' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and keep published'));
    $this->assertNoRaw('class="red_plugin_text');
    $this->assertRaw('class="blue_plugin_text bold_plugin_text');
    $this->clickLink('Edit');
    // Assert the plugin fields populate the stored values.
    $this->assertFieldByName('field_paragraphs[0][behavior_plugins][test_text_color][text_color]', $updated_text);
    $this->assertFieldByName('field_paragraphs[0][behavior_plugins][test_bold_text][bold_text]', TRUE);

    // Test plugin applicability. Add a paragraph type.
    $paragraph_type = 'text_paragraph_test';
    $this->addParagraphsType($paragraph_type);
    // Add a text field to the text_paragraph type.
    static::fieldUIAddNewField('admin/structure/paragraphs_type/' . $paragraph_type, 'text_test', 'Text', 'text_long', [], []);
    // Assert if the plugin is listed on the edit form of the paragraphs type.
    $this->drupalGet('admin/structure/paragraphs_type/' . $paragraph_type);
    $this->assertNoFieldByName('behavior_plugins[test_bold_text][enabled]');
    $this->assertFieldByName('behavior_plugins[test_text_color][enabled]');

    // Test a plugin without behavior fields.
    $edit = [
      'behavior_plugins[test_dummy_behavior][enabled]' => TRUE,
      'behavior_plugins[test_text_color][enabled]' => TRUE,
    ];
    $this->drupalPostForm('admin/structure/paragraphs_type/' . $paragraph_type, $edit, t('Save'));
    $this->drupalPostAjaxForm('node/add/paragraphed_test', [], 'field_paragraphs_text_paragraph_test_add_more');
    $edit = [
      'title[0][value]' => 'paragraph with no fields',
      'field_paragraphs[0][subform][field_text_test][0][value]' => 'my behavior plugin does not have any field',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));
    $this->assertRaw('dummy_plugin_text');
  }

}
