<?php

/**
 * @file
 * Contains Drupal\paragraphs\Entity\Form\ParagraphsItemSettingsForm.
 */

namespace Drupal\paragraphs\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ParagraphsItemSettingsForm.
 * @package Drupal\paragraphs\Form
 * @ingroup paragraphs
 */
class ParagraphsItemSettingsForm extends FormBase
{

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'ParagraphsItem_settings';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Empty implementation of the abstract submit class.
  }


  /**
   * Define the form used for ParagraphsItem  settings.
   * @return array
   *   Form definition array.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['ParagraphsItem_settings']['#markup'] = 'Settings form for ParagraphsItem. Manage field settings here.';
    return $form;
  }
}
