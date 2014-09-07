Cruddy = window.Cruddy || {}

Cruddy.backendRoot = Cruddy.root + "/" + Cruddy.uri

API_URL = "/backend/api/v1"
TRANSITIONEND = "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd"
NOT_AVAILABLE = "&mdash;"
moment.lang Cruddy.locale ? "en"

Backbone.emulateHTTP = true
Backbone.emulateJSON = true

#$(document).ajaxError (e, xhr, options) =>
#    location.href = "/login" if xhr.status is 403 and not options.dontRedirect

$(document)
    .ajaxSend (e, xhr, options) ->
        options.displayLoading = no if not Cruddy.app
        Cruddy.app.startLoading() if options.displayLoading

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