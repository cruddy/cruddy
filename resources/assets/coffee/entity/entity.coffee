Cruddy.Entity = {}

class Cruddy.Entity.Entity extends Backbone.Model

    initialize: (attributes, options) ->
        @columns = @createObjects attributes.data_source.columns
        @filters = @createObjects attributes.data_source.filters

        @permissions = Cruddy.permissions[@id]
        @cache = {}

        @forms =
            create: @setupForm attributes.create_form
            update: @setupForm attributes.update_form

        return this

    setupForm: (config) ->
        fields: @createObjects config.fields
        layout: config.layout

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
    createDataSource: (data) ->
        defaults =
            order_by: @get "order_by"

        defaults.order_dir = col.get "order_dir" if col = @columns.get defaults.order_by

        data = $.extend {}, defaults, data

        return new DataSource data, entity: this

    getDataSource: ->
        @dataSource = @createDataSource() unless @dataSource

        return @dataSource

    # Create filters for specified columns
    createFilters: (columns = @columns) ->
        filters = (col.createFilter() for col in columns.models when col.get("filter_type") is "complex")

        new Backbone.Collection filters

    # Create an instance for this entity
    createInstance: (data = {}, options = {}) ->
        options.entity = this

        attributes = _.extend {}, @get("defaults"), data.attributes

        instance = new Cruddy.Entity.Instance attributes, options

        instance.setMetaFromResponse data

    form: (type) ->
        if type instanceof Cruddy.Entity.Instance
            type = if type.isNew() then "create" else "update"

        return @forms[type]

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
        options.displayLoading = yes

        return $.ajax options

    # Destroy a model
    executeAction: (id, action, options = {}) ->
        options.url = @url id + "/" + action
        options.type = "POST"
        options.dataType = "json"
        options.displayLoading = yes

        return $.ajax options

    # Get only those attributes are not unique for the model
    getCopyableAttributes: (model, copy) ->
        form = @form model

        data = {}

        data[field.id] = field.copyAttribute(model, copy) for field in form.fields.models when field.isCopyable()

        data

    hasChangedSinceSync: (model) ->
        form = @form model

        for field in form.fields.models when field.hasChangedSinceSync model
            console.log field

            return yes

        return no

    prepareAttributes: (attributes, model) ->
        form = @form model

        result = {}
        result[key] = field.prepareAttribute value for key, value of attributes when field = form.fields.get key

        return result

    # Get url that handles syncing
    url: (id) -> entity_url @id, id

    # Get link to this entity or to the item of the entity
    link: (id) ->
        link = @url()

        id = id.id if id instanceof Cruddy.Entity.Instance

        return if id then link + "?id=" + id else link

    createController: ->
        controllerClass = get(@attributes.controller_class) or Cruddy.Entity.Page

        throw "Failed to resolve page class #{ @attributes.view }" unless controllerClass

        return new controllerClass model: this

    # Get title in plural form
    getPluralTitle: -> @attributes.title.plural

    # Get title in singular form
    getSingularTitle: -> @attributes.title.singular

    getPermissions: -> @permissions

    readPermitted: -> @permissions.read

    updatePermitted: -> @permissions.update

    createPermitted: -> @permissions.create

    deletePermitted: -> @permissions.delete

    isSoftDeleting: -> @attributes.soft_deleting

    getPrimaryKey: -> @attributes.primary_key or "id"