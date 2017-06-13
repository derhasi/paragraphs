(function ($, Drupal) {

  'use strict';

  /**
   * For body tag, adds tabs for selecting how the content will be displayed.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.bodyTabs = {
    attach: function (context) {
      var $parWidgets = $('.paragraphs-tabs-wrapper', context).once('paragraphs-bodytabs');
      $parWidgets.each(function() {
        var $parWidget = $(this);
        var $parTabs = $parWidget.find('.paragraphs-tabs');
        var $parContent = $parWidget.find('.paragraphs-content');
        var $parBehavior = $parWidget.find('.paragraphs-behavior');
        var $mainRegion = $parWidget.find('.layout-region-node-main');
        var $tabContent = $parTabs.find('#content');
        var $tabBehavior = $parTabs.find('#behavior');

        // Set content fields to visible when tabs are created. After an action
        // being performed, stay on the same perspective.
        if ($parWidget.hasClass('behavior-active')) {
          $parWidget.removeClass('content-active').addClass('behavior-active');
          $tabContent.removeClass('is-active');
          $tabBehavior.addClass('is-active');
          $parContent.hide();
          $parBehavior.show();
        }
        else {
          // Activate content tab visually if there is no previously
          // activated tab.
          if (!($mainRegion.hasClass('content-active'))
            && !($mainRegion.hasClass('behavior-active'))) {
            $tabContent.addClass('is-active');
            $mainRegion.addClass('content-active');
          }

          $parContent.show();
          $parBehavior.hide();
        }

        // Checking the number of behavior elements and showing tabs only if
        //  there are behavior elements.
        if ($parBehavior.length != 0) {
          $parTabs.show();
        }
        else {
          $parTabs.hide();
        }

        // Create click event.
        $parTabs.find('a').click(function(e) {
          e.preventDefault();
          // Switching active class between tabs.
          var $this = $(this);
          $parTabs.find('li').removeClass('is-active');
          $this.parent('li').addClass('is-active');
          $mainRegion.removeClass('behavior-active content-active is-active');
          $($parWidget).find($this.attr('href')).addClass('is-active');

          // Show/Hide fields based on current active class.
          if ($parWidget.find('#content').hasClass('is-active')) {
            $mainRegion.addClass('content-active');
            $parContent.show();
            $parBehavior.hide();
          }

          if ($parWidget.find('#behavior').hasClass('is-active')) {
            $mainRegion.addClass('behavior-active');
            $parContent.hide();
            $parBehavior.show();
          }
        });
      });
    }
  };

})(jQuery, Drupal);

