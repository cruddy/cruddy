class EntityDropdown extends BaseInput
    className: "entity-dropdown"

    events:
        "click .btn-remove": "removeItem"
        "keydown [type=search]": "searchKeydown"
        "show.bs.dropdown": "renderDropdown"

        "shown.bs.dropdown": ->
            after_break => @selector.focus()

            this

        "hidden.bs.dropdown": ->
            @opened = no

            this

    mutiple: false
    reference: null

    initialize: (options) ->
        @multiple = options.multiple if options.multiple?
        @reference = options.reference if options.reference?
        @active = false

        super

    removeItem: (e) ->
        if @multiple
            i = $(e.currentTarget).data "key"
            value = _.clone @model.get(@key)
            value.splice i, 1
        else
            value = null

        @model.set @key, value

        this

    searchKeydown: (e) ->
        if (e.keyCode is 27)
            @$el.dropdown "toggle"
            return false

    renderDropdown: ->
        @opened = yes

        return @toggleOpenDirection() if @selector?

        @selector = new EntitySelector
            model: @model
            key: @key
            multiple: @multiple
            reference: @reference

        @selector.render().entity.done => @$el.append @selector.el

        @toggleOpenDirection()

    toggleOpenDirection: ->
        return if not @opened

        wnd = $(window)
        space = wnd.height() - @$el.offset().top - wnd.scrollTop() - @$el.parent(".field-list").scrollTop()

        targetClass = if space > 292 then "open-down" else "open-up"

        @$el.removeClass("open-up open-down").addClass targetClass if not @$el.hasClass targetClass

        this

    applyChanges: (model, value) ->
        if @multiple
            @renderItems()
        else
            @updateItem()
            @$el.removeClass "open"

        @toggleOpenDirection()

        this

    render: ->
        @dispose()

        if @multiple then @renderMultiple() else @renderSingle()

        @$el.attr "id", @cid

        this

    renderMultiple: ->
        @$el.append @items = $ "<div>", class: "items"

        @$el.append """
            <button type="button" class="btn btn-default btn-sm btn-block dropdown-toggle" data-toggle="dropdown" data-target="##{ @cid }">
                Выбрать
                <span class="caret"></span>
            </button>
            """

        @renderItems()

    renderItems: ->
        html = ""
        html += @itemTemplate value.title, key for value, key in @model.get @key
        @items.html html
        @items.toggleClass "has-items", html isnt ""

        this

    renderSingle: ->
        @$el.html @itemTemplate "", "0"

        @itemTitle = @$ ".form-control"
        @itemDelete = @$ ".btn-remove"

        @updateItem()

    updateItem: ->
        value = @model.get @key
        @itemTitle.val if value then value.title else "Не выбрано"
        @itemDelete.toggle !!value

        this

    itemTemplate: (value, key = null) ->
        html = """
        <div class="input-group input-group-sm ed-item">
            <input type="text" class="form-control" #{ if not @multiple or key is null then "data-toggle='dropdown' data-target='##{ @cid }'" else "tab-index='-1'"} value="#{ _.escape value }" readonly>
            <div class="input-group-btn">
        """

        if not @multiple or key isnt null
            html += """
                <button type="button" class="btn btn-default btn-remove" data-key="#{ key }" tabindex="-1">
                    <span class="glyphicon glyphicon-remove"></span>
                </button>
                """

        if not @multiple or key is null
            html += """
                <button type="button" class="btn btn-default btn-dropdown dropdown-toggle" data-toggle="dropdown" data-target="##{ @cid }" tab-index="1">
                    <span class="glyphicon glyphicon-search"></span>
                </button>
                """

        html += "</div></div>"

    dispose: ->
        @selector?.remove()

        this

    remove: ->
        @dispose()

        super