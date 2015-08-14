# This is basic field view that will render in bootstrap's vertical form style.
class Cruddy.Fields.InputView extends Cruddy.Fields.BaseView

    updateContainer: ->
        isEditable = @isEditable

        super

        @render() if isEditable? and isEditable isnt @isEditable


    hideError: ->
        @$el.removeClass "has-error"

        super

    showError: ->
        @$el.addClass "has-error"

        super

    # Render a field
    render: ->
        @dispose()

        @$el.html @template()

        @input = @field.createInput @model, @inputId, @forceDisable

        @$el.append @input.render().el

        @$el.append @errorTemplate()

        super

    label: (label) ->
        label ?= @field.getLabel()

        """
        <label for="#{ @inputId }" class="field-label">
            #{ @helpTemplate() }#{ _.escape label }
        </label>
        """

    # The default template that is shown when field is editable.
    template: -> @label()

    # Focus the input that this field view holds.
    focus: ->
        @input?.focus()

        this

    dispose: ->
        @input?.remove()

        this