Cruddy.Columns = new Factory

class Cruddy.Columns.Base extends Attribute
    initialize: (attributes) ->
        @formatter = Cruddy.formatters.create attributes.formatter, attributes.formatterOptions if attributes.formatter?

        super

    # Return value's text representation
    format: (value) -> if @formatter? then @formatter.format value else value

    # Create input that is used for complex filtering
    createFilter: (model) -> null

    # Get column's header text
    getHeader: -> @attributes.header

    # Get column's class name
    getClass: -> "col-" + @id

    # Get the label for a filter
    getFilterLabel: -> @getHeader()

    # Get whether a column can order items
    canOrder: -> @attributes.can_order