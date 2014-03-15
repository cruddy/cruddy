class SearchDataSource extends Backbone.Model
    defaults:
        search: ""

    initialize: (attributes, options) ->
        @filters = new Backbone.Model

        @options =
            url: options.url
            type: "get"
            dataType: "json"

            data:
                simple: 1

            success: (resp) =>
                resp = resp.data

                @data.push item for item in resp.data

                @page = resp.current_page
                @more = resp.current_page < resp.last_page
                @request = null

                @trigger "data", this, @data

                this

            error: (xhr) =>
                @request = null
                @trigger "error", this, xhr

                this

        $.extend yes, @options, options.ajaxOptions if options.ajaxOptions?

        @reset()

        @on "change:search", @refresh, this
        @listenTo @filters, "change", @refresh

        this

    refresh: -> @reset().next()

    reset: ->
        @data = []
        @page = null
        @more = yes

        this

    fetch: (q, page, filters) ->
        @request.abort() if @request?

        $.extend @options.data, 
            page: page
            keywords: q
            filters: filters

        @trigger "request", this, @request = $.ajax @options

        @request

    next: ->
        if @more
            page = if @page? then @page + 1 else 1

            @fetch @get("search"), page, @filters.attributes

        this

    inProgress: -> @request?