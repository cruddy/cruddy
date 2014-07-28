class Cruddy.Inputs.EntitySelector extends Cruddy.Inputs.Base
    className: "entity-selector"

    events:
        "click .item": "check"
        "click .more": "more"
        "click .btn-add": "add"
        "click [type=search]": -> false

    initialize: (options) ->
        super

        @filter = options.filter ? false
        @multiple = options.multiple ? false
        @reference = options.reference

        @allowSearch = options.allowSearch ? yes
        @allowCreate = options.allowCreate ? yes and @reference.createPermitted()

        @createAttributes = {}

        @data = []
        @buildSelected @model.get @key

        if @reference.viewPermitted()
            @primaryKey = "id"

            @dataSource = @reference.search ajaxOptions: data: owner: options.owner

            @listenTo @dataSource, "request", @loading
            @listenTo @dataSource, "data",    @renderItems
            @listenTo @dataSource, "error",   @displayError

        this

    checkForMore: ->
        @more() if @moreElement? and @items.parent().height() + 50 > @moreElement.position().top

        this

    check: (e) ->
        id = $(e.target).data("id").toString()
        @select _.find @dataSource.data, (item) -> item.id.toString() == id

        false

    select: (item) ->
        if @multiple
            if item.id of @selected
                value = _.filter @model.get(@key), (item) -> item.id != id
            else
                value = _.clone @model.get(@key)
                value.push item
        else
            value = item

        @setValue value

    more: ->
        return if not @dataSource or @dataSource.inProgress()

        @dataSource.next()

        false

    add: (e) ->
        e.preventDefault()
        e.stopPropagation()

        instance = @reference.createInstance attributes: @createAttributes

        @innerForm = new Cruddy.Entity.Form
            model: instance
            inner: yes

        @innerForm.render().$el.appendTo document.body
        after_break => @innerForm.show()

        @listenToOnce @innerForm, "remove", => @innerForm = null

        @listenToOnce instance, "sync", (instance, resp) =>
            @select
                id: instance.id
                title: resp.data.title

            @dataSource.set "search", ""
            @innerForm.remove()

        this

    applyChanges: (data) ->
        @buildSelected data
        @renderItems()

    buildSelected: (data) ->
        @selected = {}

        if @multiple
            @selected[item.id] = yes for item in data
        else
            @selected[data.id] = yes if data?

        this

    loading: ->
        @moreElement?.addClass "loading"

        this

    renderItems: ->
        @moreElement = null

        html = ""

        if @dataSource.data.length or @dataSource.more
            html += @renderItem item for item in @dataSource.data

            html += """<li class="more #{ if @dataSource.inProgress() then "loading" else "" }">#{ Cruddy.lang.more }</li>""" if @dataSource.more
        else
            html += "<li class='empty'>#{ Cruddy.lang.no_results }</li>"

        @items.html html

        if @dataSource.more
            @moreElement = @items.children ".more"
            @checkForMore()

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

            @items.parent().on "scroll", $.proxy this, "checkForMore"

            @renderSearch() if @allowSearch
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
            <button type="button" class='btn btn-default btn-add' tabindex='-1'>
                <span class='glyphicon glyphicon-plus'></span>
            </button>
        """ if @allowCreate

        this

    template: -> """<div class="items-container"><ul class="items"><li class="more loading"></li></ul></div>"""

    focus: ->
        @searchInput?.focus() or @entity.done => @searchInput.focus()

        this

    dispose: ->
        @searchInput?.remove()
        @innerForm?.remove()

        this

    remove: ->
        @dispose()

        super
