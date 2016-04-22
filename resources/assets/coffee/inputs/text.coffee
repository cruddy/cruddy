class Cruddy.Inputs.BaseText extends Cruddy.Inputs.Base
    className: "form-control"

    events:
        "change": "submitValue"
        "keydown": "handleKeydown"

    handleKeydown: (e) ->
        # Ctrl + Enter
        return @submitValue() if e.ctrlKey and e.keyCode is 13

        this

    disable: ->
        @$el.prop "disabled", yes

        this

    enable: ->
        @$el.prop "disabled", no

        this

    submitValue: -> @setValue @$el.val()

    handleValueChanged: (newValue, bySelf) ->
        @$el.val newValue unless bySelf

        this

    focus: ->
        @el.focus()

        this

# Renders an <input> value of which is bound to a model's attribute.
class Cruddy.Inputs.Text extends Cruddy.Inputs.BaseText
    tagName: "input"

    initialize: (options) ->
        # Apply mask
        options.mask and @$el.mask options.mask

        super

# Renders a <textarea> input.
class Cruddy.Inputs.Textarea extends Cruddy.Inputs.BaseText
    tagName: "textarea"