class Cruddy.Fields.Input extends Cruddy.Fields.Base

    createEditableInput: (model, inputId) ->
        attributes =
            placeholder: @attributes.placeholder
            id: inputId
            
        type = @attributes.input_type

        if type is "textarea"
            attributes.rows = @attributes.rows

            new Cruddy.Inputs.Textarea
                model: model
                key: @id
                attributes: attributes
        else
            attributes.type = type

            new Cruddy.Inputs.Text
                model: model
                key: @id
                mask: @attributes.mask
                attributes: attributes

    format: (value) -> if @attributes.input_type is "textarea" then "<pre>#{ super }</pre>" else super