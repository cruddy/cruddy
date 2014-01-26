class Cruddy.Inputs.BaseText extends BaseInput
    className: "form-control"

    events:
        "change": "change"
        "keydown": "keydown"

    initialize: (options) ->
        @$el.addClass "input-#{ options.size ? "sm" }"

        super

    keydown: (e) ->
        # Ctrl + Enter
        return @change() if e.ctrlKey and e.keyCode is 13

        this

    disable: ->
        @$el.prop "disabled", yes

        this

    enable: ->
        @$el.prop "disabled", no

        this

    change: ->
        @model.set @key, @el.value

        this

    applyChanges: (model, data) ->
        @$el.val data

        this

    focus: ->
        @el.focus()

        this
        
# Renders an <input> value of which is bound to a model's attribute.
class TextInput extends Cruddy.Inputs.BaseText
    tagName: "input"

    initialize: (options) ->
        # Apply mask
        options.mask and @$el.mask options.mask

        super

# Renders a <textarea> input.
class Textarea extends Cruddy.Inputs.BaseText
    tagName: "textarea"