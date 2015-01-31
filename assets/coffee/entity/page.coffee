class Cruddy.Entity.Page extends Cruddy.View
    className: "page entity-page"

    events: {
        "click .ep-btn-create": "create"
        "click .ep-btn-refresh": "refreshData"
    }

    constructor: (options) ->
        @className += " entity-page-" + options.model.id

        super

    initialize: (options) ->
        @dataSource = @_setupDataSource()

        # Make sure that those events not fired twice
        after_break =>
            @listenTo Cruddy.router, "route:index", @_updateFromQuery

        super

    _updateFromQuery: ->
        @_updateDataSourceFromQuery()

        @_displayForm().fail => @_updateModelIdInQuery replace: yes

        return this

    _setupDataSource: ->
        @dataSource = dataSource = @model.getDataSource()

        @_updateFromQuery()

        dataSource.fetch() unless dataSource.inProgress() or dataSource.hasData()

        @listenTo dataSource, "change",  @_refreshQuery

        return dataSource

    _refreshQuery: ->
        dataSource = @dataSource

        Cruddy.router.refreshQuery dataSource.attributes, dataSource.defaults, trigger: no

        return this

    _updateDataSourceFromQuery: (options) ->
        data = $.extend {}, @dataSource.defaults, _.omit Cruddy.router.query.keys, [ "id" ]

        data[key] = null for key of @dataSource.attributes when not (key of data)

        @dataSource.set data, options

        return

    _updateModelIdInQuery: (options) ->
        router = Cruddy.router

        options = $.extend { trigger: no, replace: no }, options

        if @form
            router.setQuery "id", @form.model.primaryKey or "new", options
        else
            router.removeQuery "id", options

        return this

    _displayForm: (instanceId) ->
        return @loadingForm if @loadingForm

        instanceId = instanceId ? Cruddy.router.getQuery("id")

        if instanceId instanceof Cruddy.Entity.Instance
            instance = instanceId
            instanceId = instance.id or "new"

        @loadingForm = dfd = $.Deferred()

        @loadingForm.always => @loadingForm = null

        if @form
            compareId = if @form.model.isNew() then "new" else @form.model.id

            if instanceId is compareId or not @form.confirmClose()

                dfd.reject()

                return dfd.promise()

        resolve = (instance) =>
            @_createAndRenderForm instance
            dfd.resolve instance

        instance = @model.createInstance() if instanceId is "new" and not instance

        if instance
            resolve instance

            return dfd.promise()

        if instanceId
            @model.load(instanceId).done(resolve).fail -> dfd.reject()
        else
            @form?.remove()
            dfd.resolve()

        return dfd.promise()

    _createAndRenderForm: (instance) ->
        @form?.remove()

        @form = form = Cruddy.Entity.Form.display instance

        form.on "close", => Cruddy.router.removeQuery "id", trigger: no
        form.on "created", (model) -> Cruddy.router.setQuery "id", model.id

        form.on "remove", =>
            @form = null
            @model.set "instance", null

            @stopListening instance

        form.on "saved", =>
            @dataSource.fetch()
            @_updateModelIdInQuery replace: yes

        form.on "saved remove", -> Cruddy.app.updateTitle()

        @model.set "instance", instance

        Cruddy.app.updateTitle()

        this

    displayForm: (id) -> @_displayForm(id).done => @_updateModelIdInQuery()

    create: ->
        @displayForm "new"

        this

    refreshData: (e) ->
        btn = $ e.currentTarget
        btn.prop "disabled", yes

        @dataSource.fetch().always -> btn.prop "disabled", no

        this

    render: ->
        @$el.html @template()

        @searchInputView = @createSearchInputView()
        @dataView = @createDataView()
        @paginationView = @createPaginationView()
        @filterListView = @createFilterListView()

        @$component("search_input_view").append @searchInputView.render().$el   if @searchInputView
        @$component("filter_list_view").append @filterListView.render().el      if @filterListView
        @$component("data_view").append @dataView.render().el                   if @dataView
        @$component("pagination_view").append @paginationView.render().el       if @paginationView

        return this

    createDataView: -> new DataGrid
        model: @dataSource
        entity: @model

    createPaginationView: -> new Pagination model: @dataSource

    createFilterListView: ->
        return if (filters = @dataSource.entity.filters).isEmpty()

        return new FilterList
            model: @dataSource
            entity: @model
            filters: filters

    createSearchInputView: -> new Cruddy.Inputs.Search
        model: @dataSource
        key: "keywords"

    template: -> """
        <div class="content-header">
            <div class="column column-main">
                <h1 class="entity-title">#{ @model.getPluralTitle() }</h1>

                <div class="entity-title-buttons">
                    #{ @buttonsTemplate() }
                </div>
            </div>

            <div class="column column-extra">
                <div class="entity-search-box" id="#{ @componentId "search_input_view" }"></div>
            </div>
        </div>

        <div class="content-body">
            <div class="column column-main">
                <div id="#{ @componentId "data_view" }"></div>
                <div id="#{ @componentId "pagination_view" }"></div>
            </div>

            <div class="column column-extra" id="#{ @componentId "filter_list_view" }"></div>
        </div>
    """

    buttonsTemplate: ->
        html = """
            <button type="button" class="btn btn-default ep-btn-refresh" title="#{ Cruddy.lang.refresh }">
                #{ b_icon "refresh" }
            </button>
        """

        html += " "

        html += """
            <button type="button" class="btn btn-primary ep-btn-create" title="#{ Cruddy.lang.add }">
                #{ b_icon "plus" }
            </button>
        """ if @model.createPermitted()

        html

    remove: ->
        @form?.remove()

        @filterListView?.remove()
        @dataView?.remove()
        @paginationView?.remove()
        @searchInputView?.remove()

        super

    getPageTitle: ->
        title = @model.getPluralTitle()

        title = @form.model.getTitle() + TITLE_SEPARATOR + title if @form?

        title

    executeCustomAction: (actionId, modelId, el) ->
        if el and not $(el).parent().is "disabled"
            @model.executeAction modelId, actionId, success: => @dataSource.fetch()

        return this

    pageUnloadConfirmationMessage: -> return @form?.pageUnloadConfirmationMessage()