# Renders an <input> value of which is bound to a model's attribute.
class TextInput extends BaseInput
    tagName: "input"

    className: "form-control"
    size: "sm"

    events:
        "change": "change"
        "keydown": "keydown"

    constructor: (options) ->
        @size = options.size if options.size?

        @className += " input-#{ @size }"

        super

    scheduleChange: ->
        clearTimeout @timeout if @timeout?
        @timeout = setTimeout (=> @change()), 300

        this

    keydown: (e) ->
        # Ctrl + Enter
        if e.ctrlKey and e.keyCode is 13
            @change()
            return false

        # Escape
        if e.keyCode is 27
            @model.set @key, ""
            return false

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