/**
 * Created by AlexanderC <alex@mitocgroup.com> on 11/23/14.
 */

(function($) {
    window.SMYQ = function(config) {
        var defaults = {
            basePath: '',
            hazardTypeFormSelector: 'select[name=type]'
        };
        config = $.extend(defaults, config);

        return {
            login: function() {
                window.location = '/login';
            },
            logout: function() {
                window.location = '/logout';
            },
            createSharePoint: function(form) {
                var $form = $(form);

                if(!$form[0].checkValidity()) {
                    $form.focus();
                    $form.find("input[name=name]").focus();
                    return;
                }

                var type = $form.find(config.hazardTypeFormSelector).val();

                var url = config.basePath + '/api/share-point/' + type + '/create';

                $.ajax({
                    url: url,
                    type: "POST",
                    dataType: "json",
                    data: $form.serialize(),
                    success: function(response) {
                        if(response.error) {
                            alert(response.errorDescription);
                        } else {
                            window.location = config.basePath + "/dashboard";
                        }
                    }
                });
            }
        };
    };
})(jQuery);