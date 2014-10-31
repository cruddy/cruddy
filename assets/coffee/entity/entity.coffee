Cruddy.Entity = {}

class Cruddy.Entity.Entity extends Backbone.Model

    initialize: (attributes, options) ->
        @fields = @createObjects attributes.fields
        @columns = @createObjects attributes.columns
        @filters = @createObjects attributes.filters
        @permissions = Cruddy.permissions[@id]
        @cache = {}

        return this

    createObjects: (items) ->
        data = []

        for options in items
            options.entity = this

            constructor = get options.class

            throw "The class #{ options.class } is not found" unless constructor

            data.push new constructor options

        new Backbone.Collection data

    # Create a datasource that will require specified columns and can be filtered
    # by specified filters
    createDataSource: (data) -> new DataSource data, entity: this

    # Create filters for specified columns
    createFilters: (columns = @columns) ->
        filters = (col.createFilter() for col in columns.models when col.get("filter_type") is "complex")

        new Backbone.Collection filters

    # Create an instance for this entity
    createInstance: (attributes = {}, options = {}) ->
        options.entity = this

        attributes = _.extend {}, @get("defaults"), attributes.attributes

        instance = new Cruddy.Entity.Instance attributes, options

        instance.fillExtra attributes

    # Get relation field
    getRelation: (id) ->
        field = @field id

        if not field instanceof Cruddy.Fields.BaseRelation
            console.error "The field #{id} is not a relation."

            return

        field

    # Get a field with specified id
    field: (id) ->
        if not field = @fields.get id
            console.error "The field #{id} is not found."

            return

        return field

    search: (options = {}) -> new SearchDataSource {}, $.extend { url: @url() }, options

    # Load a model
    load: (id, options) ->
        defaults =
            cached: yes # whether to get record from the cache

        options = $.extend defaults, options

        return $.Deferred().resolve(@cache[id]).promise() if options.cached and id of @cache

        xhr = $.ajax
            url: @url(id)
            type: "GET"
            dataType: "json"
            displayLoading: yes

        xhr = xhr.then (resp) =>
            instance = @createInstance resp

            @cache[instance.id] = instance

            return instance

        return xhr

    # Destroy a model
    destroy: (id, options = {}) ->
        options.url = @url id
        options.type = "POST"
        options.dataType = "json"
        options.data = _method: "DELETE"

        return $.ajax options

    # Load a model and set it as current
    actionUpdate: (id) -> @load(id).then (instance) =>
        @set "instance", instance

        instance

    # Create new model and set it as current
    actionCreate: -> @set "instance", @createInstance()

    # Get only those attributes are not unique for the model
    getCopyableAttributes: (model, attributes) ->
        data = {}
        data[field.id] = attributes[field.id] for field in @fields.models when not field.isUnique() and field.id of attributes and not _.contains(@attributes.related, field.id)

        for ref in @attributes.related when ref of attributes
            data[ref] = @getRelation(ref).copy model, attributes[ref]

        data

    # Get url that handles syncing
    url: (id) -> entity_url @id, id

    # Get link to this entity or to the item of the entity
    link: (id) ->
        link = @url()

        id = id.id if id instanceof Cruddy.Entity.Instance

        return if id then link + "?id=" + id else link

    createView: ->
        pageClass = get @attributes.view

        throw "Failed to resolve page class #{ @attributes.view }" unless pageClass

        return new pageClass model: this

    # Get title in plural form
    getPluralTitle: -> @attributes.title.plural

    # Get title in singular form
    getSingularTitle: -> @attributes.title.singular

    getPermissions: -> @permissions

    updatePermitted: -> @permissions.update

    createPermitted: -> @permissions.create

    deletePermitted: -> @permissions.delete

    viewPermitted: -> @permissions.view

    isSoftDeleting: -> @attributes.soft_deleting