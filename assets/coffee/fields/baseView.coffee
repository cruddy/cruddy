class Cruddy.Fields.BaseView extends Cruddy.Layout.Element

    constructor: (options) ->
        @field = field = options.field
        model = options.model

        @inputId = [ model.entity.id, field.id, model.cid ].join "__"

        className = "form-group field field__#{ field.getType() } field--#{ field.id } field--#{ model.entity.id }--#{ field.id }"

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

        @handleValidationError message

        return this

    focus: -> this

    render: ->
        @$(".field-help").tooltip
            container: "body"
            placement: "left"

        @error = @$component "error"

        this

    helpTemplate: ->
        help = @field.getHelp()
        if help then """<span class="glyphicon glyphicon-question-sign field-help" title="#{ _.escape help }"></span>""" else ""

    errorTemplate: -> """<span class="help-block error" style="display:none;" id="#{ @componentId "error" }"></span>"""

    # Get whether the view is visible
    # The field is not visible when model is new and field is not editable or computed
    isVisible: -> @isEditable or not @model.isNew()

    isFocusable: -> @field.isEditable @model

    dispose: -> this

    remove: ->
        @dispose()

        super