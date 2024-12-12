/**
 * Created by shaun on 6/2/2017.
 *
 * Make the sidebar and content region heights all match.
 */

(function($) {

  function fixHeights() {
    // Some object references.
    var w = $(window);
    var content = $('#content');
    var leftSidebar = $('#left-sidebar');
    var rightSidebar = $('#right-sidebar');

    // Reset the heights.
    content.css('height', '');
    leftSidebar.css('height', '');
    rightSidebar.css('height', '');

    // If not in mobile/narrow display mode, update the heights to match.
    if (w.width() >= 768) {
      // Some more objects.
      var body = $('body');
      var header = $('#header');
      var footer = $('.footer');
      var toolbar = $('#toolbar-administration');

      // Get the heights.
      var windowHeight = w.height();
      var bodyHeight = body.height();
      var contentHeight = content.length ? content.height() + 30 : 0;
      var leftSidebarHeight = leftSidebar.length ? leftSidebar.height() : 0;
      var rightSidebarHeight = rightSidebar.length ? rightSidebar.height() : 0;
      var toolbarHeight = toolbar.length ? 60 : 0;
      var headerHeight = header.length ? header.height() : 0;
      var footerHeight = footer.length ? footer.height() : 0;

      var pageHeight = Math.max(bodyHeight, windowHeight);
      var minContentHeight = pageHeight - toolbarHeight - headerHeight - footerHeight;
      var height = Math.max(contentHeight, leftSidebarHeight, rightSidebarHeight, minContentHeight);

      // Set the heights.
      content.height(height - 30);
      leftSidebar.height(height);
      rightSidebar.height(height);
    }
  }

  $(window).on('load', fixHeights);
  $(window).on('resize', fixHeights);

})(jQuery);
