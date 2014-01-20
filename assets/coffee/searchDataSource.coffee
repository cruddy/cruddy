class SearchDataSource extends Backbone.Model
    defaults:
        search: ""

    initialize: (attributes, options) ->
        keyName = options.primaryKey ? "id"
        valueName = options.primaryColumn

        @options =
            url: options.url
            type: "get"
            dataType: "json"

            data:
                page: null
                q: ""
                columns: keyName + "," + valueName

            success: (resp) =>
                resp = resp.data

                @data.push { id: item[keyName].toString(), title: item[valueName] } for item in resp.data

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

        $.extend @options.data, { page: page, q: q }

        @trigger "request", this, @request = $.ajax @options

        @request

    next: ->
        if @more
            page = if @page? then @page + 1 else 1

            @fetch @get("search"), page

        this

    inProgress: -> @request?