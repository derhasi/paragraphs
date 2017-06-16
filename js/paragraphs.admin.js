(function ($, Drupal) {

  'use strict';

  /**
   * For body tag, adds tabs for selecting how the content will be displayed.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.bodyTabs = {
    attach: function (context, settings) {
      /** Set content fields to visible when tabs are created. After an action
       * being performed, stay on the same perspective. **/
      if($(context).find('.layout-region-node-main').hasClass('behavior-active')) {
        $(context).find('.layout-region-node-main').removeClass('content-active');
        $(context).find('.layout-region-node-main').addClass('behavior-active');
        $(context).find('.paragraphs-tabs').find('#content').removeClass('is-active');
        $(context).find('.paragraphs-tabs').find('#behavior').addClass('is-active');
        $('.paragraphs-content').hide();
        $('.paragraphs-behavior').show();
      }
      else {
        /** Activate content tab visually if there is no previously activated
         * tab. */
        if (!($(context).find('.layout-region-node-main').hasClass('content-active')) && !($(context).find('.layout-region-node-main').hasClass('behavior-active'))) {
          $(context).find('.paragraphs-tabs').find('#content').addClass('is-active');
          $(context).find('.layout-region-node-main').addClass('content-active');
        }
        $('.paragraphs-content').show();
        $('.paragraphs-behavior').hide();
      }
      /** Checking the number of behavior elements and showing tabs only if there
       * are behavior elements.
       */
      if($('.paragraphs-behavior').length != 0) {
        $(context).find('.paragraphs-tabs-wrapper').find('.paragraphs-tabs').show();
      }
      else {
        $(context).find('.paragraphs-tabs-wrapper').find('.paragraphs-tabs').hide();
      }
      /** Create click event. **/
      $(context).find('.paragraphs-tabs').find('a').click(function(e) {
        e.preventDefault();
        /** Switching active class between tabs. */
        var el = jQuery(this);
        $(context).find('.paragraphs-tabs').find('li').removeClass('is-active');
        el.parent('li').addClass('is-active');
        $(context).find('.layout-region-node-main').removeClass('behavior-active');
        $(context).find('.layout-region-node-main').removeClass('content-active');
        $(context).find('.paragraphs-tabs-wrapper').removeClass('is-active');
        $(context).find(el.attr('href')).addClass('is-active');
        /** Show/Hide fields based on current active class. */
        if($(context).find('.paragraphs-tabs-wrapper').find('#content').hasClass('is-active')) {
          $(context).find('.layout-region-node-main').addClass('content-active');
          $('.paragraphs-content').show();
          $('.paragraphs-behavior').hide();
        }
        if($(context).find('.paragraphs-tabs-wrapper').find('#behavior').hasClass('is-active')) {
          $(context).find('.layout-region-node-main').addClass('behavior-active');
          $('.paragraphs-content').hide();
          $('.paragraphs-behavior').show();
        }
      } );
    }
  };

})(jQuery, Drupal);

