class EntitySelector extends BaseInput
    className: "entity-selector"

    events:
        "click .item": "check"
        "click .more": "more"
        "click .search-input": -> false

    initialize: (options) ->
        super

        @filter = options.filter ? false
        @multiple = options.multiple ? false
        @search = options.search ? true

        @data = []
        @buildSelected @model.get @key

        entity = Cruddy.app.entity(options.reference)

        entity.done (entity) =>
            @entity = entity
            @primaryKey = "id"
            @primaryColumn = entity.get "primary_column"

            @dataSource = entity.search()

            @listenTo @dataSource, "request", @loading
            @listenTo @dataSource, "data",    @renderItems
            @listenTo @dataSource, "error",   @displayError

            @renderSearch() if @items?

        entity.fail $.proxy this, "displayError"

        this

    checkForMore: ->
        @more() if @items.parent().height() + 50 > @moreElement?.position().top

        this

    check: (e) ->
        id = parseInt $(e.target).data "id"
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
        xhr.handled = yes

        error = if xhr.status is 403 then "Ошибка доступа" else "Ошибка"

        @$el.html "<span class=error>#{ error }</span>"

        this

    loading: ->
        @moreElement.addClass "loading"

        this

    renderItems: ->
        @moreElement = null

        html = ""

        if @dataSource?
            html += @renderItem item for item in @dataSource.data

            html += """<li class="more #{ if @dataSource.inProgress() then "loading" else "" }">еще</li>""" if @dataSource.more

        @items.html html

        if @dataSource?.more
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

        @renderItems()

        @items.parent().on "scroll", $.proxy this, "checkForMore"

        @renderSearch() if @dataSource?

        this

    renderSearch: ->
        return if not @search

        @searchInput = new TextInput
            model: @dataSource
            key: "search"
            continous: yes
            attributes:
                placeholder: "поиск"
            className: "form-control search-input"

        @$el.prepend @searchInput.render().el

        @searchInput.$el.wrap "<div class=search-input-container></div>"

        this

    template: -> """<div class="items-container"><ul class="items"></ul></div>"""

    focus: ->
        @searchInput?.focus()

        this

    dispose: ->
        @searchInput?.remove()

        this

    remove: ->
        @dispose()

        super
