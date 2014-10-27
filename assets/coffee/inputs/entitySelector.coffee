class Cruddy.Inputs.EntitySelector extends Cruddy.Inputs.Base
    className: "entity-selector"

    events:
        "click .items>.item": "checkItem"
        "click .more": "loadMore"
        "click .btn-add": "showNewForm"
        "click .btn-refresh": "refresh"
        "click [type=search]": -> false

    initialize: (options) ->
        super

        @filter = options.filter ? false
        @multiple = options.multiple ? false
        @reference = options.reference

        @allowSearch = options.allowSearch ? yes
        @allowCreate = options.allowCreate ? yes and @reference.createPermitted()

        @attributesForNewModel = {}

        @makeSelectedMap @getValue()

        if @reference.viewPermitted()
            @primaryKey = "id"

            @dataSource = @reference.search ajaxOptions: data: owner: options.owner

            @listenTo @dataSource, "request", @displayLoading
            @listenTo @dataSource, "data",    @renderItems

        this

    displayLoading: (dataSource, xhr) ->
        @$el.addClass "loading"

        xhr.always => @$el.removeClass "loading"

        this

    maybeLoadMore: ->
        @loadMore() if @$more? and @items.parent().height() + 50 > @$more.position().top

        this

    refresh: (e) ->
        if e
            e.preventDefault()
            e.stopPropagation()

        @dataSource.refresh()

        return

    checkItem: (e) ->
        e.preventDefault()
        e.stopPropagation()

        @selectItem @dataSource.getById $(e.target).data("id")

        return

    selectItem: (item) ->
        return if not item

        if @multiple
            if item.id of @selected
                value = _.filter @model.get(@key), (item) -> item.id != id
            else
                value = _.clone @model.get(@key)
                value.push item
        else
            value = item

        @setValue value

    loadMore: ->
        return if not @dataSource or @dataSource.inProgress()

        @dataSource.next()

        false

    showNewForm: (e) ->
        if e
            e.preventDefault()
            e.stopPropagation()

        return if @newModelForm

        instance = @reference.createInstance attributes: @attributesForNewModel

        @newModelForm = form = Cruddy.Entity.Form.display instance

        form.once "remove", => @newModelForm = null

        form.once "created", (model, resp) =>
            @selectItem
                id: model.id
                title: model.title

            form.remove()

            return

        this

    applyChanges: (data) ->
        @makeSelectedMap data
        @renderItems()

    makeSelectedMap: (data) ->
        @selected = {}

        if @multiple
            @selected[item.id] = yes for item in data
        else
            @selected[data.id] = yes if data?

        this

    renderItems: ->
        @$more = null

        html = ""

        if @dataSource.data.length or @dataSource.more
            html += @renderItem item for item in @dataSource.data

            html += """<li class="more">#{ Cruddy.lang.more }</li>""" if @dataSource.more
        else
            html += """<li class="empty">#{ Cruddy.lang.no_results }</li>"""

        @items.html html

        if @dataSource.more
            @$more = @items.children ".more"
            @maybeLoadMore()

        this

    renderItem: (item) ->
        className = if item.id of @selected then "selected" else ""

        """<li class="item #{ className }" data-id="#{ item.id }">#{ item.title }</li>"""

    render: ->
        if @reference.viewPermitted()
            @dispose()

            @$el.html @template()

            @items = @$ ".items"

            @renderItems()

            @items.parent().on "scroll", $.proxy this, "maybeLoadMore"

            @renderSearch() if @allowSearch

            @dataSource.refresh() if @dataSource.isEmpty()
        else
            @$el.html "<span class=error>#{ Cruddy.lang.forbidden }</span>"

        this

    renderSearch: ->
        @searchInput = new Cruddy.Inputs.Search
            model: @dataSource
            key: "search"

        @$el.prepend @searchInput.render().$el

        @searchInput.$el.wrap "<div class=search-input-container></div>"

        @searchInput.appendButton """
            <button type="button" class="btn btn-default btn-refresh" tabindex="-1">
                <span class="glyphicon glyphicon-refresh"></span>
            </button>
        """

        @searchInput.appendButton """
            <button type="button" class='btn btn-default btn-add' tabindex='-1'>
                <span class='glyphicon glyphicon-plus'></span>
            </button>
        """ if @allowCreate

        this

    template: -> """<div class="items-container"><ul class="items"></ul></div>"""

    focus: ->
        @searchInput?.focus() or @entity.done => @searchInput.focus()

        this

    dispose: ->
        @searchInput?.remove()
        @newModelForm?.remove()

        this

    remove: ->
        @dispose()

        super
