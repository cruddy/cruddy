class EntityInstance extends Backbone.Model
    initialize: (attributes, options) ->
        @entity = options.entity
        @related = options.related
        @original = _.clone attributes

        @on "error", @processError, this
        @on "sync", => @original = _.clone @attributes
        @on "destroy", => @set "deleted_at", moment().unix() if @entity.get "soft_deleting"

    processError: (model, xhr) ->
        @trigger "invalid", this, xhr.responseJSON.data if xhr.responseJSON? and xhr.responseJSON.error is "VALIDATION"

    validate: ->
        @set "errors", {}
        null

    link: -> @entity.link @id

    url: -> @entity.url @id

    sync: (method, model, options) ->
        if method in ["update", "create"]
            # Form data will allow us to upload files via AJAX request
            options.data = new AdvFormData(options.attrs ? @attributes).original

            # Set the content type to false to let browser handle it
            options.contentType = false
            options.processData = false

        super

    save: ->
        xhr = super

        return xhr if _.isEmpty @related

        queue = (xhr) =>
            save = []

            save.push xhr if xhr?

            for key, model of @related
                @entity.related.get(key).associate @, model if model.isNew()

                save.push model.save() if model.hasChangedSinceSync()

            $.when.apply $, save

        # Create related models after the main model is saved
        if @isNew() then xhr.then (resp) -> queue() else queue xhr

    parse: (resp) -> resp.data

    hasChangedSinceSync: ->
        return yes for key, value of @attributes when not _.isEqual value, @original[key]

        # Related models do not affect the result unless model is created
        return yes for key, related of @related when related.hasChangedSinceSync() unless @isNew()

        no

    isSaveable: -> (@isNew() and @entity.get("can_create")) or (!@isNew() and @entity.get("can_update"))