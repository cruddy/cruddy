# Renders formatted text and doesn't have any editing features.
class StaticInput extends BaseInput
    tagName: "p"
    className: "form-control-static"

    initialize: (options) ->
        @formatter = options.formatter if options.formatter?

        super

    applyChanges: (model, data) -> @render()

    render: ->
        value = @model.get @key
        value = @formatter.format value if @formatter?

        @$el.html value

        this