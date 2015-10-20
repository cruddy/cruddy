class Cruddy.Fields.RelatedCollection extends Backbone.Collection

    initialize: (items, options) ->
        @entity = options.entity
        @owner = options.owner
        @field = options.field
        @maxItems = options.maxItems

        # The flag is set when user has deleted some items
        @deleted = no
        @removedSoftly = 0

        @listenTo @owner, "sync", (model, resp, options) ->
            @deleted = no
            @_triggerItems "sync", {}, options

        @listenTo @owner, "request", (model, xhr, options) -> @_triggerItems "request", xhr, options
        @listenTo @owner, "invalid", @_handleInvalidEvent

        super

    _handleInvalidEvent: (model, errors) ->
        return unless @field.id of errors

        for cid, itemErrors of errors[@field.id] when item = @get cid
            item.trigger "invalid", item, itemErrors

        return

    _triggerItems: (event, param1, param2) ->
        model.trigger event, model, param1, param2 for model in @models

        return

    add: ->
        @removeSoftDeleted() if @maxItems and @models.length >= @maxItems

        super

    removeSoftDeleted: -> @remove @filter((m) -> m.isDeleted)

    remove: (models) ->
        @deleted = yes

        if _.isArray models
            @removedSoftly-- for item in models when item.isDeleted
        else
            @removedSoftly-- if modes.isDeleted

        super

    removeSoftly: (m) ->
        return if m.isDeleted

        m.isDeleted = yes
        @removedSoftly++

        @trigger "removeSoftly", m

        return this

    restore: (m) ->
        return if not m.isDeleted

        m.isDeleted = no
        @removedSoftly--

        @trigger "restore", m

        return this

    hasSpots: (num = 1)-> not @maxItems? or @models.length - @removedSoftly + num <= @maxItems

    hasChangedSinceSync: ->
        return yes if @deleted or @removedSoftly
        return yes for item in @models when item.hasChangedSinceSync()

        no

    copy: (copy) ->
        items = if @field.isUnique() then [] else (item.copy() for item in @models)

        new Cruddy.Fields.RelatedCollection items,
            owner: copy
            field: @field

    serialize: ->
        models = @filter (model) -> model.canBeSaved()

        data = {}
        data[item.cid] = item.serialize() for item in models

        return data

    modelId: -> @entity.getPrimaryKey()