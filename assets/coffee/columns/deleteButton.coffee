class Cruddy.Columns.DeleteButton extends Cruddy.Columns.Base

    id: "__deleteButton"

    getHeader: -> ""

    getClass: -> "col__delete-button col__button"

    canOrder: -> false

    render: (item) -> """
        <a href="#" data-action="deleteItem" class="btn btn-default btn-xs">
            #{ b_icon "trash" }
        </a>
    """