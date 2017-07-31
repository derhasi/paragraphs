/**
 * @file paragraphs.modal.js
 *
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Click handler for click "Add" button between paragraphs.
   *
   * @type {Object}
   */
  Drupal.behaviors.paragraphsModalAdd = {
    attach: function (context) {
      $('.paragraph-type-add-modal-button', context)
        .once('add-click-handler')
        .on('click', function (event) {
          var $button = $(this);
          var $add_more_wrapper = $button.parent().siblings('.paragraphs-add-dialog');
          Drupal.paragraphsAddModal.openDialog($add_more_wrapper);

          // Stop default execution of click event.
          event.preventDefault();
          event.stopPropagation();
        });
    }
  };

  /**
   * Namespace for modal related javascript methods.
   *
   * @type {Object}
   */
  Drupal.paragraphsAddModal = {};

  /**
   * Open modal dialog for adding new paragraph in list.
   *
   * @param {Object} $context
   *   jQuery element of form wrapper used to submit request for adding new
   *   paragraph to list. Wrapper also contains dialog template.
   */
  Drupal.paragraphsAddModal.openDialog = function ($context) {

    $context.dialog({
      modal: true,
      resizable: false,
      title: drupalSettings.paragraphs.title,
      width: 'auto',
      close: function () {
        var $dialog = $(this);

        // Destroy dialog object.
        $dialog.dialog('destroy');
      }
    });

    // Close the dialog after a button was clicked.
    $('.field-add-more-submit', $context)
      .each(function () {
      // Use mousedown event, because we are using ajax in the modal add mode
      // which explicitly suppresses the click event.
      $(this).on('mousedown', function () {
        var $this = $(this);
        $this.closest('div.ui-dialog-content').dialog('close');
      });
    });
  };

}(jQuery, Drupal, drupalSettings));
