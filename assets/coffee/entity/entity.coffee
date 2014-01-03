class Entity extends Backbone.Model

    initialize: (attributes, options) ->
        @fields = @createCollection Cruddy.fields, attributes.fields
        @columns = @createCollection Cruddy.columns, attributes.columns
        @related = @createCollection Cruddy.related, attributes.related

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
        data = { order_by: @get("order_by") || @get("primary_column") }
        data.order_dir = if data.order_dir? then @columns.get(data.order_by).get "order_dir" else "asc"

        new DataSource data, { entity: this, columns: columns, filter: new Backbone.Model }

    # Create filters for specified columns
    createFilters: (columns = @columns) ->
        filters = (col.createFilter() for col in columns.models when col.get "filterable")

        new Backbone.Collection filters

    # Create an instance for this entity
    createInstance: (attributes = {}, relatedData = {}) ->
        related = {}
        related[item.id] = item.related.createInstance(relatedData[item.id]) for item in @related.models

        console.log @related

        new EntityInstance _.extend({}, @get("defaults"), attributes), { entity: this, related: related }

    search: ->
        @searchInstance = @createDataSource ["id", @get "primary_column"] if not @searchInstance?
        @searchInstance.set "current_page", 1

        @searchInstance

    # Load a model
    load: (id) ->
        $.getJSON(@url(id)).then (resp) =>
            resp = resp.data

            @createInstance resp.model, resp.related

    # Load a model and set it as current
    update: (id) ->
        @load(id).then (instance) =>
            console.log instance
            @set "instance", instance

            instance

    url: (id) -> entity_url @id, id

    link: (id) -> "#{ @id}" + if id? then "/#{ id }" else ""