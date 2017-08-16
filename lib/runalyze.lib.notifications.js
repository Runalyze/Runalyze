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

        elements.menu.find('.notification-message a.internal').unbind('click').click(function(e){
            Parent.Overlay.load($(this).attr('href'), { size: $(this).data('size') || 'normal' });

            if ($(this).parent().hasClass('is-new')) {
                $(this).parent().removeClass('is-new');
                $.ajax(urls.markRead($(this).parent().data('id')));

                checkNewState();
            }

            return false;
        });

        Parent.Overlay.bindLinks();
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
                } else if ('external' == val.size) {
                    li = elements.externalLink.clone();
                    li.find('a').attr('href', val.link);
                } else {
                    li = elements.internalLink.clone();
                    li.find('a').attr('href', val.link).data('size', val.size || 'normal');
                }

                li.data('id', val.id);
                li.find('span:not(.no-link)').text(val.text);

                if(window.Notification && Notification.permission !== "denied" && (lastRequest - val.createdAt) < 86400) {
                    Notification.requestPermission(function(status) {
                        var n = new Notification('RUNALYZE', {
                            body: val.text,
                            icon: '/favicon.ico',
                            timestamp: val.createdAt,
                        });
                        setTimeout(n.close.bind(n), 3500);
                    });
                }

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

    function clearMenu() {
        elements.menu.find('.notification-message').remove();
        elements.noNewMessage.removeClass('hide');
        elements.newIndicator.addClass('hide');
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
            $list.find('.is-new').unbind('click').click(function(e){
                var $el = $(this);

                $el.removeClass('is-new');
                $.ajax(urls.markRead($el.data('id')));

                elements.menu.find('.notification-message.is-new').filter(function(){
                    return $(this).data('id') === $el.data('id');
                }).remove();

                checkNewState();
            });

            $list.find('.internal').unbind('click').click(function(e){
                Parent.Overlay.load($(this).attr('href'), { size: $(this).data('size') || 'normal' });

                if ($(this).parent().hasClass('is-new')) {
                    var $el = $(this).parent();

                    $el.removeClass('is-new');
                    $.ajax(urls.markRead($el.data('id')));

                    elements.menu.find('.notification-message.is-new').filter(function(){
                        return $(this).data('id') === $el.data('id');
                    }).remove();

                    checkNewState();
                }

                return false;
            });

            $list.find('.read-all-notifications-link').unbind('click').click(function(e){
                e.preventDefault();

                $list.find('.is-new').removeClass('is-new');
                $.ajax(urls.markAllRead);
                $(this).parent().fadeOut();

                clearMenu();
            });
        } else {
            $list.find('.read-all-notifications-link').parent().remove();
        }
    };

    Parent.addInitHook('init-notifications', self.init);

    return self;
})(jQuery, Runalyze);
