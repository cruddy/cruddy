Cruddy = window.Cruddy || {}

API_URL = "/backend/api/v1"
TRANSITIONEND = "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd"
moment.lang Cruddy.locale ? "en"

Backbone.emulateHTTP = true
Backbone.emulateJSON = true

#$(document).ajaxError (e, xhr, options) =>
#    location.href = "/login" if xhr.status is 403 and not options.dontRedirect

$(document)
    .ajaxSend((e, xhr, options) -> Cruddy.app.startLoading() if options.displayLoading)
    .ajaxComplete((e, xhr, options) -> Cruddy.app.doneLoading() if options.displayLoading)

$.extend $.fancybox.defaults,
    openEffect: "elastic"