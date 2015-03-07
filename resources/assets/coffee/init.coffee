Cruddy = window.Cruddy || {}

TRANSITIONEND = "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd"
NOT_AVAILABLE = "&mdash;"
TITLE_SEPARATOR = " / "
VALIDATION_FAILED_CODE = 422

moment.lang [ Cruddy.locale, "en" ]

Backbone.emulateHTTP = true
Backbone.emulateJSON = true

$(document)
    .ajaxSend (e, xhr, options) ->
        options.displayLoading = no if not Cruddy.app
        Cruddy.app.startLoading() if options.displayLoading

        xhr.setRequestHeader "X-CSRF-TOKEN", Cruddy.token

        return

    .ajaxComplete (e, xhr, options) ->
        Cruddy.app.doneLoading() if options.displayLoading

        return

$(document.body)
    .on "click", "[data-trigger=fancybox]", (e) ->
        return no if $.fancybox.open(e.currentTarget) isnt false

        return

$.extend $.fancybox.defaults,
    openEffect: "elastic"