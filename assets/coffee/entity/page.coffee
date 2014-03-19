class Cruddy.Entity.Page extends Cruddy.View
    className: "page entity-page"

    events: {
        "click .btn-create": "create"
    }

    constructor: (options) ->
        @className += " entity-page-" + options.model.id

        super

    initialize: (options) ->
        @listenTo @model, "change:instance", @toggleForm

        super

    toggleForm: (entity, instance) ->
        if @form?
            @stopListening @form.model
            @form.remove()

        if instance?
            @listenTo instance, "sync", -> Cruddy.router.navigate instance.link()

            @form = new Cruddy.Entity.Form model: instance
            @$el.append @form.render().$el

            after_break => @form.show()

        this

    create: ->
        Cruddy.router.navigate @model.link("create"), trigger: true

        this

    render: ->
        @dispose()

        @$el.html @template()

        @dataSource = @model.createDataSource()
        
        @dataSource.fetch()

        # Search input
        @search = @createSearchInput @dataSource

        @$component("search").append @search.render().el

        # Filters
        if not _.isEmpty filters = @dataSource.entity.get "filters"
            @filterList = @createFilterList @dataSource.filter, filters

            @$component("filters").append @filterList.render().el

        # Data grid
        @dataGrid = @createDataGrid @dataSource
        @pagination = @createPagination @dataSource
        
        @$component("body").append(@dataGrid.render().el).append(@pagination.render().el)

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

    buttonsTemplate: -> if @model.createPermitted() then b_btn Cruddy.lang.entity_new + @model.getSingularTitle(), "plus", [ "default", "create" ] else ""

    dispose: ->
        @form?.remove()
        @filterList?.remove()
        @dataGrid?.remove()
        @pagination?.remove()
        @search?.remove()
        @dataSource?.stopListening()

        this

    remove: ->
        @dispose()

        super