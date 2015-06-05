class Cruddy.Fields.EmbeddedView extends Cruddy.Fields.BaseView
    className: "has-many-view"

    events:
        "click .btn-create": "create"

    initialize: (options) ->
        @views = {}

        @collection = collection = @model.get @field.id

        @listenTo collection, "add", @add
        @listenTo collection, "remove", @removeItem
        @listenTo collection, "removeSoftly restore", @update
        @listenTo collection, "reset", @render

        super

    handleInvalid: (model, errors) ->
        super if @field.id of errors and errors[@field.id].length

        this

    create: (e) ->
        e.preventDefault()
        e.stopPropagation()

        @collection.add @field.getReferencedEntity().createInstance(), focus: yes

        this

    add: (model, collection, options) ->
        itemOptions =
            model: model
            collection: @collection
            disable: not @isEditable

        @views[model.cid] = view = new Cruddy.Fields.EmbeddedItemView itemOptions, this

        @body.append view.render().el

        after_break( -> view.focus()) if options?.focus

        @focusable = view if not @focusable

        @update()

        this

    removeItem: (model) ->
        if view = @views[model.cid]
            view.remove()
            delete @views[model.cid]

        @update()

        this

    render: ->
        @dispose()

        @$el.html @template()
        @body = @$component "body"
        @createButton = @$ ".btn-create"

        @add model for model in @collection.models

        super

    update: ->
        @createButton.toggle @field.isMultiple() or @collection.hasSpots()

        this

    template: ->
        buttons = if @canCreate() then b_btn("", "plus", ["default", "create"]) else ""

        """
        <div class='header field-label'>
            #{ @helpTemplate() }#{ _.escape @field.getLabel() } #{ buttons }
        </div>
        <div class="error-container has-error">#{ @errorTemplate() }</div>
        <div class="body" id="#{ @componentId "body" }"></div>
        """

    canCreate: -> @isEditable and @field.getReferencedEntity().createPermitted()

    dispose: ->
        view.remove() for cid, view of @views
        @views = {}
        @focusable = null

        this

    remove: ->
        @dispose()

        super

    isFocusable: ->
        return no if not super

        return (@field.isMultiple() and @canCreate()) or (not @field.isMultiple() and @focusable?)

    focus: ->
        if @field.isMultiple() then @createButton[0]?.focus() else @focusable?.focus()

        this