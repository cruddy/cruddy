class Cruddy.Columns.Actions extends Attribute

    getHeader: -> ""

    getClass: -> "col-actions"

    canOrder: -> false

    render: (item) -> """<div class="btn-group btn-group-xs">#{ @renderActions item }</div>"""

    renderActions: (item) ->
        html = ""

        html += @renderEditAction item if @entity.viewPermitted()
        html += @renderDeleteAction item if @entity.deletePermitted()


    renderDeleteAction: (item) ->
        """<a href="#" data-action="deleteItem" data-id="#{ item.id }" class="btn btn-default">#{ b_icon "trash" }</a>"""

    renderEditAction: (item) -> """
        <a href="#{ @entity.url() + "?id=" + item.id }" data-action="edit" class="btn btn-default">
            #{ b_icon("pencil") }
        </a>
    """