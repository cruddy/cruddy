humanize = (id) => id.replace(/_-/, " ")

entity_url = (id, extra) ->
    url = Cruddy.root + "/" + Cruddy.uri + "/api/v1/entity/" + id;
    url += "/" + extra if extra

    url

after_break = (callback) -> setTimeout callback, 50

class Alert extends Backbone.View
    tagName: "span"
    className: "alert"

    initialize: (options) ->
        @$el.addClass @className + "-" + options.type ? "info"
        @$el.text options.message

        setTimeout (=> @remove()), options.timeout if options.timeout?

        this