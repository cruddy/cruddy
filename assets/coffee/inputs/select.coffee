class Cruddy.Inputs.Select extends Cruddy.Inputs.Text
    tagName: "select"

    initialize: (options) ->
        @items = options.items ? {}
        @prompt = options.prompt ? null

        super

    applyChanges: (data, external) ->
        @$(":nth-child(#{ @optionIndex data })").prop "selected", yes if external

        this

    optionIndex: (value) ->
        index = if @prompt then 2 else 1

        for data, label of @items
            break if value == data

            index++

        index

    render: ->
        @$el.html @template()

        super

    template: ->
        html = ""
        html += @optionTemplate "", @prompt ? ""
        html += @optionTemplate key, value for key, value of @items
        html

    optionTemplate: (value, title) ->
        """<option value="#{ _.escape value }">#{ _.escape title }</option>"""