class Cruddy.Inputs.Boolean extends Cruddy.Inputs.Base
    events:
        "click .btn": "check"

    initialize: (options) ->
        @tripleState = options.tripleState ? false

        super

    check: (e) ->
        currentValue = @getValue()
        value = !!$(e.target).data "value"

        value = null if value is currentValue and @tripleState

        @setValue value

    handleValueChanged: (value) ->
        value = switch value
            when yes then 0
            when no then 1
            else null

        @values.removeClass "active"
        @values.eq(value).addClass "active" if value?

        this

    render: ->
        @$el.html @template()

        @values = @$ ".btn"

        super

    template: ->
        """
        <div class="btn-group">
            <button type="button" class="btn btn-default" data-value="1">#{ Cruddy.lang.yes }</button>
            <button type="button" class="btn btn-default" data-value="0">#{ Cruddy.lang.no }</button>
        </div>
        """

    focus: ->
        @values?[0].focus()

        this