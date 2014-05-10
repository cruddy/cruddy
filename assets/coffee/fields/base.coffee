Cruddy.Fields = new Factory

class Cruddy.Fields.BaseView extends Backbone.View

    constructor: (options) ->
        @field = field = options.field

        inputId = options.model.entity.id + "__" + field.id
        @inputId = inputId + "__" + options.model.cid

        base = " field-"
        classes = [ field.getType(), field.id, inputId ]
        className = "field" + base + classes.join base

        className += " form-group"

        @className = if @className then className + " " + @className else className

        @forceDisable = options.forceDisable ? false

        super

    initialize: (options) ->
        @listenTo @model, "sync",    @handleSync
        @listenTo @model, "request", @handleRequest
        @listenTo @model, "invalid", @handleInvalid

        @updateContainer()

    handleSync: -> @updateContainer()

    handleRequest: -> @hideError()

    handleInvalid: (model, errors) ->
        if @field.id of errors
            error = errors[@field.id]

            @showError if _.isArray error then _.first error else error

        this

    updateContainer: ->
        @isEditable = not @forceDisable and @field.isEditable(@model)

        @$el.toggle @isVisible()
        @$el.toggleClass "required", @field.isRequired @model

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
    isVisible: -> @isEditable or not @model.isNew()

    dispose: -> this

    remove: ->
        @dispose()

        super

# This is basic field view that will render in bootstrap's vertical form style.
class Cruddy.Fields.InputView extends Cruddy.Fields.BaseView

    updateContainer: ->
        isEditable = @isEditable
        
        super

        @render() if isEditable isnt @isEditable


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
        @input.focus()

        this

    dispose: ->
        @input?.remove()

        this

class Cruddy.Fields.Base extends Attribute
    viewConstructor: Cruddy.Fields.InputView

    # Create a view that will represent this field in field list
    createView: (model, forceDisable = no) -> new @viewConstructor { model: model, field: this, forceDisable: forceDisable }

    # Create an input that is used by default view
    createInput: (model, inputId, forceDisable = no) ->
        input = @createEditableInput model, inputId if not forceDisable and @isEditable(model)

        input or new Cruddy.Inputs.Static { model: model, key: @id, formatter: this }

    # Create an input that is used when field is editable
    createEditableInput: (model, inputId) -> null

    # Create filter input that
    createFilterInput: (model) -> null

    # Get a label for filter input
    getFilterLabel: -> @attributes.label

    # Format value as static text
    format: (value) -> value or "n/a"

    # Get field's label
    getLabel: -> @attributes.label

    # Get whether the field is editable for specified model
    isEditable: (model) -> model.isSaveable() and @attributes.fillable and @attributes.disabled isnt yes and @attributes.disabled isnt model.action()

    # Get whether field is required
    isRequired: (model) -> @attributes.required is yes or @attributes.required == model.action()

    # Get whether the field is unique
    isUnique: -> @attributes.unique