# Renders a checkbox
class Checkbox extends BaseInput
    tagName: "label"
    label: ""

    events:
        "change": "change"

    initialize: (options) ->
        @label = options.label if options.label?

        super

    change: ->
        @model.set @key, @input.prop "checked"

        this

    applyChanges: (model, value) ->
        @input.prop "checked", value

        this

    render: ->
        @input = $ "<input>", { type: "checkbox", checked: @model.get @key }
        @$el.append @input
        @$el.append @label if @label?

        this