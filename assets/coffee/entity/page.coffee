class EntityPage extends Backbone.View
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

            @form = new EntityForm model: instance
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

        @dataGrid = new DataGrid
            model: @dataSource

        @pagination = new Pagination
            model: @dataSource

        @filterList = new FilterList
            model: @dataSource.filter
            entity: @dataSource.entity

        @search = new SearchInput
            model: @dataSource
            key: "search"

        @dataSource.fetch()

        @$(".col-search").append @search.render().el
        @$(".col-filters").append @filterList.render().el
        @content.append @dataGrid.render().el
        @footer.append @pagination.render().el

        this

    template: ->
        html = "<div class='entity-page-header'>"
        html += """
        <h1>
            #{ @model.get "title" }

        """

        if @model.get "can_create"
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
        @form.remove() if @form?
        @filterList.remove() if @filterList?
        @dataGrid.remove() if @dataGrid?
        @pagination.remove() if @pagination?
        @search.remove() if @search?
        @dataSource.stopListening() if @dataSource?

        this

    remove: ->
        @dispose()

        super