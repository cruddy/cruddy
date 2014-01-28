# Renders formatted text and doesn't have any editing features.
class Cruddy.Inputs.Static extends Cruddy.Inputs.Base
    tagName: "p"
    className: "form-control-static"

    initialize: (options) ->
        @formatter = options.formatter if options.formatter?

        super

    applyChanges: (data) -> @render()

    render: ->
        value = @getValue()
        value = @formatter.format value if @formatter?

        @$el.html value

        this