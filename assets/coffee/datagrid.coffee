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

        @listenTo @model, "data", => @renderBody() if @$items?
        @listenTo @model, "change:order_by change:order_dir", @markOrderColumn

        @listenTo @entity, "change:instance", @markActiveItem

    addActionColumns: (columns) ->
        @columns.unshift new Cruddy.Columns.ViewButton entity: @entity
        @columns.push new Cruddy.Columns.DeleteButton entity: @entity if @entity.deletePermitted()

        return this

    markOrderColumn: ->
        orderBy = @model.get("order_by")
        orderDir = @model.get("order_dir") or "asc"

        if @orderBy? and orderBy isnt @orderBy
            @$colCell(@orderBy).removeClass "asc desc"

        @$colCell(@orderBy = orderBy).removeClass("asc desc").addClass orderDir

        this

    markActiveItem: ->
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

    render: ->
        @$el.html @template()

        @$header = @$component "header"
        @$items = @$component "items"

        @renderHead()
        @renderBody()

        this

    renderHead: ->
        html = ""
        html += @renderHeadCell column for column in @columns

        @$header.html html

        @markOrderColumn()

    renderHeadCell: (column) -> """
        <th class="#{ column.getClass() }" id="#{ @colCellId column }" data-id="#{ column.id }">
            #{ @renderHeadCellValue column }
        </th>
    """

    renderHeadCellValue: (col) ->
        title = _.escape col.getHeader()

        if help = _.escape col.getHelp()
            title = """
                <span class="glyphicon glyphicon-question-sign" title="#{ help }"></span> #{ title }
            """

        return title

    renderBody: ->
        if @model.isEmpty()
            @$items.html @emptyTemplate()

            return this

        html = ""
        html += @renderRow item for item in @model.getData()

        @$items.html html

        @markActiveItem()

    renderRow: (item) ->
        html = """
            <tr class="item #{ @itemStates item }" id="#{ @itemRowId item }" data-id="#{ item.meta.id }">"""

        html += @renderCell columns, item for columns in @columns

        html += "</tr>"

    itemStates: (item) ->
        states = if item.attributes._states then item.attributes._states else ""

        states += " active" if (instance = @entity.get "instance")? and item.meta.id == instance.id

        return states

    renderCell: (column, item) -> """
        <td class="#{ column.getClass() }">
            #{ column.render item }
        </td>
    """

    executeAction: (e) ->
        $el = $ e.currentTarget
        action = $el.data "action"

        if action and action = this[action]
            e.preventDefault()

            action.call this, $el.closest(".item").data("id"), $el

        return

    deleteItem: (id, $el) ->
        return if not confirm Cruddy.lang.confirm_delete

        $row = $el.closest ".item"

        $el.attr "disabled", yes

        @entity.destroy id,

            displayLoading: yes

            success: =>
                $row.fadeOut()
                @model.fetch()

            fail: ->
                $el.attr "disabled", no

        return

    template: -> """
        <thead><tr id="#{ @componentId "header" }"></tr></thead>
        <tbody class="items" id="#{ @componentId "items" }"></tbody>
    """

    emptyTemplate: -> """
        <tr class="empty">
            <td colspan="#{ @columns.length }">
                #{ Cruddy.lang.no_results }
            </td>
        </tr>
    """

    colCellId: (col) -> @componentId "col-" + col.id

    $colCell: (id) -> @$component "col-" + id

    itemRowId: (item) -> @componentId "item-" + item.meta.id

    $itemRow: (item) -> @$component "item-" + item.meta.id