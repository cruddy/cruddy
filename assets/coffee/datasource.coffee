class DataSource extends Backbone.Model
    defaults:
        data: []
        search: ""

    initialize: (attributes, options) ->
        @entity = options.entity
        @columns = options.columns if options.columns?
        @filter = options.filter if options.filter?

        @listenTo @filter, "change", @fetch if @filter?

        @on "change", => @fetch() unless @_hold
        @on "change:search", => @set current_page: 1, silent: yes

    hasData: -> not _.isEmpty @get "data"

    hasMore: -> @get("current_page") < @get("last_page")

    isFull: -> !@hasMore()

    inProgress: -> @request?

    fetch: ->
        @request.abort() if @request?

        @request = $.getJSON @entity.url(), @data(), (resp) =>
            @_hold = true
            @set resp.data
            @_hold = false
            @trigger "data", this, resp.data.data

        @request.fail (xhr) => @trigger "error", this, xhr
        @request.always => @request = null

        @trigger "request", this, @request

        @request

    more: ->
        return if @isFull()

        @set current_page: @get("current_page") + 1, silent: yes

        @fetch()

    data: ->
        data = {
            order_by: @get "order_by"
            order_dir: @get "order_dir"
            page: @get "current_page"
            per_page: @get "per_page"
            q: @get "search"
        }

        filters = @filterData()

        data.filters = filters unless _.isEmpty filters
        data.columns = @columns.join "," if @columns?

        data

    filterData: ->
        return null unless @filter?

        data = {}

        for key, value of @filter.attributes
            data[key] = value unless value is null or value is ""

        data