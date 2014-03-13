class Cruddy.Entity.Page extends Backbone.View
    className: "entity-page"

    events: {
        "click .btn-create": "create"
    }

    constructor: (options) ->
        @className += " " + @className + "-" + options.model.id

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

        @header = @$ ".entity-page-header"
        @content = @$ ".entity-page-content"
        @footer = @$ ".entity-page-footer"

        @dataSource = @model.createDataSource()
        
        @dataSource.fetch()

        # Search input
        @search = @createSearchInput @dataSource

        @$(".col-search").append @search.render().el

        # Filters
        if not _.isEmpty filters = @dataSource.entity.get "filters"
            @filterList = @createFilterList @dataSource.filter, filters

            @$(".col-filters").append @filterList.render().el

        # Data grid
        @dataGrid = @createDataGrid @dataSource
        
        @content.append @dataGrid.render().el        
        
        # Pagination
        @pagination = @createPagination @dataSource
        
        @footer.append @pagination.render().el

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
        html = "<div class='entity-page-header'>"
        html += """
        <h1>
            #{ @model.getPluralTitle() }

        """

        if @model.createPermitted()
            html += """
                <button class="btn btn-default btn-create" type="button">
                    <span class="glyphicon glyphicon-plus"</span>
                </button>
            """

        html += "</h1>"

        html += """<div class="row row-search"><div class="col-xs-2 col-search"></div><div class="col-xs-10 col-filters"></div></div>"""
        html += "</div>"
        
        html += "<div class='entity-page-content-wrap'><div class='entity-page-content'></div></div>"
        html += "<div class='entity-page-footer'></div>"

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