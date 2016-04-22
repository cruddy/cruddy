class Cruddy.Entity.Instance extends Backbone.Model
    # Cid is used for identification during validation and we need this to be
    # numeric value rather than string to allow laravel's asterisk validation
    # We prefix with 0 to make sure that cid doesn't interfere with default
    # numeric primary keys
    cidPrefix: "0"

    idAttribute: "__id"

    constructor: (attributes, options) ->
        @entity = options.entity
        @meta = {}

        super

    initialize: (attributes, options) ->
        @syncOriginalAttributes()

        @on "error", @handleErrorEvent, this
        @on "sync", @handleSyncEvent, this
        @on "destroy", @handleDestroyEvent, this

        this

    syncOriginalAttributes: ->
        @original = _.clone @attributes

        return this

    handleSyncEvent: (model, resp) ->
        @syncOriginalAttributes()

        @setMetaFromResponse resp.model if resp?.model?

        this

    setMetaFromResponse: (resp) ->
        @meta = _.clone resp.meta if resp.meta?

        return this

    handleErrorEvent: (model, xhr) ->
        @trigger "invalid", this, xhr.responseJSON if xhr.status is VALIDATION_FAILED_CODE

        return

    handleDestroyEvent: (model) ->
        @isDeleted = yes

        return

    validate: ->
        @set "errors", {}

        return null

    link: -> @entity.link @id or "create"

    url: -> @entity.url @id

    set: (key, val, options) ->
        if _.isObject key
            for attributeId, value of key when field = @entity.getField attributeId
                key[attributeId] = field.parse this, value

        super

    sync: (method, model, options) ->
        if method in ["update", "create"]
            # Form data will allow us to upload files via AJAX request
            options.data = new AdvFormData(@entity.prepareAttributes options.attrs ? @attributes, this).original

            # Set the content type to false to let browser handle it
            options.contentType = false
            options.processData = false

        super

    parse: (resp) -> resp.model.attributes

    copy: ->
        copy = @entity.createInstance()

        copy.set @entity.getCopyableAttributes(this, copy), silent: yes

        copy

    hasChangedSinceSync: -> return @entity.hasChangedSinceSync this

    # Get whether is allowed to save instance
    canBeSaved: ->
        isNew = @isNew()

        return ((isNew and @entity.createPermitted()) or (not isNew and @entity.updatePermitted())) and (not @isDeleted or not isNew)

    serialize: ->
        data = if @isDeleted then {} else @entity.prepareAttributes @attributes, this

        return $.extend data, { __id: @id, __d: @isDeleted }

    # Get current action on the model
    action: -> if @isNew() then "create" else "update"

    getTitle: -> if @isNew() then Cruddy.lang.model_new_record else @meta.title

    getOriginal: (key) -> @original[key]