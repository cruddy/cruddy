class Cruddy.Fields.Boolean extends Cruddy.Fields.Base

    createEditableInput: (model) -> new Cruddy.Inputs.Boolean
        model: model
        key: @id

    format: (value) -> if value then Cruddy.lang.yes else Cruddy.lang.no

    prepareAttribute: (value) ->
        return 0 if value is false
        return 1 if value is true

        return null

    getType: -> "bool"