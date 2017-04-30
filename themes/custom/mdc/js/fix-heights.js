/**
 * Created by shaun on 6/2/2017.
 *
 * Make the sidebar and content region heights all match.
 */

(function($) {

  function fixHeights() {
    var content = $('#content');
    var leftSidebar = $('#left-sidebar');
    var rightSidebar = $('#right-sidebar');

    // Reset the heights.
    content.css("height", "");
    leftSidebar.css("height", "");
    rightSidebar.css("height", "");

    // If not in mobile/narrow display mode, update the heights to match.
    if (leftSidebar.css('border-radius') != '0px') {
      // Get the heights.
      var contentHeight = content.height() + 30;
      var leftSidebarHeight = leftSidebar.height();
      var rightSidebarHeight = rightSidebar.height();

      // Get the minimum content height to span the full height of the viewport.
      var windowHeight = $(window).height();
      var headerHeight = $('#header').height();
      var footerHeight = $('.footer').height();
      var toolbarHeight = $('#toolbar-administration').size() ? 78 : 0;
      var minContentHeight = windowHeight - toolbarHeight - headerHeight - footerHeight;

      // Get the desired heights.
      var height = Math.max(contentHeight, leftSidebarHeight, rightSidebarHeight, minContentHeight);

      // Set the heights.
      content.height(height - 30);
      leftSidebar.height(height);
      rightSidebar.height(height);
    }
  }

  $(window).load(fixHeights);
  $(window).resize(fixHeights);

})(jQuery);
