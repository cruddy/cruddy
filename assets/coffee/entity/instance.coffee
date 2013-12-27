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

    url: ->
        url = "#{ API_URL }/#{ @entity.id }"
        if @isNew() then url else url + "/" + @id

    sync: (method, model, options) ->
        if method in ["update", "create"]
            # Form data will allow us to upload files via AJAX request
            options.data = new FormData
            @append options.data, @entity.id, options.attrs ? @attributes

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

            for related in @related
                @entity.related[related.entity.id].associate @, related if related.isNew()

                save.push related.save()

            $.when.apply save

        # Create related models after the main model is saved
        if @isNew() then xhr.then (resp) -> queue() else queue xhr

    append: (data, key, value) ->
        if value instanceof File
            data.append key, value
            return

        if _.isArray value
            return @append data, key, "" if value.length == 0

            @append data, key + "[" + i + "]", _value for _value, i in value

            return

        if _.isObject value
            @append data, key + "[" + _key + "]", _value for _key, _value of value

            return

        data.append key, @convertValue value

        this

    convertValue: (value) ->
        return "" if value is null
        return 1 if value is yes
        return 0 if value is no

        value

    parse: (resp) -> resp.data

    hasChangedSinceSync: ->
        return yes for key, value of @attributes when not _.isEqual value, @original[key]

        # Related models do not affect the result unless model is created
        return yes for related in @related when related.hasChangedSinceSync() unless @isNew()

        no

    isSaveable: -> (@isNew() and @entity.get("can_create")) or (!@isNew() and @entity.get("can_update"))