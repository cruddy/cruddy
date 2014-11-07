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

        @listenTo @model, "request", => @toggleButtons yes
        @listenTo @model, "data", => @toggleButtons no

        this

    toggleButtons: (disabled) ->
        @$buttons.prop "disabled", disabled

        return

    apply: ->
        @model.filter.set @getFiltersData()

        return this

    getFiltersData: ->
        data = {}

        data[key] = filter.prepareData value for key, value of @filterModel.attributes when filter = @availableFilters.get key

        return data

    reset: ->
        input.empty() for input in @filters

        @apply()

    render: ->
        @dispose()

        @$el.html @template()
        @items = @$ ".filter-list-container"

        for filter in @availableFilters.models
            @filters.push input = filter.createFilterInput @filterModel
            @items.append input.render().el
            input.$el.wrap("""<div class="form-group #{ filter.getClass() }"></div>""").parent().before "<label>#{ filter.getLabel() }</label>"

        @$buttons = @$el.find ".fl-btn"

        this

    template: -> """
        <div class="filter-list-container"></div>
        <button type="button" class="btn fl-btn btn-primary btn-apply">#{ Cruddy.lang.filter_apply }</button>
        <button type="button" class="btn fl-btn btn-default btn-reset">#{ Cruddy.lang.filter_reset }</button>
    """

    dispose: ->
        filter.remove() for filter in @filters if @filters?

        @filters = []

        this

    remove: ->
        @dispose()

        super