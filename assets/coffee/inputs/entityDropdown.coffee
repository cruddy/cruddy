class Cruddy.Inputs.EntityDropdown extends Cruddy.Inputs.Base
    className: "entity-dropdown"

    events:
        "click .btn-remove": "removeItem"
        "click .btn-edit": "editItem"
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
        @allowEdit = options.allowEdit ? yes
        @active = false

        super

    getKey: (e) -> $(e.currentTarget).closest(".ed-item").data "key"

    removeItem: (e) ->
        if @multiple
            i = @getKey e
            value = _.clone @model.get(@key)
            value.splice i, 1
        else
            value = null

        @setValue value

    editItem: (e) ->
        item = @model.get @key
        item = item[@getKey e] if @multiple

        return if not item

        target = $(e.currentTarget).prop "disabled", yes

        xhr = Cruddy.app.entity(@reference).then (entity) => entity.load(item.id).done (instance) =>
            @innerForm = new Cruddy.Entity.Form
                model: instance
                inner: yes

            @innerForm.render().$el.appendTo document.body
            after_break => @innerForm.show()

            @listenTo instance, "sync", (model, resp) =>
                # Check whether the model was destroyed
                if resp.data
                    target.parent().siblings("input").val resp.data.title
                    @innerForm.remove()
                else
                    @removeItem e

            @listenTo @innerForm, "remove", => @innerForm = null

        xhr.always -> target.prop "disabled", no

        this

    searchKeydown: (e) ->
        if (e.keyCode is 27)
            @$el.dropdown "toggle"
            return false

    renderDropdown: ->
        @opened = yes

        return @toggleOpenDirection() if @selector?

        @selector = new Cruddy.Inputs.EntitySelector
            model: @model
            key: @key
            multiple: @multiple
            reference: @reference
            allowCreate: @allowEdit

        @selector.render().entity.done => @$el.append @selector.el

        @toggleOpenDirection()

    toggleOpenDirection: ->
        return if not @opened

        wnd = $(window)
        space = wnd.height() - @$el.offset().top - wnd.scrollTop() - @$el.parent(".field-list").scrollTop()

        targetClass = if space > 292 then "open-down" else "open-up"

        @$el.removeClass("open-up open-down").addClass targetClass if not @$el.hasClass targetClass

        this

    applyChanges: (value) ->
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
            <button type="button" class="btn btn-default btn-block dropdown-toggle ed-dropdown-toggle" data-toggle="dropdown" data-target="##{ @cid }">
                Выбрать
                <span class="caret"></span>
            </button>
            """

        @renderItems()

    renderItems: ->
        html = ""
        html += @itemTemplate value.title, key for value, key in @getValue()
        @items.html html
        @items.toggleClass "has-items", html isnt ""

        this

    renderSingle: ->
        @$el.html @itemTemplate "", "0"

        @itemTitle = @$ ".form-control"
        @itemDelete = @$ ".btn-remove"
        @itemEdit = @$ ".btn-edit"

        @updateItem()

    updateItem: ->
        value = @getValue()
        @itemTitle.val if value then value.title else "Не выбрано"
        @itemDelete.toggle !!value
        @itemEdit.toggle !!value

        this

    itemTemplate: (value, key = null) ->
        html = """
        <div class="input-group input-group ed-item #{ if not @multiple then "ed-dropdown-toggle" else "" }" data-key="#{ key }">
            <input type="text" class="form-control" #{ if not @multiple then "data-toggle='dropdown' data-target='##{ @cid }'" else "tab-index='-1'"} value="#{ _.escape value }" readonly>
            <div class="input-group-btn">
        """

        html += """
            <button type="button" class="btn btn-default btn-edit" tabindex="-1">
                <span class="glyphicon glyphicon-pencil"></span>
            </button>
            """ if @allowEdit

        html += """
            <button type="button" class="btn btn-default btn-remove" tabindex="-1">
                <span class="glyphicon glyphicon-remove"></span>
            </button>
            """

        if not @multiple
            html += """
                <button type="button" class="btn btn-default btn-dropdown dropdown-toggle" data-toggle="dropdown" data-target="##{ @cid }" tab-index="1">
                    <span class="glyphicon glyphicon-search"></span>
                </button>
                """

        html += "</div></div>"

    dispose: ->
        @selector?.remove()
        @innerForm?.remove()

        this

    remove: ->
        @dispose()

        super