class Cruddy.Fields.EmbeddedView extends Cruddy.Fields.BaseView
    className: "has-many-view"

    events:
        "click .btn-create": "create"

    initialize: (options) ->
        @views = {}
        @collection = @model.get @field.id

        @listenTo @collection, "add", @add
        @listenTo @collection, "remove", @removeItem

        super

    handleSync: ->
        super

        @render()

    handleInvalid: (model, errors) ->
        super if @field.id of errors and errors[@field.id].length

        this

    create: (e) ->
        e.preventDefault()
        e.stopPropagation()

        @collection.add @field.getReference().createInstance(), focus: yes

        this

    add: (model, collection, options) ->
        @views[model.cid] = view = new Cruddy.Fields.EmbeddedItemView
            model: model
            collection: @collection
            disabled: not @isEditable

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
        @body = @$ "##{ @cid }-body"
        @createButton = @$ ".btn-create"

        @add model for model in @collection.models

        super

    update: ->
        @createButton.toggle @field.isMultiple() or @collection.isEmpty()

        this

    template: ->
        ref = @field.getReference()

        buttons = if @isEditable and ref.createPermitted() then b_btn("", "plus", ["default", "create"]) else ""

        """
        <div class='header field-label'>
            #{ @helpTemplate() }#{ _.escape @field.getLabel() } #{ buttons }
        </div>
        <div class="error-container has-error">#{ @errorTemplate() }</div>
        <div class='body' id='#{ @cid }-body'></div>
        """

    dispose: ->
        view.remove() for cid, view of @views
        @views = {}
        @focusable = null

        this

    remove: ->
        @dispose()

        super

    focus: ->
        @focusable?.focus()

        this

class Cruddy.Fields.EmbeddedItemView extends Backbone.View
    className: "has-many-item-view"

    events:
        "click .btn-delete": "deleteItem"

    initialize: (options) ->
        @collection = options.collection
        @disabled = options.disabled ? true

        super

    deleteItem: (e) ->
        e.preventDefault()
        e.stopPropagation()

        @collection.remove @model

        this

    render: ->
        @dispose()

        @$el.html @template()

        @fieldList = new FieldList
            model: @model
            forceDisable: @disabled

        @$el.prepend @fieldList.render().el

        this

    template: -> if not @disabled and (@model.entity.deletePermitted() or @model.isNew()) then b_btn(Cruddy.lang.delete, "trash", ["default", "sm", "delete"]) else ""

    dispose: ->
        @fieldList?.remove()
        @fieldList = null

        this

    remove: ->
        @dispose()

        super

    focus: ->
        @fieldList?.focus()

        this

class Cruddy.Fields.RelatedCollection extends Backbone.Collection

    initialize: (items, options) ->
        @owner = options.owner
        @field = options.field

        # The flag is set when user has deleted some items
        @deleted = no

        @listenTo @owner, "sync", => @deleted = false

        super

    remove: ->
        @deleted = yes

        super

    hasChangedSinceSync: ->
        return yes if @deleted
        return yes for item in @models when item.hasChangedSinceSync()

        no

    copy: (copy) ->
        items = if @field.isUnique() then [] else (item.copy() for item in @models)

        new Cruddy.Fields.RelatedCollection items,
            owner: copy
            field: @field

    serialize: ->
        if @field.isMultiple()
            return "" if _.isEmpty @models

            data = {}

            data[item.cid] = item for item in @models

            data
        else
            @first()

class Cruddy.Fields.Embedded extends Cruddy.Fields.BaseRelation

    viewConstructor: Cruddy.Fields.EmbeddedView

    createInstance: (model, items) ->
        return items if items instanceof Backbone.Collection

        items = (if items or @isRequired(model) then [ items ] else []) if not @attributes.multiple

        ref = @getReference()
        items = (ref.createInstance item for item in items)

        new Cruddy.Fields.RelatedCollection items,
            owner: model
            field: this

    applyValues: (collection, items) ->
        items = [ items ] if not @attributes.multiple

        collection.set _.pluck(items, "attributes"), add: no

        # Add new items
        ref = @getReference()

        collection.add (ref.createInstance item for item in items when not collection.get item.id)

        this

    hasChangedSinceSync: (items) -> items.hasChangedSinceSync()

    copy: (copy, items) -> items.copy(copy)

    processErrors: (collection, errorsCollection) ->
        return if not _.isObject errorsCollection

        if not @attributes.multiple
            model = collection.first()
            model.trigger "invalid", model, errorsCollection if model

            return this

        for cid, errors of errorsCollection
            model = collection.get cid
            model.trigger "invalid", model, errors if model

        this

    triggerRelated: (event, collection, args) ->
        model.trigger.apply model, [ event, model ].concat(args) for model in collection.models

        this

    isMultiple: -> @attributes.multiple
