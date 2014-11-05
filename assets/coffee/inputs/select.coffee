class Cruddy.Inputs.Select extends Cruddy.Inputs.Text
    tagName: "select"

    initialize: (options) ->
        @items = options.items ? {}
        @prompt = options.prompt ? null
        @required = options.required ? no

        super

    applyChanges: (data, external) ->
        @$(":nth-child(#{ @optionIndex data })").prop "selected", yes if external

        this

    optionIndex: (value) ->
        index = if @hasPrompt() then 2 else 1

        return index unless value?

        value = value.toString()

        for data, label of @items
            break if value == data.toString()

            index++

        index

    render: ->
        @$el.html @template()

        @setValue @$el.val() if @required and not @getValue()

        super

    template: ->
        html = ""
        html += @optionTemplate "", @prompt ? Cruddy.lang.not_selected, @required if @hasPrompt()
        html += @optionTemplate key, value for key, value of @items
        html

    optionTemplate: (value, title, disabled = no) ->
        """<option value="#{ _.escape value }"#{ if disabled then " disabled" else ""}>#{ _.escape title }</option>"""

    hasPrompt: -> not @required or @prompt?