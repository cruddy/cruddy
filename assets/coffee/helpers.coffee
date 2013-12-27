humanize = (id) => id.replace(/_-/, " ")

class Alert extends Backbone.View
    tagName: "span"
    className: "alert"

    initialize: (options) ->
        @$el.addClass @className + "-" + options.type ? "info"
        @$el.text options.message

        setTimeout (=> @remove()), options.timeout if options.timeout?

        this