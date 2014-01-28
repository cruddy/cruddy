class Cruddy.Inputs.Select extends Cruddy.Inputs.Text
    tagName: "select"

    initialize: (options) ->
        @items = options.items ? {}
        @prompt = options.prompt ? null

        super

    applyChanges: (data, external) ->
        @$("[value='#{ data }']").prop "selected", yes if external

        this

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