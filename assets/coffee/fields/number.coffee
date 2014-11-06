class Cruddy.Fields.Number extends Cruddy.Fields.Input

    createFilterInput: (model) -> new Cruddy.Inputs.NumberFilter
        model: model
        key: @id

    prepareFilterData: (value) ->
        return if _.isEmpty value.val

        return value