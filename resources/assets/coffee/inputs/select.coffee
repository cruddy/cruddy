class Cruddy.Inputs.Select extends Cruddy.Inputs.BaseText
    tagName: "select"

    initialize: (options) ->
        @items = options.items ? {}
        @prompt = options.prompt ? null
        @required = options.required ? no
        @multiple = options.multiple ? no

        @$el.attr "multiple", "multiple" if @multiple

        super

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

    hasPrompt: -> not @multiple and (not @required or @prompt)