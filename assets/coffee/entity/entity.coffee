class Entity extends Backbone.Model

    initialize: (attributes, options) ->
        @fields = @createCollection Cruddy.fields, attributes.fields
        @columns = @createCollection Cruddy.columns, attributes.columns

        @related = {}
        @related[item.related] = new Related item for item in attributes.related

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
    createInstance: (attributes = {}, related = null) ->
        related = (item.related.createInstance() for key, item of @related) if related is null

        new EntityInstance _.extend({}, @get("defaults"), attributes), { entity: this, related: related }

    search: ->
        @searchInstance = @createDataSource ["id", @get "primary_column"] if not @searchInstance?
        @searchInstance.set "current_page", 1

        @searchInstance

    # Load an instance and set it as currently active.
    update: (id) ->
        $.getJSON("#{ API_URL }/#{ @id }/#{ id }").then (resp) =>
            #@fields.set resp.data.runtime, add: false

            related = (item.related.createInstance resp.data.related[item.id] for key, item of @related)

            @set "instance", instance = @createInstance resp.data.instanceData, related

            instance

    link: (id) -> "#{ @id}" + if id? then "/#{ id }" else ""