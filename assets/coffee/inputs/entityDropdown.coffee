class Cruddy.Inputs.EntityDropdown extends Cruddy.Inputs.Base
    className: "entity-dropdown"

    events:
        "click .btn-remove": "removeItem"
        "click .btn-edit": "editItem"
        "click .form-control": "maybeEditItem"
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
        @owner = options.owner if options.owner?

        # Whether to show edit button (pencil)
        @allowEdit = options.allowEdit ? yes and @reference.updatePermitted()

        @placeholder = options.placeholder ? Cruddy.lang.not_selected

        # Whether the drop down is enabled
        @enabled = options.enabled ? true

        # Whether the item is currently editing
        @editing = false

        # Whether to not allow to open a dropdown
        @disableDropdown = false

        # Whether the dropdown is opened
        @opened = false

        @$el.addClass "disabled" if not @enabled

        if options.constraint
            @constraint = options.constraint
            @listenTo @model, "change:" + @constraint.field, -> @checkToDisable().applyConstraint yes

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

    maybeEditItem: (e) -> @editItem e if @multiple or not @enabled

    editItem: (e) ->
        console.log @editing

        return if @editing or not @allowEdit

        item = @model.get @key
        item = item[@getKey e] if @multiple

        return if not item

        target = $(e.currentTarget)

        # We'll look for the button if it is form control that was clicked
        target = target.next().children(".btn-edit") if target.is ".form-control"

        target.prop "disabled", yes

        @editing = @reference.load(item.id).done (instance) =>
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

        @editing.always =>
            @editing = no
            target.prop "disabled", no

        this

    searchKeydown: (e) ->
        if (e.keyCode is 27)
            @$el.dropdown "toggle"
            return false

    applyConstraint: (reset = no) ->
        if @selector
            value = @model.get @constraint.field
            @selector.dataSource?.filters.set @constraint.otherField, value
            @selector.createAttributes[@constraint.otherField] = value

        @model.set(@key, if @multiple then [] else null) if reset

        this

    checkToDisable: ->
        (if _.isEmpty @model.get @constraint.field then @disable() else @enable()) if @constraint and @enabled

        this

    disable: ->
        return this if @disableDropdown

        @disableDropdown = yes

        @toggleDisableControls()

    enable: ->
        return this if not @disableDropdown

        @disableDropdown = no

        @toggleDisableControls()

    toggleDisableControls: ->
        @dropdownBtn.prop "disabled", @disableDropdown
        @itemTitle.prop "disabled", @disableDropdown if not @multiple

        this

    renderDropdown: (e) ->
        if @disableDropdown or not @enabled
            e.preventDefault()

            return

        @opened = yes

        if not @selector
            @selector = new Cruddy.Inputs.EntitySelector
                model: @model
                key: @key
                multiple: @multiple
                reference: @reference
                allowCreate: @allowEdit
                owner: @owner

            @applyConstraint() if @constraint

            @$el.append @selector.render().el

        dataSource = @selector.dataSource

        dataSource.refresh() if not dataSource.inProgress()

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

        @dropdownBtn = @$ "##{ @cid }-dropdown"

        @$el.attr "id", @cid

        @checkToDisable()

        this

    renderMultiple: ->
        @$el.append @items = $ "<div>", class: "items"

        @$el.append """
            <button type="button" class="btn btn-default btn-block dropdown-toggle ed-dropdown-toggle" data-toggle="dropdown" id="#{ @cid }-dropdown" data-target="##{ @cid }">
                #{ Cruddy.lang.choose }
                <span class="caret"></span>
            </button>
            """ if @enabled

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
        @itemTitle.val if value then value.title else ""
        @itemDelete.toggle !!value
        @itemEdit.toggle !!value

        this

    itemTemplate: (value, key = null) ->
        html = """
            <div class="input-group input-group ed-item #{ if not @multiple then "ed-dropdown-toggle" else "" }" data-key="#{ key }">
                <input type="text" class="form-control" #{ if not @multiple then "data-toggle='dropdown' data-target='##{ @cid }' placeholder='#{ @placeholder }'" else "tab-index='-1'"} value="#{ _.escape value }" readonly>
            """

        html += """
            <div class="input-group-btn">
                #{ buttons }
            </div>
            """ if not _.isEmpty buttons = @buttonsTemplate()

        html += "</div></div>"

    buttonsTemplate: ->
        html = ""

        html += """
            <button type="button" class="btn btn-default btn-edit" tabindex="-1">
                <span class="glyphicon glyphicon-pencil"></span>
            </button>
            """ if @allowEdit

        html += """
            <button type="button" class="btn btn-default btn-remove" tabindex="-1">
                <span class="glyphicon glyphicon-remove"></span>
            </button>
            """ if @enabled

        html += """
            <button type="button" class="btn btn-default btn-dropdown dropdown-toggle" data-toggle="dropdown" id="#{ @cid }-dropdown" data-target="##{ @cid }" tab-index="1">
                <span class="glyphicon glyphicon-search"></span>
            </button>
            """ if @enabled and not @multiple

        html

    focus: ->
        @$component("dropdown").trigger("click")[0].focus()

        this

    dispose: ->
        @selector?.remove()
        @innerForm?.remove()

        this

    remove: ->
        @dispose()

        super