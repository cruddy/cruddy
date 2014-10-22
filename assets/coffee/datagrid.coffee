class DataGrid extends Backbone.View
    tagName: "table"
    className: "table table-hover data-grid"

    events: {
        "click .sortable": "setOrder"
        "click [data-action]": "executeAction"
    }

    constructor: (options) ->
        @className += " data-grid-" + options.entity.id

        super

    initialize: (options) ->
        @entity = options.entity
        @columns = @entity.columns.models.filter (col) -> col.isVisible()
        @columns.unshift new Cruddy.Columns.Actions entity: @entity

        @listenTo @model, "data", @updateData
        @listenTo @model, "change:order_by change:order_dir", @onOrderChange

        @listenTo @entity, "change:instance", @onInstanceChange

    onOrderChange: ->
        orderBy = @model.get "order_by"
        orderDir = @model.get "order_dir"

        if @orderBy? and orderBy isnt @orderBy
            @$("#col-#{ @orderBy } .sortable").removeClass "asc desc"

        @orderBy = orderBy
        @$("#col-#{ @orderBy } .sortable").removeClass("asc desc").addClass orderDir

        this

    onInstanceChange: (entity, curr) ->
        prev = entity.previous "instance"

        if prev?
            @$("#item-#{ prev.id }").removeClass "active"
            prev.off null, null, this

        if curr?
            @$("#item-#{ curr.id }").addClass "active"
            curr.on "sync destroy", (=> @model.fetch()), this

        this

    setOrder: (e) ->
        orderBy = $(e.target).data "id"
        orderDir = @model.get "order_dir"

        if orderBy is @model.get "order_by"
            orderDir = if orderDir == 'asc' then 'desc' else 'asc'
        else
            orderDir = @entity.columns.get(orderBy).get "order_dir"

        @model.set { order_by: orderBy, order_dir: orderDir }

        this

    updateData: (datasource, data) ->
        @$(".items").replaceWith @renderBody @columns, data

        this

    render: ->
        data = @model.get "data"

        @$el.html @renderHead(@columns) + @renderBody(@columns, data)

        @onOrderChange @model

        this

    renderHead: (columns) ->
        html = "<thead><tr>"
        html += @renderHeadCell col for col in columns
        html += "</tr></thead>"

    renderHeadCell: (col) ->
        """<th class="#{ col.getClass() }" id="col-#{ col.id }">#{ @renderHeadCellValue col }</th>"""

    renderHeadCellValue: (col) ->
        title = _.escape col.getHeader()
        help = _.escape col.getHelp()
        title = "<span class=\"sortable\" data-id=\"#{ col.id }\">#{ title }</span>" if col.canOrder()
        if help then "<span class=\"glyphicon glyphicon-question-sign\" title=\"#{ help }\"></span> #{ title }" else title

    renderBody: (columns, data) ->
        html = "<tbody class=\"items\">"

        if data? and data.length
            html += @renderRow columns, item for item in data
        else
            html += """<tr><td class="no-items" colspan="#{ columns.length }">#{ Cruddy.lang.no_results }</td></tr>"""

        html += "</tbody>"

    renderRow: (columns, item) ->
        html = "<tr class=\"item #{ @states item }\" id=\"item-#{ item.id }\" data-id=\"#{ item.id }\">"
        html += @renderCell col, item for col in columns
        html += "</tr>"

    states: (item) ->
        states = if item._states then item._states else ""

        states += " active" if (instance = @entity.get "instance")? and item.id == instance.id

        return states

    renderCell: (col, item) ->
        """<td class="#{ col.getClass() }">#{ col.render item }</td>"""

    executeAction: (e) ->
        e.preventDefault()

        $el = $ e.currentTarget

        this[$el.data "action"].call this, $el

        return

    deleteItem: ($el) ->
        id = $el.data "id"

        $.ajax
            url: Cruddy.backendRoot + "/api/" + @entity.id + "/" + $el.data("id")
            type: "POST"
            dataType: "json"
            displayLoading: yes

            data:
                _method: "DELETE"

            success: =>
                $el.closest("tr").fadeOut()
                @model.fetch()

        return