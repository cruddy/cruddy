class DataGrid extends Cruddy.View
    tagName: "table"
    className: "table table-hover dg"

    events: {
        "click .col__sortable": "setOrder"
        "click [data-action]": "executeAction"
    }

    constructor: (options) ->
        @className += " dg-" + options.entity.id

        super

    initialize: (options) ->
        @entity = options.entity

        @columns = @entity.columns.models.filter (col) -> col.isVisible()

        @addActionColumns @columns

        @listenTo @model, "data", @updateData
        @listenTo @model, "change:order_by change:order_dir", @markOrderColumn

        @listenTo @entity, "change:instance", @markSelectedItem

    addActionColumns: (columns) ->
        @columns.unshift new Cruddy.Columns.ViewButton entity: @entity
        @columns.push new Cruddy.Columns.DeleteButton entity: @entity if @entity.deletePermitted()

        return this

    markOrderColumn: ->
        orderBy = @model.get "order_by"
        orderDir = @model.get "order_dir"

        if @orderBy? and orderBy isnt @orderBy
            @$colCell(@orderBy).removeClass "asc desc"

        @$colCell(@orderBy = orderBy).removeClass("asc desc").addClass orderDir

        this

    markSelectedItem: ->
        @$itemRow(model).removeClass "active" if model = @entity.previous "instance"
        @$itemRow(model).addClass "active" if model = @entity.get "instance"

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

        @markSelectedItem()

        this

    render: ->
        data = @model.get "data"

        @$el.html @renderHead(@columns) + @renderBody(@columns, data)

        @markOrderColumn @model

        this

    renderHead: (columns) ->
        html = "<thead><tr>"
        html += @renderHeadCell col for col in columns
        html += "</tr></thead>"

    renderHeadCell: (col) ->
        """<th class="#{ col.getClass() }" id="#{ @colCellId col }" data-id="#{ col.id }">#{ @renderHeadCellValue col }</th>"""

    renderHeadCellValue: (col) ->
        title = _.escape col.getHeader()

        if help = _.escape col.getHelp()
            title = """
                <span class="glyphicon glyphicon-question-sign" title="#{ help }"></span> #{ title }
            """

        return title

    renderBody: (columns, data) ->
        html = """<tbody class="items">"""

        if data? and data.length
            html += @renderRow columns, item for item in data
        else
            html += """<tr class="empty"><td colspan="#{ columns.length }">#{ Cruddy.lang.no_results }</td></tr>"""

        html += "</tbody>"

    renderRow: (columns, item) ->
        html = """
            <tr class="item #{ @states item }" id="#{ @itemRowId item }" data-id="#{ item.id }">"""

        html += @renderCell col, item for col in columns

        html += "</tr>"

    states: (item) ->
        states = if item._states then item._states else ""

        states += " active" if (instance = @entity.get "instance")? and item.id == instance.id

        return states

    renderCell: (col, item) ->
        """<td class="#{ col.getClass() }">#{ col.render item }</td>"""

    executeAction: (e) ->
        $el = $ e.currentTarget
        action = $el.data "action"

        if action and action = this[action]
            e.preventDefault()

            action.call this, $el

        return

    deleteItem: ($el) ->
        return if not confirm Cruddy.lang.confirm_delete

        $row = $el.closest ".item"

        $el.attr "disabled", yes

        @entity.destroy $row.data("id"),

            displayLoading: yes

            success: =>
                $row.fadeOut()
                @model.fetch()

            fail: ->
                $el.attr "disabled", no

        return

    colCellId: (col) -> @componentId "col-" + col.id

    $colCell: (id) -> @$component "col-" + id

    itemRowId: (item) -> @componentId "item-" + item.id

    $itemRow: (item) -> @$component "item-" + item.id