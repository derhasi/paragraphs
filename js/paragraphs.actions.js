/**
 * @file
 * Paragraphs actions JS code for paragraphs actions button.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Process paragraph_actions elements.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches paragraphsActions behaviors.
   */
  Drupal.behaviors.paragraphsActions = {
    attach: function (context, settings) {
      var $actionsElement = $(context).find('.paragraph-actions').once('paragraph-actions');
      // Attach event handlers to toggle button.
      $actionsElement.each(function () {
        var $this = $(this);
        var $toggle = $this.find('.paragraph-actions-toggle');

        $toggle.on('click', function (e) {
          e.preventDefault();
          $this.toggleClass('open');
        });

        $toggle.on('focusout', function (e) {
          $this.removeClass('open');
        });
      });
    }
  };

})(jQuery, Drupal);
