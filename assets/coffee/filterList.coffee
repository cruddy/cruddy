class FilterList extends Backbone.View
    className: "filter-list"

    tagName: "fieldset"

    initialize: (options) ->
        @entity = options.entity

        this

    render: ->
        @dispose()

        @$el.html @template()
        @items = @$ ".filter-list-container"

        @filters = []
        for col in @entity.columns.models when col.get "filterable"
            if input = col.createFilterInput @model
                @filters.push input
                @items.append input.render().el
                input.$el.wrap("""<div class="form-group filter #{ col.getClass() }"><div class="input-wrap"></div></div>""").parent().before "<label>#{ col.get "title" }</label>"

        this

    template: -> """<div class="filter-list-container"></div>"""

    dispose: ->
        filter.remove() for filter in @filters if @filters?

        this

    remove: ->
        @dispose()

        super