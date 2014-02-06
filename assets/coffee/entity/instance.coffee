class Cruddy.Entity.Instance extends Backbone.Model
    constructor: (attributes, options) ->
        @entity = options.entity
        @related = {}

        super
        
    initialize: (attributes, options) ->
        @original = _.clone attributes

        @on "error", @processError, this
        @on "sync", @handleSync, this
        @on "destroy", => @set "deleted_at", moment().unix() if @entity.get "soft_deleting"

    handleSync: (model, resp, options) ->
        @original = _.clone @attributes

        related.trigger "sync", related, resp, options for id, related of @related

        this

    processError: (model, xhr) ->
        if xhr.responseJSON? and xhr.responseJSON.error is "VALIDATION"
            errors = xhr.responseJSON.data

            @trigger "invalid", this, errors

            # Trigger errors for related models
            model.trigger "invalid", model, errors[id] for id, model of @related when id of errors

    validate: ->
        @set "errors", {}
        null

    link: -> @entity.link if @isNew() then "create" else @id

    url: -> @entity.url @id

    set: (key, val) ->
        if typeof key is "object"
            attrs = key

            for id in @entity.get "related" when id of attrs
                relation = @entity.getRelation id
                relationAttrs = attrs[id]

                if id of @related
                    related = @related[id]
                    related.set relationAttrs.attributes if relationAttrs
                else
                    related = @related[id] = relation.getReference().createInstance relationAttrs
                    related.parent = this

                # Attribute will now hold instance
                attrs[id] = related

        super

    sync: (method, model, options) ->
        if method in ["update", "create"]
            # Form data will allow us to upload files via AJAX request
            options.data = new AdvFormData(options.attrs ? @attributes).original

            # Set the content type to false to let browser handle it
            options.contentType = false
            options.processData = false

        super

    # save: ->
    #     xhr = super

    #     return xhr if _.isEmpty @related

    #     queue = (xhr) =>
    #         save = []

    #         save.push xhr if xhr?

    #         for key, model of @related
    #             @entity.related.get(key).associate @, model if model.isNew()

    #             save.push model.save() if model.hasChangedSinceSync()

    #         $.when.apply $, save

    #     # Create related models after the main model is saved
    #     if @isNew() then xhr.then (resp) -> queue() else queue xhr

    parse: (resp) -> resp.data.attributes

    copy: ->
        copy = @entity.createInstance()

        copy.set @getCopyableAttributes(), silent: yes
        copy.related[key].set item.getCopyableAttributes(), silent: yes for key, item of @related

        copy

    getCopyableAttributes: -> @entity.getCopyableAttributes @attributes

    hasChangedSinceSync: ->
        return yes for key, value of @attributes when if value instanceof Cruddy.Entity.Instance then value.hasChangedSinceSync() else not _.isEqual value, @original[key]

        # Related models do not affect the result unless model is created
        # return yes for key, related of @related when related.hasChangedSinceSync() unless @isNew()

        no

    # Get whether is allowed to save instance
    isSaveable: -> (@isNew() and @entity.createPermitted()) or (!@isNew() and @entity.updatePermitted())