function onUseParentChangedHandler(element) {
    'use strict';
    var useParent = element.value === 1;
    var dependencies = {
        'use_parent_review_settings': 'review_fields_available',
        'use_parent_proscons_settings': ['pros', 'cons']
    };

    for (var cssClass in dependencies) {
        if(element.hasClassName(cssClass)) {
            var dependentAttrs = dependencies[cssClass];
            break;
        }
    }

    var changeState = function(el, idNamePart) {
        if(el.id.indexOf(idNamePart) !== -1) {
            el.disabled = useParent;
        }
    };

    element.up(2).select('select[multiple]').each(function(el) {
        if(typeof dependentAttrs == 'string') {
            changeState(el, dependentAttrs);
        } else {
            for(var index in dependentAttrs) {
                changeState(el, dependentAttrs[index]);
            }
        }
    });
}

function generateReminders(url, button) {
    DRjQuery(button).attr('disabled', true).toggleClass('disabled');
    $('loading-mask').show();
    DRjQuery.ajax({
        url: url,
        timeout: 0,
        success: function (response) {
            $('loading-mask').hide();
            var jsonObj = JSON.parse(response);
            if (jsonObj.success) {
                $('messages').insert('<ul class="messages"><li class="success-msg"><ul><li><span>' + jsonObj.message + '</span></li></ul></li></ul>');
                setTimeout(function() {
                    window.location.reload();
                }, 2000)
            } else {
                $('messages').insert('<ul class="messages"><li class="success-msg"><ul><li><span>' + jsonObj.message + '</span></li></ul></li></ul>');
            }
        },
        error: function () {
            $('loading-mask').hide();
            $('messages').insert('<ul class="messages"><li class="error-msg"><ul><li><span>Something went wrong</span></li></ul></li></ul>');
        },
        complete: function () {
            DRjQuery(button).attr('disabled', false).toggleClass('disabled');
        }
    });
};
