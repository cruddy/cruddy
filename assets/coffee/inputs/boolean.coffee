class BooleanInput extends BaseInput
    tripleState: false

    events:
        "click .btn": "check"

    initialize: (options) ->
        @tripleState = options.tripleState if options.tripleState?

        super

    check: (e) ->
        value = !!$(e.target).data "value"
        currentValue = @model.get @key

        value = null if value == currentValue and @tripleState

        @model.set @key, value

        this

    applyChanges: (model, value) ->
        value = switch value
            when yes then 0
            when no then 1
            else null

        @values.removeClass("active")
        @values.eq(value).addClass "active" if value?

        this

    render: ->
        @$el.html @template()

        @values = @$ ".btn"

        super

    template: ->
        """
        <div class="btn-group">
            <button type="button" class="btn btn-info" data-value="1">да</button>
            <button type="button" class="btn btn-default" data-value="0">нет</button>
        </div>
        """

    itemTemplate: (label, value) -> """
        <label class="radio-inline">
            <input type="radio" name="#{ @cid }" value="#{ value }">
            #{ label }
        </label>
        """