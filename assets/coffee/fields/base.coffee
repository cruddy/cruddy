Cruddy.Fields = new Factory

class Cruddy.Fields.BaseView extends Backbone.View

    constructor: (options) ->
        @field = field = options.field

        @inputId = options.model.entity.id + "_" + field.id

        base = " field-"
        classes = [ field.getType(), field.id, @inputId ]
        className = "field" + base + classes.join base

        className += " required" if field.isRequired()
        className += " form-group"

        @className = if @className then className + " " + @className else className

        super

    initialize: (options) ->
        @listenTo @model, "sync",    @handleSync
        @listenTo @model, "request", @handleRequest
        @listenTo @model, "invalid", @handleInvalid

        this

    handleSync: -> @toggleVisibility()

    handleRequest: -> @hideError()

    handleInvalid: (model, errors) ->
        if @field.id of errors
            error = errors[@field.id]

            @showError if _.isArray error then _.first error else error

        this

    toggleVisibility: ->
        @$el.toggle @isVisible()

        this

    hideError: ->
        @error.hide()

        this

    showError: (message) ->
        @error.text(message).show()

        this

    focus: -> this

    render: ->
        @$(".field-help").tooltip
            container: "body"
            placement: "left"

        @error = @$ "##{ @cid }-error"

        this

    helpTemplate: ->
        help = @field.getHelp()
        if help then """<span class="glyphicon glyphicon-question-sign field-help" title="#{ _.escape help }"></span>""" else ""

    errorTemplate: -> """<span class="help-block error" id="#{ @cid }-error"></span>"""

    # Get whether the view is visible
    # The field is not visible when model is new and field is not editable or computed
    isVisible: -> @field.isEditable(@model.action()) or not @model.isNew()

    dispose: -> this

    remove: ->
        @dispose()

        super

# This is basic field view that will render in bootstrap's vertical form style.
class Cruddy.Fields.InputView extends Cruddy.Fields.BaseView
    handleRequest: (model) ->
        @isEditable = @field.isEditable(model.action())

        super

    handleSync: (model) ->
        @render() if @field.isEditable(model.action()) isnt @isEditable

        super

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

        @input = @field.createInput @model

        @$el.append @input.render().el

        @$el.append @errorTemplate()

        @toggleVisibility()

        @isEditable = @field.isEditable(@model.action())

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
        @input.focus()

        this

    dispose: ->
        @input?.remove()

        this

class Cruddy.Fields.Base extends Attribute
    viewConstructor: Cruddy.Fields.InputView

    # Create a view that will represent this field in field list
    createView: (model) -> new @viewConstructor { model: model, field: this }

    # Create an input that is used by default view
    createInput: (model) ->
        input = @createEditableInput model if @isEditable(model.action()) and model.isSaveable()

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

    # Get whether the field is editable for specified action
    isEditable: (action) -> @attributes.fillable and @attributes.disabled isnt yes and @attributes.disabled isnt action

    # Get whether field is required
    isRequired: -> @attributes.required

    # Get whether the field is unique
    isUnique: -> @attributes.unique