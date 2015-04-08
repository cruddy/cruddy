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
        @filterModel.entity = @entity

        @listenTo @model, "request", => @_toggleButtons yes
        @listenTo @model, "data", => @_toggleButtons no
        @listenTo @model, "change", => @_setDataFromDataSource() unless @_applying

        @_setDataFromDataSource()

        this

    _toggleButtons: (disabled) ->
        @$buttons.prop "disabled", disabled

        return

    apply: ->
        @_applying = yes

        @model.set $.extend @_prepareData(), page: 1

        @_applying = no

        return this

    _prepareData: ->
        data = {}

        for key, value of @filterModel.attributes when filter = @availableFilters.get(key)
            data[filter.getDataKey()] = filter.prepareData(value)

        return data

    _setDataFromDataSource: ->
        data = {}

        for filter in @availableFilters.models
            data[filter.id] = filter.parseData @model.get filter.getDataKey()

        @filterModel.set data

        return this

    reset: ->
        input.empty() for input in @filters

        @apply()

    render: ->
        @dispose()

        @$el.html @template()
        @items = @$ ".filter-list-container"

        for filter in @availableFilters.models
            continue unless input = filter.createFilterInput @filterModel

            @filters.push input
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