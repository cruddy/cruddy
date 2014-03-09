class Attribute extends Backbone.Model

    initialize: (options) ->
        @entity = options.entity

        this

    # Get field's type (i.e. css class name)
    getType: -> @attributes.type

    # Get field's help
    getHelp: -> @attributes.help

    # Get whether a column has complex filter
    canFilter: -> @attributes.filter_type is "complex"

    # Get whether a column is visible
    isVisible: -> @attributes.hide is no