class SearchDataSource extends Backbone.Model
    defaults:
        keywords: null
        constraint: null

    initialize: (attributes, options) ->
        @resetData = no
        @needsRefresh = no
        @data = []
        @page = null
        @more = yes

        @options =
            url: options.url
            type: "get"
            dataType: "json"

            data:
                simple: 1

            success: (resp) =>
                if @resetData
                    @data = []
                    @resetData = no

                @data.push item for item in resp.items

                @page = resp.page
                @more = resp.page < resp.lastPage
                @request = null

                @trigger "data", @, @data

                this

            error: (xhr) =>
                @request = null
                @trigger "error", @, xhr

                this

        $.extend yes, @options, options.ajaxOptions if options.ajaxOptions?

        @on "change", @refresh, this

        this

    refresh: ->
        @resetData = yes

        @fetchPage 1

    fetchPage: (page) ->
        @request.abort() if @request?

        $.extend @options.data, @attributes, { page: page }

        @trigger "request", this, @request = $.ajax @options

        @request

    next: ->
        @fetchPage if @page? then @page + 1 else 1 if @more

        this

    inProgress: -> @request?

    isEmpty: -> @page is null and not @request

    getById: (id) ->
        id = id.toString() if not id.length

        return _.find @data, (item) -> item.id.toString() == id