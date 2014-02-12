Cruddy.Fields = new Factory

# This is basic field view that will render in bootstrap's vertical form style.
class FieldView extends Backbone.View
    className: "field"

    constructor: (options) ->
        field = options.field

        @inputId = options.model.entity.id + "_" + field.id

        base = " " + @className + "-"
        classes = [ field.getType(), field.id, @inputId ]
        @className += base + classes.join base

        @className += " required" if field.isRequired()

        super

    initialize: (options) ->
        @field = options.field

        @listenTo @model, "sync",    @toggleVisibility
        @listenTo @model, "request", @hideError
        @listenTo @model, "invalid", @showError

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

        @input = @field.createInput @model
        @inputHolder.append @input.render().el if @input?

        @inputHolder.append @error = $ @errorTemplate()

        @toggleVisibility()

        this

    helpTemplate: ->
        help = @field.getHelp()
        if help then """<span class="glyphicon glyphicon-question-sign field-help" title="#{ help }"></span>""" else ""

    errorTemplate: -> """<span class="help-block error"></span>"""

    label: (label) ->
        label ?= @field.getLabel()
        """<label for="#{ @inputId }">#{ label }</label>"""

    # The default template that is shown when field is editable.
    template: ->
        """
        #{ @helpTemplate() }
        <div class="form-group input-holder">
            #{ @label() }
        </div>
        """

    # Get whether the view is visible
    isVisible: -> @field.isEditable() or not @model.isNew()

    # Toggle visibility
    toggleVisibility: -> @$el.toggle @isVisible()

    # Focus the input that this field view holds.
    focus: ->
        @input.focus() if @input?

        this

    dispose: ->
        @input?.remove()

        @input = null

        this

    remove: ->
        @dispose()

        super

class Cruddy.Fields.Base extends Attribute
    viewConstructor: FieldView

    # Create a view that will represent this field in field list
    createView: (model) -> new @viewConstructor { model: model, field: this }

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