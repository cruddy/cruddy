class FilterList extends Backbone.View
    className: "filter-list"

    tagName: "fieldset"

    initialize: (options) ->
        @entity = options.entity
        @availableFilters = options.filters

        this

    render: ->
        @dispose()

        @$el.html @template()
        @items = @$ ".filter-list-container"

        for filter in @availableFilters when (field = @entity.fields.get filter) and field.canFilter() and (input = field.createFilterInput @model)
            @filters.push input
            @items.append input.render().el
            input.$el.wrap("""<div class="form-group filter filter-#{ field.id }"></div>""").parent().before "<label>#{ field.getFilterLabel() }</label>"

        this

    template: -> """<div class="filter-list-container"></div>"""

    dispose: ->
        filter.remove() for filter in @filters if @filters?

        @filters = []

        this

    remove: ->
        @dispose()

        super