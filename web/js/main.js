/**
 * Created by AlexanderC <alex@mitocgroup.com> on 11/23/14.
 */

(function($) {
    window.SMYQ = function(config) {
        var defaults = {};
        config = $.extend(defaults, config);

        return {
            login: function() {
                window.location = '/login';
            },
            logout: function() {
                window.location = '/logout';
            }
        };
    };
})(jQuery);