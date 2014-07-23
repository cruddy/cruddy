class Cruddy.Columns.Actions extends Attribute

    getHeader: -> ""

    getClass: -> "col-actions"

    canOrder: -> false

    render: (item) -> """
        <div class="btn-group btn-group-xs">
            <a href="#{ Cruddy.baseUrl + "/" + @entity.link item.id }" data-action="edit" class="btn btn-default">
                #{ b_icon("pencil") }
            </a>
        </div>
    """
