# Search input implements "change when type" and also allows to clear text with Esc
class Cruddy.Inputs.Search extends Cruddy.View
    className: "input-group"

    events:
        "click .btn-search": "search"

    initialize: (options) ->
        @input = new Cruddy.Inputs.Text
            model: @model
            key: options.key
            attributes:
                type: "search"
                placeholder: Cruddy.lang.search

        super

    search: (e) ->
        if e
            e.preventDefault()
            e.stopPropagation()

        @input.change()

        return

    appendButton: (btn) -> @$btns.append btn

    render: ->
        @$el.append @input.render().$el
        @$el.append @$btns = $ """<div class="input-group-btn"></div>"""

        @appendButton """
            <button type="button" class="btn btn-default btn-search" title="#{ Cruddy.lang.find }">
                <span class="glyphicon glyphicon-search"></span>
            </button>
        """

        return this

    focus: ->
        @input.focus()

        return this