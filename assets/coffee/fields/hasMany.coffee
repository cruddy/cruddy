class Cruddy.Fields.HasManyView extends Backbone.View
    className: "has-many-view"

    events:
        "click .btn-create": "create"

    initialize: (options) ->
        @field = options.field
        @views = {}
        @collection = @model.get @field.id

        @listenTo @collection, "add", @add
        @listenTo @collection, "remove", @removeItem

        super

    create: (e) ->
        e.preventDefault()
        e.stopPropagation()

        @collection.add @field.getReference().createInstance(), focus: yes

        this

    add: (model, collection, options) ->
        @views[model.cid] = view = new Cruddy.Fields.HasManyItemView
            model: model
            collection: @collection
            disabled: @field.isEditable()

        @body.append view.render().el

        after_break( -> view.focus()) if options?.focus

        @focusable = view if not @focusable

        this

    removeItem: (model) ->
        if view = @views[model.cid]
            view.remove()
            delete @views[model.cid]

        this

    render: ->
        @dispose()

        @$el.html @template()
        @body = @$ ".body"

        @add model for model in @collection.models

        this

    template: ->
        ref = @field.getReference()

        buttons = if ref.createPermitted() then b_btn("", "plus-sign", ["default", "create"]) else ""

        "<div class='header'>#{ @field.getReference().getPluralTitle() } #{ buttons }</div><div class='body'></div>"

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

class Cruddy.Fields.HasManyItemView extends Backbone.View
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
            disabled: @disabled or not @model.isSaveable()

        @$el.prepend @fieldList.render().el

        this

    template: -> if @model.entity.deletePermitted() then b_btn("Удалить", "trash", ["default", "sm", "delete"]) else ""

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

class Cruddy.Fields.HasMany extends Cruddy.Fields.BaseRelation
    viewConstructor: Cruddy.Fields.HasManyView

    createInstance: (items) ->
        return items if items instanceof Backbone.Collection

        ref = @getReference()
        items = (ref.createInstance item for item in items)

        new Backbone.Collection items

    applyValues: (collection, items) ->
        collection.set _.pluck(items, "attributes"), add: no

        # Add new items
        ref = @getReference()
        collection.add (ref.createInstance item for item in items when not collection.get item.id)

        this

    hasChangedSinceSync: (items) ->
        return yes for item in items.models when item.hasChangedSinceSync()

        no

    copy: (items) -> new Backbone.Collection if @isUnique() then [] else (item.copy() for item in items.models)

    processErrors: (collection, errorsCollection) ->
        for cid, errors of errorsCollection
            model = collection.get cid
            model.trigger "invalid", model, errors if model

        this

    triggerRelated: (event, collection, args) ->
        model.trigger.apply model, [ event, model ].concat(args) for model in collection.models

        this
