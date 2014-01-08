# Renders an <input> value of which is bound to a model's attribute.
class TextInput extends BaseInput
    tagName: "input"

    events:
        "change": "change"
        "keydown": "keydown"

    constructor: (options) ->
        options.className ?= "form-control"
        options.className += " input-#{ options.size ? "sm" }"

        super

    keydown: (e) ->
        # Ctrl + Enter
        return @change() if e.ctrlKey and e.keyCode is 13

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

# Renders a <textarea> input.
class Textarea extends TextInput
    tagName: "textarea"