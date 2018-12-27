class Stats extends Backbone.View
    className: "stats-list"

    initialize: (options) ->
        @entity = options.entity
        @listenTo @model, "data", => @render()

        this

    render: ->
        @$el.html ""

        for key, value of @model.getStats()
            @$el.append """<div class="stat-item"><span class="title">#{ key }</span><span class="value">#{ value }</span></div>"""

        this