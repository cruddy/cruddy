class Cruddy.Inputs.EntityDropdown extends Cruddy.Inputs.Base
    className: "entity-dropdown"

    events:
        "click .ed-item>.input-group-btn>.btn-remove": "removeItem"
        "click .ed-item>.input-group-btn>.btn-edit": "editItem"
        "click .ed-item>.form-control": "executeFirstAction"
        "keydown .ed-item>.form-control": "itemKeydown"
        "keydown [type=search]": "searchKeydown"
        "show.bs.dropdown": "renderDropdown"

        "shown.bs.dropdown": ->
            after_break => @selector.focus()

            this

        "hide.bs.dropdown": (e) ->
            @opened = no

            return

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

        if options.constraint
            @constraint = options.constraint
            @listenTo @model, "change:" + @constraint.field, -> @checkToDisable().applyConstraint yes

        super

    getKey: (e) -> $(e.currentTarget).closest(".ed-item").data "key"

    getValue: -> super or if @multiple then [] else null

    removeItem: (e) ->
        if @multiple
            i = @getKey e
            value = _.clone @getValue()
            value.splice i, 1
        else
            value = null

        @setValue value

    executeFirstAction: (e) ->
        $(".btn:not(:disabled):last", $(e.currentTarget).next()).trigger "click"

        return false

    editItem: (e) ->
        return if @editing or not @allowEdit

        item = @model.get @key
        item = item[@getKey e] if @multiple

        return if not item

        btn = $(e.currentTarget)

        # We'll look for the button if it is form control that was clicked
        btn = btn.next().children(".btn-edit") if btn.is ".form-control"

        btn.prop "disabled", yes

        @editing = @reference.load(item.id).done (instance) =>
            @editingForm = form = Cruddy.Entity.Form.display instance

            form.once "saved", (model) =>
                btn.parent().siblings(".form-control").text model.getTitle()
                form.remove()

            form.once "destroyed", (model) => @removeItem e
            form.once "remove", => @editingForm = null

        @editing.always =>
            @editing = null
            btn.prop "disabled", no

        this

    searchKeydown: (e) ->
        if (e.keyCode is 27)
            @selector.$el.dropdown "toggle" if @selector

            return false

        return

    itemKeydown: (e) ->
        if (e.keyCode is 13)
            @executeFirstAction e

            return false

        return

    applyConstraint: (reset = no) ->
        if @selector
            field = @model.entity.getField @constraint.field
            value = @model.get @constraint.field
            @selector.dataSource?.set "constraint", field.prepareAttribute value
            @selector.attributesForNewModel[@constraint.otherField] = value

        @model.set(@key, if @multiple then [] else null) if reset

        this

    checkToDisable: ->
        if not @enabled or @constraint and _.isEmpty(@model.get @constraint.field) then @disable() else @enable()

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
        @$el.toggleClass "disabled", @disableDropdown

        this

    renderDropdown: (e) ->
        if @disableDropdown
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

        @toggleOpenDirection()

        return

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
        html += @itemTemplate @itemToString(value), key for value, key in @getValue()
        @items.html html
        @items.toggleClass "has-items", html isnt ""

        this

    renderSingle: ->
        @$el.html @itemTemplate @placeholder, "0"

        @itemTitle = @$ ".form-control"
        @itemDelete = @$ ".btn-remove"
        @itemEdit = @$ ".btn-edit"

        @updateItem()

    updateItem: ->
        value = @getValue()

        @itemTitle.text if value then @itemToString(value) else @placeholder

        @itemDelete.toggle !!value
        @itemEdit.toggle !!value

        this

    itemToString: (item) ->
        return item.title if item.title?

        return item.id unless @selector?

        data = @selector.dataSource.getById item.id

        return if data? then data.title else item.id

    itemTemplate: (value, key = null) ->
        html = """
            <div class="input-group ed-item #{ if not @multiple then "ed-dropdown-toggle" else "" }" data-key="#{ key }">
                <div class="form-control">#{ _.escape value }</div>
            """

        html += """
            <div class="input-group-btn">
                #{ buttons }
            </div>
            """ if not _.isEmpty buttons = @buttonsTemplate()

        html += "</div>"

    buttonsTemplate: ->
        html = ""

        html += """
            <button type="button" class="btn btn-default btn-remove" tabindex="-1" title="#{ Cruddy.lang.reset }">
                <span class="glyphicon glyphicon-remove"></span>
            </button>
            """ if @enabled

        html += """
            <button type="button" class="btn btn-default btn-edit" tabindex="-1" title="#{ Cruddy.lang.edit }">
                <span class="glyphicon glyphicon-pencil"></span>
            </button>
            """ if @allowEdit

        html += """
            <button type="button" class="btn btn-default btn-dropdown dropdown-toggle" data-toggle="dropdown" id="#{ @cid }-dropdown" data-target="##{ @cid }" tab-index="1" title="#{ Cruddy.lang.list_show }">
                <span class="glyphicon glyphicon-search"></span>
            </button>
            """ if not @multiple

        html

    focus: ->
        $el = @$component("dropdown")
        $el = $el.parent().prev() if not @multiple

        $el[0].focus()

        $el.trigger("click") if _.isEmpty @getValue()

        this

    emptyValue: -> if @multiple then [] else null

    dispose: ->
        @selector?.remove()
        @editingForm?.remove()

        this

    remove: ->
        @dispose()

        super