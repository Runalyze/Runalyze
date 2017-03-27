Runalyze.Notifications = (function($, Parent){

    // Public

    var self = {};

    // Private

    var options = {
        cronjobInterval: 60
    };

    var lastRequest = Math.floor(new Date().valueOf() / 1000);

    var urls = {
        markAllRead: '_internal/notifications/read/all',
        markRead: function(id) { return '_internal/notifications/read/' + id; },
        check: function(lastRequest) { return '_internal/notifications?last_request=' + lastRequest; }
    };

    var elements = {
        menu: [],
        newIndicator: [],
        noNewMessage: [],
        internalLink: [],
        externalLink: [],
        noLink: []
    };

    // Private Methods

    function initElements() {
        elements.menu = $('#new-notifications-menu');
        elements.newIndicator = elements.menu.find('.new-notifications-indicator');
        elements.noNewMessage = elements.menu.find('.no-notifications-messages');
        elements.internalLink = $('#tpl-notification-message-with-internal-link').remove().removeClass('hide').removeAttr('id');
        elements.externalLink = $('#tpl-notification-message-with-external-link').remove().removeClass('hide').removeAttr('id');
        elements.noLink = $('#tpl-notification-message-without-link').remove().removeClass('hide').removeAttr('id');

        bindNotificationLinks();
    }

    function initCronjob() {
        ifvisible.onEvery(options.cronjobInterval, checkForNewMessages);
    }

    function bindNotificationLinks() {
        elements.menu.find('.notification-message').unbind('click').click(function(e){
            if ($(this).hasClass('is-new')) {
                $(this).removeClass('is-new');
                $.ajax(urls.markRead($(this).data('id')));

                checkNewState();
            }
        });
    }

    function checkForNewMessages() {
        if (lastRequest + 30 > Math.floor(new Date().valueOf() / 1000)) {
            return;
        }

        $.getJSON(urls.check(lastRequest), function(data) {
            // To be absolutely correct, the request itself should give this timestamp
            lastRequest = Math.floor(new Date().valueOf() / 1000);

            if (data.length) {
                elements.noNewMessage.addClass('hide');
            }

            $.each(data, function(key, val) {
                var li;

                if ('' == val.link) {
                    li = elements.noLink.clone();
                } else if ('http' == val.link.substring(0, 4)) {
                    li = elements.externalLink.clone();
                    li.find('a').attr('href', val.link);
                } else {
                    li = elements.internalLink.clone();
                    li.find('a').attr('href', val.link);
                }

                li.data('id', val.id);
                li.find('span:not(.no-link)').text(val.text);
                li.insertAfter(elements.noNewMessage);
            });

            checkNewState();
            bindNotificationLinks();
        });
    }

    function checkNewState() {
        var status = elements.menu.find('.notification-message.is-new').length == 0;

        elements.newIndicator.toggleClass('hide', status);
    }

    // Public Methods

    self.init = function() {
        initElements();
        initCronjob();
        checkNewState();
        checkForNewMessages(); // As long as base_logged_in.twig.html does not include current notifications
    };

    self.setLastRequestTime = function(timestamp) {
        lastRequest = timestamp;
    };

    self.bindToList = function(selector) {
        var $list = $(selector);
        var $new = $list.find('.is-new');

        if ($new.length) {
            $list.find('.is-new').each(function(){
                $(this).find('a').each(function(){
                    if ('http' == $(this).attr('href').substring(0, 4)) {
                        $(this).attr('target', '_blank');
                    }
                })
            }).unbind('click').click(function(e){
                $(this).removeClass('is-new');
                $.ajax(urls.markRead($(this).data('id')));
            });

            $list.find('.read-all-notifications-link').unbind('click').click(function(e){
                e.preventDefault();

                $list.find('.is-new').removeClass('is-new');
                $.ajax(urls.markAllRead);
                $(this).parent().fadeOut();
            });
        } else {
            $list.find('.read-all-notifications-link').parent().remove();
        }
    };

    Parent.addLoadHook('init-notifications', self.init);

    return self;
})(jQuery, Runalyze);
