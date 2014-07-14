Cruddy = window.Cruddy || {}

Cruddy.baseUrl = Cruddy.root + "/" + Cruddy.uri

API_URL = "/backend/api/v1"
TRANSITIONEND = "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd"
NOT_AVAILABLE = "&mdash;"
moment.lang Cruddy.locale ? "en"

Backbone.emulateHTTP = true
Backbone.emulateJSON = true

#$(document).ajaxError (e, xhr, options) =>
#    location.href = "/login" if xhr.status is 403 and not options.dontRedirect

$(document)
    .ajaxSend((e, xhr, options) -> Cruddy.app.startLoading() if Cruddy.app and options.displayLoading)
    .ajaxComplete((e, xhr, options) -> Cruddy.app.doneLoading() if Cruddy.app and options.displayLoading)

$.extend $.fancybox.defaults,
    openEffect: "elastic"