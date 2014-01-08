humanize = (id) => id.replace(/_-/, " ")

entity_url = (id, extra) ->
    url = Cruddy.baseUrl + "/api/v1/entity/" + id;
    url += "/" + extra if extra

    url

after_break = (callback) -> setTimeout callback, 50

thumb = (src, width, height) ->
    url = "#{ Cruddy.baseUrl }/thumb?src=#{ encodeURIComponent(src) }"
    url += "&amp;width=#{ width }" if width
    url += "&amp;height=#{ height }" if height

    url

class Alert extends Backbone.View
    tagName: "span"
    className: "alert"

    initialize: (options) ->
        @$el.addClass @className + "-" + options.type ? "info"
        @$el.text options.message

        setTimeout (=> @remove()), options.timeout if options.timeout?

        this