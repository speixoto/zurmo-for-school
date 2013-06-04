(function ($) {
    $.fn.yiiactiveform.validate = function (form, successCallback, errorCallback) {
        var $form = $(form),
            settings = $form.data('settings'),
            needAjaxValidation = false,
            messages = {};
        $.each(settings.attributes, function () {
            var value,
                msg = [];
            if (this.clientValidation !== undefined && (settings.submitting || this.status === 2 || this.status === 3)) {
                value = getAFValue($form.find('#' + this.inputID));
                this.clientValidation(value, msg, this);
                if (msg.length) {
                    messages[this.id] = msg;
                }
            }
            if (this.enableAjaxValidation && !msg.length && (settings.submitting || this.status === 2 || this.status === 3)) {
                needAjaxValidation = true;
            }
        });

        if (!needAjaxValidation || settings.submitting && !$.isEmptyObject(messages)) {
            if (settings.submitting) {
                // delay callback so that the form can be submitted without problem
                setTimeout(function () {
                    successCallback(messages);
                }, 200);
            } else {
                successCallback(messages);
            }
            return;
        }

        var $button = $form.data('submitObject'),
            extData = '&' + settings.ajaxVar + '=' + $form.attr('id');
        if ($button && $button.length) {
            extData += '&' + $button.attr('name') + '=' + $button.attr('value');
        }

        $.ajax({
            url : settings.validationUrl,
            type : $form.attr('method'),
            data : $form.serialize() + extData,
            dataType : 'jsonp',
            success : function (data) {
                if (data !== null && typeof data === 'object') {
                    $.each(settings.attributes, function () {
                        if (!this.enableAjaxValidation) {
                            delete data[this.id];
                        }
                    });
                    successCallback($.extend({}, messages, data));
                } else {
                    successCallback(messages);
                }
            },
            error : function () {
                if (errorCallback !== undefined) {
                    errorCallback();
                }
            }
        });
    };
})(jQuery);