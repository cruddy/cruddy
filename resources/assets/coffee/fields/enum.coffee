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

    format: (value) ->
        items = @attributes.items

        value = [ value ] unless _.isArray value

        labels = ((if key of items then items[key] else key) for key in value)

        labels.join ", "

    getType: -> "enum"