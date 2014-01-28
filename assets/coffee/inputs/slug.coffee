class Cruddy.Inputs.Slug extends Backbone.View
    events:
        "click .btn": "toggleSyncing"

    constructor: (options) ->
        @input = new Cruddy.Inputs.Text _.clone options

        options.className ?= "input-group"

        delete options.attributes if options.attributes?

        super

    initialize: (options) ->
        chars = options.chars ? "a-z0-9\-_"

        @regexp = new RegExp "[^#{ chars }]+", "g"
        @separator = options.separator ? "-"

        @key = options.key
        @ref = options.ref if options.ref?

        super

    toggleSyncing: ->
        if @syncButton.hasClass "active" then @unlink() else @link()

        this

    link: ->
        return if not @ref

        @listenTo @model, "change:" + @ref, @sync
        @syncButton.addClass "active"
        @input.disable()

        @sync()

    unlink: ->
        @stopListening @model, null, @sync if @ref?
        @syncButton.removeClass "active"
        @input.enable()

        this

    linkable: ->
        refValue = @convert @model.get @ref
        refValue == @model.get @key

    convert: (value) -> if value then value.toLocaleLowerCase().replace(/\s+/g, @separator).replace(@regexp, "") else value

    change: ->
        @unlink()

        @$el.val @convert @$el.val()

        super

    sync: ->
        @model.set @key, @convert @model.get @ref

        this

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
            <button type="button" tabindex="-1" class="btn btn-default" title="Связать с полем #{ @model.entity.fields.get(@ref).get "label" }"><span class="glyphicon glyphicon-link"></span></button>
        </div>
        """