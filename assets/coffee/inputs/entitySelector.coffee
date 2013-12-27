class EntitySelector extends BaseInput
    className: "entity-selector"

    events:
        "click li": "check"

    initialize: (options) ->
        super

        @filter = options.filter ? false
        @multiple = options.multiple ? false

        @data = []
        @buildSelected @model.get @key

        Cruddy.app.entity(options.reference).then (entity) =>
            @entity = entity
            @primaryKey = "id"
            @primaryColumn = entity.get "primary_column"

            @dataSource = entity.search()

            @listenTo @dataSource, "request",   @loading
            @listenTo @dataSource, "data",      @appendItems

        this

    check: (e) ->
        id = parseInt $(e.target).data "id"
        uncheck = id of @selected
        item = _.find @data, (item) -> item.id == id

        if @multiple
            if uncheck
                value = _.filter @model.get(@key), (item) -> item.id != id
            else
                value = _.clone @model.get(@key)
                value.push item
        else
            value = item

        @model.set @key, value

        false

    applyChanges: (model, data) ->
        @buildSelected data
        @renderItems()

    buildSelected: (data) ->
        @selected = {}

        if @multiple
            @selected[item.id] = yes for item in data
        else
            @selected[data.id] = yes if data?

        this

    loading: -> this

    appendItems: (datasource, data) ->
        return if _.isEmpty data

        @data.push { id: item[@primaryKey], title: item[@primaryColumn] } for item in data

        @renderItems()

        this

    renderItems: ->
        html = ""
        html += @renderItem item for item in @data
        @items.html html

        this

    renderItem: (item) ->
        className = if item.id of @selected then "selected" else ""

        """<li class="#{ className }" data-id="#{ item.id }">#{ item.title }</li>"""

    render: ->
        @dispose()

        @$el.html @template()

        @items = @$ ".items"

        @appendItems @dataSource, @dataSource.get "data" if @dataSource? and @dataSource.hasData()

        this

    template: -> """<div class="items-container"><ul class="items"></ul></div>"""

    dispose: ->
        this

    stopListening: ->
        @dispose()

        super
