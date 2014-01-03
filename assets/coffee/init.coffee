Cruddy = window.Cruddy || {}

API_URL = "/backend/api/v1"
TRANSITIONEND = "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd"
moment.lang Cruddy.locale ? "en"

Backbone.emulateHTTP = true
Backbone.emulateJSON = true

$(document).ajaxError (e, xhr) =>
    location.href = "/login" if xhr.status == 403