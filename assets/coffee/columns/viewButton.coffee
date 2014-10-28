class Cruddy.Columns.ViewButton extends Cruddy.Columns.Base

    id: "__viewButton"

    getHeader: -> ""

    getClass: -> "col__view-button col__button"

    canOrder: -> false

    render: (item) -> """
        <a href="#{ @entity.link item.id }" class="btn btn-default btn-xs">
            #{ b_icon("pencil") }
        </a>
    """