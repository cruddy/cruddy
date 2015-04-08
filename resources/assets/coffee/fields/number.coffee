class Cruddy.Fields.Number extends Cruddy.Fields.Input

    createFilterInput: (model) -> new Cruddy.Inputs.NumberFilter
        model: model
        key: @id

    prepareFilterData: (value) ->
        return null if _.isEmpty value.val

        return (if value.op is "=" then "" else value.op) + value.val

    parseFilterData: (value) ->
        op = ">"
        val = null

        if _.isString(value) and value.length
            op = value[0]
            if op in [ "=", "<", ">" ]
                val = value.substr 1
            else
                op = "="
                val = value

        else if _.isNumber value
            op = "="
            val = value

        return op: op, val: val

    getType: -> "number"