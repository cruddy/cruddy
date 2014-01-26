class Cruddy.fields.Input extends Field
    createEditableInput: (model) ->
        attributes = placeholder: @get "label"
        type = @get "input_type"

        if type is "textarea"
            attributes.rows = @get "rows"

            new Textarea
                model: model
                key: @id
                attributes: attributes
        else
            attributes.type = type

            new TextInput
                model: model
                key: @id
                mask: @get "mask"
                attributes: attributes

    format: (value) -> if @get("input_type") is "textarea" then "<pre>#{ super }</pre>" else super

    createFilterInput: (model, column) ->
        new TextInput
                model: model
                key: @id
                attributes:
                    placeholder: @get "label"