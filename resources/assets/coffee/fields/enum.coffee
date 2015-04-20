class Cruddy.Fields.Enum extends Cruddy.Fields.Input

    createBaseInput: (model, inputId) -> new Cruddy.Inputs.Select
        model: model
        key: @id
        prompt: @attributes.prompt
        items: @attributes.items
        required: @attributes.required
        multiple: @attributes.multiple
        attributes:
            id: inputId

    createFilterInput: (model) -> new Cruddy.Inputs.Select
        model: model
        key: @id
        prompt: Cruddy.lang.any_value
        items: @attributes.items
        multiple: yes

    format: (value) ->
        items = @attributes.items

        value = [ value ] unless _.isArray value

        labels = ((if key of items then items[key] else key) for key in value)

        labels.join ", "

    parseFilterData: (value) -> if _.isString value then value.split "," else null

    getType: -> "enum"