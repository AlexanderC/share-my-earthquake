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

        function showMessage(message, type)
        {
            alert(type.toUpperCase() + ': ' + message);
        }

        return {
            showMessage: function(message, type) {
                showMessage(message, type);
            },
            login: function() {
                window.location = '/login';
            },
            logout: function() {
                window.location = '/logout';
            },
            previewShare: function(type, template, callback) {
                var url = config.basePath + '/api/social-preview/' + type;

                $.ajax({
                    url: url,
                    type: "POST",
                    data: {template: template},
                    dataType: "json",
                    success: function(response) {
                        if(response.error) {
                            showMessage(response.errorDescription, 'error');
                        } else {
                            callback(response.preview);
                        }
                    }
                });
            },
            deleteSharePoint: function(id, selectorToRemove) {
                var url = config.basePath + '/api/share-point/' +id;

                $.ajax({
                    url: url,
                    type: "DELETE",
                    dataType: "json",
                    success: function(response) {
                        if(response.error) {
                            showMessage(response.errorDescription, 'error');
                        } else {
                            $(selectorToRemove).remove();
                        }
                    }
                });
            },
            createSharePoint: function(form) {
                var $form = $(form);

                if(!$form[0].checkValidity()) {
                    $form.focus();
                    $form.find("input[name=name]").focus();
                    return;
                }

                var type = $form.find(config.hazardTypeFormSelector).val();

                var url = config.basePath + '/api/share-point/' + type;

                $.ajax({
                    url: url,
                    type: "POST",
                    dataType: "json",
                    data: $form.serialize(),
                    success: function(response) {
                        if(response.error) {
                            showMessage(response.errorDescription, 'error');
                        } else {
                            window.location = config.basePath + "/dashboard";
                        }
                    }
                });
            }
        };
    };
})(jQuery);