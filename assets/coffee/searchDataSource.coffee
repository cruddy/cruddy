class SearchDataSource extends Backbone.Model
    defaults:
        search: ""

    initialize: (attributes, options) ->
        @options =
            url: options.url
            type: "get"
            dataType: "json"

            data:
                page: null
                keywords: ""

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

        $.extend @options, options.ajaxOptions if options.ajaxOptions?

        @reset()

        @on "change:search", => @reset().next()

        this

    reset: ->
        @data = []
        @page = null
        @more = yes

        this

    fetch: (q, page) ->
        @request.abort() if @request?

        $.extend @options.data, { page: page, keywords: q }

        @trigger "request", this, @request = $.ajax @options

        @request

    next: ->
        if @more
            page = if @page? then @page + 1 else 1

            @fetch @get("search"), page

        this

    inProgress: -> @request?