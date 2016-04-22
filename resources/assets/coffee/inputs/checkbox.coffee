# Renders a checkbox
class Cruddy.Inputs.Checkbox extends Cruddy.Inputs.Base
    tagName: "label"

    events:
        "change :checkbox": "handleCheckboxChanged"

    initialize: (options) ->
        @label = options.label || null

        super

    handleCheckboxChanged: -> @setValue @input.prop "checked"

    handleValueChanged: (newValue, bySelf) ->
        @input.prop "checked", newValue unless bySelf

        this

    render: ->
        @input = $ "<input>", { type: "checkbox", checked: @getValue() }
        @$el.append @input
        @$el.append @label if @label?

        this