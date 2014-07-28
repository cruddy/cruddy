class FilterList extends Backbone.View
    className: "filter-list"

    tagName: "fieldset"

    events:
        "click .btn-apply": "apply"
        "click .btn-reset": "reset"

    initialize: (options) ->
        @entity = options.entity
        @availableFilters = options.filters
        @filterModel = new Backbone.Model

        @listenTo @model, "change", (model) -> @filterModel.set model.attributes

        this

    apply: ->
        @model.set @filterModel.attributes

        return this

    reset: ->
        input.empty() for input in @filters

        @apply()

    render: ->
        @dispose()

        @$el.html @template()
        @items = @$ ".filter-list-container"

        for filter in @availableFilters when (field = @entity.fields.get filter) and field.canFilter() and (input = field.createFilterInput @filterModel)
            @filters.push input
            @items.append input.render().el
            input.$el.wrap("""<div class="form-group filter filter-#{ field.id }"></div>""").parent().before "<label>#{ field.getFilterLabel() }</label>"

        this

    template: -> """
        <div class="filter-list-container"></div>
        <button type="button" class="btn btn-primary btn-apply">#{ Cruddy.lang.filter_apply }</button>
        <button type="button" class="btn btn-default btn-reset">#{ Cruddy.lang.filter_reset }</button>
    """

    dispose: ->
        filter.remove() for filter in @filters if @filters?

        @filters = []

        this

    remove: ->
        @dispose()

        super