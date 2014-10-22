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
        @dataSource = @model.createDataSource @getDatasourceData()

        @listenTo @dataSource, "change", (model) -> Cruddy.router.refreshQuery @getDatasourceDefaults(), model.attributes

        @listenTo Cruddy.router, "route:index", =>
            @dataSource.holdFetch().set(@getDatasourceData()).fetch()

            @_displayForm()

        super

    getDatasourceDefaults: ->
        return @dsDefaults if @dsDefaults

        @dsDefaults = data =
            current_page: 1
            order_by: @model.get "order_by"
            order_dir: "asc"
            search: ""

        if data.order_by and (col = @model.columns.get(data.order_by))
            data.order_dir = col.get "order_dir"

        return data

    getDatasourceData: -> $.extend {}, @getDatasourceDefaults(), Cruddy.router.query.keys

    displayForm: (id) -> @_displayForm(id).done (instance) =>

        id = instance.id or "new"

        if id then Cruddy.router.setQuery("id", id, trigger: no) else Cruddy.router.removeQuery("id", trigger: no)

        return

    _displayForm: (instanceId) ->
        instanceId = instanceId ? Cruddy.router.getQuery("id") or null

        if instanceId instanceof Cruddy.Entity.Instance
            instance = instanceId
            instanceId = instance.id or "new"

        dfd = $.Deferred()

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
            dfd.resolve()

        return dfd.promise()

    _createAndRenderForm: (instance) ->
        @form?.remove()

        @form = new Cruddy.Entity.Form model: instance
        @$el.append @form.render().$el

        @form.once "close", => Cruddy.router.removeQuery "id", trigger: no

        @listenTo instance, "sync", (model) -> Cruddy.router.setQuery "id", model.id

        @form.once "remove", =>
            @form = null
            @model.set "instance", null
            @stopListening instance

        @model.set "instance", instance

        after_break => @form.show()

        this

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

        @dataSource.fetch()

        # Search input
        @search = @createSearchInput @dataSource

        @$component("search").append @search.render().$el

        # Filters
        if not _.isEmpty filters = @dataSource.entity.get "filters"
            @filterList = @createFilterList @dataSource.filter, filters

            @$component("filters").append @filterList.render().el

        # Data grid
        @dataGrid = @createDataGrid @dataSource
        @pagination = @createPagination @dataSource

        @$component("body").append(@dataGrid.render().el).append(@pagination.render().el)

        @_displayForm()

        this

    createDataGrid: (dataSource) -> new DataGrid
        model: dataSource
        entity: @model

    createPagination: (dataSource) -> new Pagination model: dataSource

    createFilterList: (model, filters) -> new FilterList
        model: model
        entity: @model
        filters: filters

    createSearchInput: (dataSource) -> new Cruddy.Inputs.Search
        model: dataSource
        key: "search"

    template: ->
        html = """
            <div class="content-header">
                <div class="column column-main">
                    <h1 class="entity-title">#{ @model.getPluralTitle() }</h1>

                    <div class="entity-title-buttons">
                        #{ @buttonsTemplate() }
                    </div>
                </div>

                <div class="column column-extra">
                    <div class="entity-search-box" id="#{ @componentId "search" }"></div>
                </div>
            </div>

            <div class="content-body">
                <div class="column column-main" id="#{ @componentId "body" }"></div>
                <div class="column column-extra" id="#{ @componentId "filters" }"></div>
            </div>
        """

    buttonsTemplate: ->
        html = """<button type="button" class="btn btn-default ep-btn-refresh" title="#{ Cruddy.lang.refresh }">#{ b_icon "refresh" }</button>"""
        html += """ <button type="button" class="btn btn-primary ep-btn-create" title="#{ Cruddy.lang.add }">#{ b_icon "plus" }</button>""" if @model.createPermitted()

        html

    remove: ->
        @form?.remove()
        @filterList?.remove()
        @dataGrid?.remove()
        @pagination?.remove()
        @search?.remove()
        @dataSource?.stopListening()

        super