class EntitySelector extends BaseInput
    className: "entity-selector"

    events:
        "click .item": "check"
        "click .more": "more"
        "click [type=search]": -> false

    initialize: (options) ->
        super

        @filter = options.filter ? false
        @multiple = options.multiple ? false
        @search = options.search ? true

        @data = []
        @buildSelected @model.get @key

        @entity = Cruddy.app.entity(options.reference)

        @entity.done (entity) =>
            @primaryKey = "id"
            @primaryColumn = entity.get "primary_column"

            @dataSource = entity.search()

            @listenTo @dataSource, "request", @loading
            @listenTo @dataSource, "data",    @renderItems
            @listenTo @dataSource, "error",   @displayError

        @entity.fail $.proxy this, "displayError"

        this

    checkForMore: ->
        @more() if @moreElement? and @items.parent().height() + 50 > @moreElement.position().top

        this

    check: (e) ->
        id = $(e.target).data("id").toString()
        uncheck = id of @selected
        item = _.find @dataSource.data, (item) -> item.id == id

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

    more: ->
        return if not @dataSource or @dataSource.inProgress()

        @dataSource.next()

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

    displayError: (xhr) ->
        return if xhr.status isnt 403

        @$el.html "<span class=error>Ошибка доступа</span>"

        this

    loading: ->
        @moreElement?.addClass "loading"

        this

    renderItems: ->
        @moreElement = null

        html = ""

        if @dataSource.data.length or @dataSource.more
            html += @renderItem item for item in @dataSource.data

            html += """<li class="more #{ if @dataSource.inProgress() then "loading" else "" }">еще</li>""" if @dataSource.more
        else
            html += "<li class='empty'>нет результатов</li>"

        @items.html html

        if @dataSource.more
            @moreElement = @items.children ".more"
            @checkForMore()

        this

    renderItem: (item) ->
        className = if item.id of @selected then "selected" else ""

        """<li class="item #{ className }" data-id="#{ item.id }">#{ item.title }</li>"""

    render: ->
        @dispose()

        @$el.html @template()

        @items = @$ ".items"

        @entity.done =>
            @renderItems()

            @items.parent().on "scroll", $.proxy this, "checkForMore"

            @renderSearch()

        this

    renderSearch: ->
        @searchInput = new SearchInput
            model: @dataSource
            key: "search"

        @$el.prepend @searchInput.render().el

        @searchInput.$el.wrap "<div class=search-input-container></div>"

        this

    template: -> """<div class="items-container"><ul class="items"><li class="more loading"></li></ul></div>"""

    focus: ->
        @searchInput?.focus() or @entity.done => @searchInput.focus()

        this

    dispose: ->
        @searchInput?.remove()

        this

    remove: ->
        @dispose()

        super
