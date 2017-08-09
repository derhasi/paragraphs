/**
 * @file
 * Paragraphs actions JS code for paragraphs actions button.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Process elements with the .paragraphs-actions class on page load.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches paragraphsActions behaviors.
   */
  Drupal.behaviors.paragraphsActions = {
    attach: function (context, settings) {
      var $actionsElement = $(context).find('.paragraphs-actions').once('paragraphs-actions');
      // Attach event handlers to toggle button.
      $actionsElement.each(function () {
        var $this = $(this);
        var $toggle = $this.find('.paragraphs-actions-toggle');

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
