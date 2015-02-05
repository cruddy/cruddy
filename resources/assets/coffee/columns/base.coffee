class Cruddy.Columns.Base extends Cruddy.Attribute

    initialize: (attributes) ->
        @formatter = Cruddy.formatters.create attributes.formatter, attributes.formatter_options if attributes.formatter?

        super

    render: (item) -> @format item.attributes[@id]

    # Return value's text representation
    format: (value) -> if @formatter? then @formatter.format value else _.escape value

    # Get column's header text
    getHeader: -> @attributes.header

    # Get column's class name
    getClass: -> "col-" + @id + if @canOrder() then " col__sortable" else ""

    # Get whether a column can order items
    canOrder: -> @attributes.can_order