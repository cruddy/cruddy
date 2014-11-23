class DataSource extends Backbone.Model
    defaults:
        page: 1
        per_page: null
        keywords: ""
        order_by: null
        order_dir: "asc"

    initialize: (attributes, options) ->
        @entity = entity = options.entity
        @filter = filter = new Backbone.Model
        @defaults = _.clone @attributes

        @options =
            url: entity.url()
            dataType: "json"
            type: "get"
            displayLoading: yes

            success: (resp) =>
                @resp = resp

                @trigger "data", this, resp.data

            error: (xhr) => @trigger "error", this, xhr

        @listenTo filter, "change", =>
            @set "page", 1, noFetch: yes
            @fetch()

        @on "change:keywords", => @set "page", 1
        @on "change", (model, options) => @fetch() unless options.noFetch

    hasData: -> @resp?

    isEmpty: -> not @hasData() or _.isEmpty @resp.data

    hasMore: -> @hasData() and @resp.current_page < @resp.last_page

    isFull: -> not @hasMore()

    inProgress: -> @request?

    fetch: ->
        @request.abort() if @request?

        @options.data = @_requestData()

        @request = $.ajax @options

        @request.always => @request = null

        @trigger "request", this, @request

        @request

    next: ->
        return if @inProgress() or @isFull()

        @set page: @get("page") + 1

        return this

    prev: ->
        return if @inProgress() or (page = @get "page") <= 1

        @set page: page - 1

        return this

    _requestData: ->
        data = {}

        data[key] = value for key, value of @attributes when _.isNumber(value) or not _.isEmpty value

        data.filters = filters unless _.isEmpty filters = @_filtersData()

        data

    _filtersData: ->
        data = {}

        data[key] = value for key, value of @filter.attributes when value?

        return data

    getData: -> @resp?.data
    getTotal: -> @resp?.total
    getFrom: -> @resp?.from
    getTo: -> @resp?.to
    getLastPage: -> @resp?.last_page
