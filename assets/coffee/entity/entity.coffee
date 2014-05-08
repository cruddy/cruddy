Cruddy.Entity = {}

class Cruddy.Entity.Entity extends Backbone.Model

    initialize: (attributes, options) ->
        @fields = @createCollection Cruddy.Fields, attributes.fields
        @columns = @createCollection Cruddy.Columns, attributes.columns

        @set "label", humanize @id if @get("label") is null

    createCollection: (factory, items) ->
        data = []
        for options in items
            options.entity = this
            instance = factory.create options.class, options
            data.push instance if instance?

        new Backbone.Collection data

    # Create a datasource that will require specified columns and can be filtered
    # by specified filters
    createDataSource: (columns = null) ->
        data = { order_by: @get("order_by") }
        data.order_dir = if data.order_dir? then @columns.get(data.order_by).get "order_dir" else "asc"

        new DataSource data, { entity: this, columns: columns, filter: new Backbone.Model }

    # Create filters for specified columns
    createFilters: (columns = @columns) ->
        filters = (col.createFilter() for col in columns.models when col.get("filter_type") is "complex")

        new Backbone.Collection filters

    # Create an instance for this entity
    createInstance: (attributes = {}, options = {}) ->
        options.extra = attributes.extra
        options.entity = this
        
        attributes = _.extend {}, @get("defaults"), attributes.attributes

        new Cruddy.Entity.Instance attributes, options

    # Get relation field
    getRelation: (id) ->
        field = @fields.get id

        if not field
            console.error "The field #{id} is not found."

            return

        if not field instanceof Cruddy.Fields.BaseRelation
            console.error "The field #{id} is not a relation."

            return

        field

    search: (options = {}) -> new SearchDataSource {}, $.extend { url: @url() }, options

    # Load a model
    load: (id) ->
        xhr = $.ajax
            url: @url(id)
            type: "GET"
            dataType: "json"
            cache: yes
            displayLoading: yes

        xhr.then (resp) =>
            resp = resp.data

            @createInstance resp

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
    link: (id) -> "#{ @id}" + if id? then "/#{ id }" else ""

    # Get title in plural form
    getPluralTitle: -> @attributes.title.plural

    # Get title in singular form
    getSingularTitle: -> @attributes.title.singular

    getPermissions: -> @attributes.permissions

    updatePermitted: -> @attributes.permissions.update

    createPermitted: -> @attributes.permissions.create

    deletePermitted: -> @attributes.permissions.delete

    viewPermitted: -> @attributes.permissions.view

    isSoftDeleting: -> @attributes.soft_deleting