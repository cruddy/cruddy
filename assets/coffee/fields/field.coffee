Cruddy.fields = new Factory

class FieldView extends Backbone.View
    className: "field"

    constructor: (options) ->
        @inputId = options.model.entity.id + "_" + options.field.id

        base = " " + @className + "-"
        classes = [ options.field.attributes.type, options.field.id, @inputId ]
        @className += base + classes.join base

        @className += " required" if options.field.get "required"

        super

    initialize: (options) ->
        @field = options.field

        @listenTo @field, "change:visible",     @toggleVisibility
        @listenTo @field, "change:editable",    @render

        @listenTo @model, "sync",       @render
        @listenTo @model, "request",    @hideError
        @listenTo @model, "invalid",    @showError

        this

    hideError: ->
        @error.hide()
        @inputHolder.removeClass "has-error"

    showError: (model, errors) ->
        error = errors[@field.get "id"]

        if error
            @inputHolder.addClass "has-error"
            @error.text(error).show()

    # Render a field.
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
        help = @field.get "help"
        if help then """<span class="glyphicon glyphicon-question-sign field-help" title="#{ help }"></span>""" else ""

    errorTemplate: -> """<span class="help-block error"></span>"""

    label: (label) ->
        label ?= @field.get "label"
        """<label for="#{ @inputId }">#{ label }</label>"""

    # The default template that is shown when field is editable.
    template: ->
        """
        #{ @helpTemplate() }
        <div class="form-group input-holder">
            #{ @label() }
        </div>
        """

    # Get whether this field view is visible.
    isVisible: -> @field.get("visible") and (@field.get("editable") and @field.get("updateable") or not @model.isNew())

    toggleVisibility: -> @$el.toggle @isVisible()

    # Focus the input that this field view holds.
    focus: ->
        @input.focus() if @input?

        this

    dispose: ->
        @input?.remove()

        this

    stopListening: ->
        @dispose()

        super

class Field extends Attribute
    viewConstructor: FieldView

    createView: (model) -> new @viewConstructor { model: model, field: this }

    createInput: (model) ->
        input = @createEditableInput model if @isEditable model

        if input? then input else new StaticInput { model: model, key: @id, formatter: this }

    createEditableInput: (model) -> null

    format: (value) -> if value then value else "n/a"

    isEditable: (model) -> @get("editable") and (@get("updateable") or not model.isNew()) and model.isSaveable()