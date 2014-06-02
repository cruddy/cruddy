Cruddy.Columns = new Factory

class Cruddy.Columns.Base extends Attribute
    initialize: (attributes) ->
        @formatter = Cruddy.formatters.create attributes.formatter, attributes.formatter_options if attributes.formatter?

        super

    # Return value's text representation
    format: (value) -> if @formatter? then @formatter.format value else _.escape value

    # Get column's header text
    getHeader: -> @attributes.header

    # Get column's class name
    getClass: -> "col-" + @id

    # Get whether a column can order items
    canOrder: -> @attributes.can_order