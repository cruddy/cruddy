class Cruddy.Attribute extends Backbone.Model

    initialize: (options) ->
        @entity = options.entity

        this

    # Get field's type (i.e. css class name)
    getType: -> "attribute"

    # Get field's help
    getHelp: -> @attributes.help

    # Get whether a column is visible
    isVisible: -> @attributes.hide is no