Cruddy.Fields = new Factory

class Cruddy.Fields.BaseView extends Backbone.View

    constructor: (options) ->
        @field = field = options.field

        @inputId = options.model.entity.id + "_" + field.id

        base = " field-"
        classes = [ field.getType(), field.id, @inputId ]
        className = "field" + base + classes.join base

        className += " required" if field.isRequired()

        @className = if @className then className + " " + @className else className

        super

    initialize: (options) ->
        @listenTo @model, "sync",    @toggleVisibility
        @listenTo @model, "request", @hideError
        @listenTo @model, "invalid", @showError

        this

    toggleVisibility: -> @$el.toggle @isVisible()

    hideError: -> this

    showError: -> this

    focus: -> this

    render: ->
        @$(".field-help").tooltip
            container: "body"
            placement: "left"

        this

    helpTemplate: ->
        help = @field.getHelp()
        if help then """<span class="glyphicon glyphicon-question-sign field-help" title="#{ help }"></span>""" else ""

    errorTemplate: -> """<span class="help-block error"></span>"""

    # Get whether the view is visible
    isVisible: -> @field.isEditable() or not @model.isNew()

    dispose: -> this

    remove: ->
        @dispose()

        super

# This is basic field view that will render in bootstrap's vertical form style.
class Cruddy.Fields.InputView extends Cruddy.Fields.BaseView
    initialize: (options) ->
        @input = options.input

        this

    hideError: ->
        @error.hide()
        @inputHolder.removeClass "has-error"

        this

    showError: (model, errors) ->
        error = errors[@field.get "id"]

        if error
            @inputHolder.addClass "has-error"
            @error.text(error).show()

        this

    # Render a field
    render: ->
        @dispose()

        @$el.html @template()

        @inputHolder = @$ ".input-holder"
        @inputHolder.append @input.render().el

        @inputHolder.append @error = $ @errorTemplate()

        @toggleVisibility()

        super

    label: (label) ->
        label ?= @field.getLabel()
        
        """
        <label for="#{ @inputId }" class="field-label">
            #{ @helpTemplate() }#{ label }
        </label>
        """

    # The default template that is shown when field is editable.
    template: ->
        """
        <div class="form-group input-holder">
            #{ @label() }
        </div>
        """

    # Focus the input that this field view holds.
    focus: ->
        @input.focus()

        this

    remove: ->
        @input.remove()

        super

class Cruddy.Fields.Base extends Attribute
    viewConstructor: Cruddy.Fields.InputView

    # Create a view that will represent this field in field list
    createView: (model) -> new @viewConstructor { model: model, field: this, input: @createInput(model) }

    # Create an input that is used by default view
    createInput: (model) ->
        input = @createEditableInput model if @isEditable() and model.isSaveable()

        input or new Cruddy.Inputs.Static { model: model, key: @id, formatter: this }

    # Create an input that is used when field is editable
    createEditableInput: (model) -> null

    # Create filter input that
    createFilterInput: (model) -> null

    # Get a label for filter input
    getFilterLabel: -> @attributes.label

    # Format value as static text
    format: (value) -> value or "n/a"

    # Get field's label
    getLabel: -> @attributes.label

    # Get whether the field is editable
    isEditable: -> @attributes.fillable

    # Get whether field is required
    isRequired: -> @attributes.required

    # Get whether the field is unique
    isUnique: -> @attributes.unique