/**
 * Created by shaun on 6/2/17.
 */

(function($) {

  function fixHeights() {
    var content = $('#content');
    var leftSidebar = $('#left-sidebar');

    // Get the heights.
    content.css("height", "");
    var contentHeight = content.height();
    leftSidebar.css("height", "");
    var leftSidebarHeight = leftSidebar.height();

    // Get the minimum content height to span the full height of the viewport.
    var windowHeight = $(window).height();
    var headerHeight = $('#header').height();
    var footerHeight = $('.footer').height();
    var toolbarHeight = $('#toolbar-administration').size() ? 78 : 0;
    var minContentHeight = windowHeight - toolbarHeight - headerHeight - footerHeight;

    // Get the desired heights.
    var height = Math.max(contentHeight, leftSidebarHeight, minContentHeight);

    // Set the heights.
    leftSidebar.height(height);
    content.height(height);
  }

  $(window).load(fixHeights);
  $(window).resize(fixHeights);

})(jQuery);
