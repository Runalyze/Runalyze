/*
 * jbar (for jQuery)
 * version: 0.2.0 (07/02/2011)
 * @requires jQuery v1.4 or later
 * http://javan.github.com/jbar/
 * http://github.com/javan/jbar
 *
 * Licensed under the MIT:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright 2010+ Javan Makhmali :: javan@javan.us
 *
 * Usage:
 *  
 *  jQuery(function(){
 *    jQuery('.jbar').jbar();
 *  });
 *  // Where .jbar is the class belonging to your menus.
 *
 */

(function($) {
  $.fn.jbar = function(settings) {
    var config = {
      cssClass: 'jbar',
      downArrow: '&#x25BC;',
      upArrow: '&#x25B2;',
      showSubmenuEvent: 'click',
      fixIEzindex: true
    };
    
    if (settings) {
      $.extend(config, settings);
    }
  
    this.each(function(){
      var menu = $(this);
      menu.addClass(config.cssClass);
      
      // To allow IE specific css
      if ($.browser.msie) {
        menu.addClass('jbar_browser_IE').addClass('jbar_browser_IE'+parseInt($.browser.version));
      }
      
      menu.find('> li').each(function(){
        var li = $(this);
        var submenu = li.find('ul');
        var link = li.find('> a');
        var hasSubmenu = (submenu.length != 0);
        var hasLink = (link.length != 0);
        var hasAnchorLink = (hasLink && link.attr('href').charAt(0) == '#');
        
        if (hasLink) {
          link.wrapInner('<span class="link_text" />');
          
          if (!hasSubmenu) {
            link.addClass('has_no_down_arrow');
          }
        }
        
        if (!hasSubmenu) {
          return true;
        }
        
        submenu.wrap('<div class="submenu_container" />');
        li.find('div.submenu_container').prepend('<span class="up_arrow">'+config.upArrow+'</span>');
        submenu.show();
        
        if (!hasLink) {
          link = $('<a href="#" class="has_lonely_down_arrow"></a>');
          hasAnchorLink = true;
          li.prepend(link);
        }
        
        link.addClass('has_down_arrow').append(' <span class="down_arrow"><em>'+config.downArrow+'</em></span>');
        
        // If the the link is an anchor (#) then the whole thing becomes
        // the trigger to open the sub menu.
        if (hasAnchorLink) {
          link.addClass('trigger');
        // Otherwise the down arrow is the trigger and the link remains active.
        } else {
          link.addClass('has_trigger_down_arrow').find('.link_text').mouseover(function(){
            $(this).addClass('hovered');
          }).mouseleave(function(){
            $(this).removeClass('hovered');
          });
          
          link.find('.down_arrow').addClass('trigger').mouseover(function(){
            $(this).addClass('hovered');
          }).mouseleave(function(){
            $(this).removeClass('hovered');
          });
        }

        li.find('.trigger').live(config.showSubmenuEvent, function(event){
          event.preventDefault();
          var submenuWidth = li.find('.submenu_container').outerWidth();
          var downArrowPosition = li.find('.down_arrow').position().left + 2;
          // The up arrow is positioned directly under the down arrow by default.
          var upArrowPosition = downArrowPosition;
          // If the down arrow is more to the right than the submenu,
          // put the up arrow on the right side of the submenu.
          if (downArrowPosition > submenuWidth) {
            upArrowPosition = submenuWidth - 20;
          }
          // Position the up arrow.
          li.find('span.up_arrow').css({ paddingLeft: upArrowPosition + 'px' });
          // Position the submenu directly under the menu.
          li.find('.submenu_container').css({ top: li.outerHeight() + 'px' }).show();
          $(this).addClass('triggered').addClass('hovered');
          li.mouseleave(function(){
            li.find('.trigger').removeClass('triggered').removeClass('hovered');
            li.find('.submenu_container').hide();
          });
        });
      });
      
      $(this).find('> li:first a:first').addClass('first');
      $(this).find('> li:last a:first').addClass('last');
    });
  
    // IE 7 and down doesn't do z-index correctly.
    if (config.fixIEzindex && $.browser.msie && $.browser.version < 8) {
     var zIndex = 99999;
     $('ul.jbar').each(function(){
       $(this).wrap('<div class="jbar_IE_zIndex_fix" style="z-index:'+zIndex+'" />');
       zIndex--;
     });
    }
   
    return this;
  }
})(jQuery);
