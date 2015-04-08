class Cruddy.Fields.Boolean extends Cruddy.Fields.Base

    createEditableInput: (model) -> new Cruddy.Inputs.Boolean
        model: model
        key: @id

    createFilterInput: (model) -> new Cruddy.Inputs.Boolean
        model: model
        key: @id
        tripleState: yes

    format: (value) -> if value then Cruddy.lang.yes else Cruddy.lang.no

    prepareAttribute: (value) ->
        return 0 if value is false
        return 1 if value is true

        return null

    parseFilterData: (value) ->
        value = parseInt value

        return true if value is 1
        return false if value is 0

        return null

    getType: -> "bool"