class Cruddy.Filters.Entity extends Cruddy.Filters.Base
    createFilterInput: (model) -> new Cruddy.Inputs.EntityDropdown
        model: model
        key: @id
        reference: Cruddy.app.entity @attributes.refEntityId
        allowEdit: no
        placeholder: Cruddy.lang.any_value
        owner: @entity.id + "." + @id

    prepareData: (value) ->
        return _.pluck(value, "id").join(",") if _.isArray(value) && value.length

        return value && value.id || null

    parseData: (value) ->
        return null unless _.isString(value) or _.isNumber(value)

        value = value.toString()

        return null unless value.length

        return { id: value }

        value = value.split ","

        return _.map value, (value) -> { id: value }