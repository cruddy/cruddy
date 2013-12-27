class BooleanInput extends BaseInput
    tripleState: false

    events:
        "click input": "check"

    initialize: (options) ->
        @tripleState = options.tripleState if options.tripleState?

    check: (e) ->
        @model.set @key, switch e.target.value
            when "1" then yes
            when "0" then no
            else null

        this

    applyChanges: (model, value) ->
        value = switch value
            when yes then "1"
            when no then "0"
            else ""

        @$("[value=\"#{ value }\"]").prop "checked", true

        this

    render: ->
        @$el.empty()
        @$el.append @itemTemplate "неважно", "" if @tripleState
        @$el.append @itemTemplate "да", 1
        @$el.append @itemTemplate "нет", 0

        super

    itemTemplate: (label, value) -> """
        <label class="radio-inline">
            <input type="radio" name="#{ @cid }" value="#{ value }">
            #{ label }
        </label>
        """