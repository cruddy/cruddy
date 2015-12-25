class Cruddy.Inputs.Slug extends Backbone.View
    events:
        "click .btn": "toggleSyncing"

    constructor: (options) ->
        @input = new Cruddy.Inputs.Text _.clone options

        options.className ?= "input-group"

        delete options.attributes if options.attributes?

        super

    initialize: (options) ->
        @key = options.key
        @ref = if _.isArray(options.field) then options.field else [options.field] if options.field

        @$el.removeClass("input-group") unless @ref

        @listenTo @model, "change:" + @key, =>
            @unlink() unless @linkable()

        super

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
        modelValue = @model.get @key
        value = @getSlug()

        return value == modelValue or modelValue is null and value is ""

    sync: ->
        @model.set @key, @getSlug()

        this

    getSlug: ->
        components = []

        for key in @ref
            refValue = @model.get key
            components.push refValue if refValue

        if components.length then components.join " " else ""

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