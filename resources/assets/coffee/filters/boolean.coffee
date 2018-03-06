class Cruddy.Filters.Boolean extends Cruddy.Filters.Base
    createFilterInput: (model) -> new Cruddy.Inputs.Boolean
        model: model
        key: @id
        tripleState: yes

    prepareData: (value) ->
        return 0 if value is false
        return 1 if value is true

        return null

    parseData: (value) ->
        value = parseInt value

        return true if value is 1
        return false if value is 0

        return null