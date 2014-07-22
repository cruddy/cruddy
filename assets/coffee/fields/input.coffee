class Cruddy.Fields.Input extends Cruddy.Fields.Base

    createEditableInput: (model, inputId) ->
        input = @createBaseInput model, inputId

        if @attributes.prepend or @attributes.append
            return new Cruddy.Fields.Input.PrependAppendWrapper
                prepend: @attributes.prepend
                append: @attributes.append
                input: input

        return input

    createBaseInput: (model, inputId) -> new Cruddy.Inputs.Text
        model: model
        key: @id
        mask: @attributes.mask
        attributes:
            placeholder: @attributes.placeholder
            id: inputId
            type: @attributes.input_type or "input"

    format: (value) ->
        return NOT_AVAILABLE if value is null or value is ""

        value += " " + @attributes.append if @attributes.append
        value = @attributes.prepend + " " + value if @attributes.prepend

        return value

class Cruddy.Fields.Input.PrependAppendWrapper extends Cruddy.View
    className: "input-group"

    initialize: (options) ->
        @$el.append @createAddon options.prepend if options.prepend
        @$el.append (@input = options.input).$el
        @$el.append @createAddon options.append if options.append

    render: ->
        @input.render()

        return this

    createAddon: (text) -> "<span class=input-group-addon>" + _.escape(text) + "</span>"