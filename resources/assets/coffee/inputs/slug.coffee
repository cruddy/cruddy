class Cruddy.Inputs.Slug extends Cruddy.Inputs.Base
    events:
        "click .btn": "toggleSyncing"

    constructor: (options) ->
        @input = new Cruddy.Inputs.Text _.clone options

        options.className ?= "input-group"

        delete options.attributes if options.attributes?

        super

    initialize: (options) ->
        @ref = if _.isArray(options.field) then options.field else [options.field] if options.field

        @$el.removeClass "input-group" unless @ref

        super

    handleValueChanged: ->
        @unlink() unless @linkable()

        return this

    toggleSyncing: ->
        if @syncButton.hasClass "active" then @unlink() else @link()

        this

    link: ->
        return unless @ref

        @listenTo @model, "change:" + @ref.join(" change:"), @sync
        @syncButton.addClass "active"

        @input.disable()

        @sync()

    unlink: ->
        @stopListening @model, null, @sync if @ref?
        @syncButton.removeClass "active"

        @input.enable()

        this

    linkable: ->
        modelValue = @getValue()
        value = @getSlug()

        return value == modelValue

    sync: -> @setValue @getSlug()

    getSlug: ->
        components = []

        for key in @ref
            refValue = @model.get key
            components.push refValue if refValue

        if components.length then components.join " " else null

    render: ->
        @$el.html @template()
        @$el.prepend @input.render().el

        if @ref?
            @syncButton = @$ ".btn"
            @link() if @linkable()

        this

    template: ->
        return "" if not @ref?

        """
        <div class="input-group-btn">
            <button type="button" tabindex="-1" class="btn btn-default" title="#{ Cruddy.lang.slug_sync }"><span class="glyphicon glyphicon-link"></span></button>
        </div>
        """