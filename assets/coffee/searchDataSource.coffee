class SearchDataSource extends Backbone.Model
    defaults:
        search: ""

    initialize: (attributes, options) ->
        @resetData = no
        @needsRefresh = no
        @data = []
        @page = null
        @more = yes

        @filters = new Backbone.Model

        @options =
            url: options.url
            type: "get"
            dataType: "json"

            data:
                simple: 1

            success: (resp) =>
                resp = resp.data

                if @resetData
                    @data = []

                @data.push item for item in resp.data

                @page = resp.current_page
                @more = resp.current_page < resp.last_page
                @request = null

                @trigger "data", @, @data

                this

            error: (xhr) =>
                @request = null
                @trigger "error", @, xhr

                this

        $.extend yes, @options, options.ajaxOptions if options.ajaxOptions?

        @on "change:search", @refresh, this
        @listenTo @filters, "change", @refresh

        this

    refresh: ->
        @resetData = yes

        @fetchPage 1

    _fetch: (q, page, filters) ->
        @request.abort() if @request?

        $.extend @options.data,
            page: page
            keywords: q
            filters: filters

        @trigger "request", this, @request = $.ajax @options

        @request

    fetchPage: (page) -> @_fetch @get("search"), page, @filters.attributes

    next: ->
        @fetchPage if @page? then @page + 1 else 1 if @more

        this

    inProgress: -> @request?

    isEmpty: -> @page is null and not @request

    getById: (id) ->
        id = id.toString() if not id.length

        return _.find @data, (item) -> item.id.toString() == id