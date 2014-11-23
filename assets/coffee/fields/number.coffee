class Cruddy.Fields.Number extends Cruddy.Fields.Input

    createFilterInput: (model) -> new Cruddy.Inputs.NumberFilter
        model: model
        key: @id

    prepareFilterData: (value) ->
        return null if _.isEmpty value.val

        return value.op + value.val

    parseFilterData: (value) ->
        op = "="
        val = null

        if value?
            op = value[0]
            val = value.substr 1

        return op: op, val: val