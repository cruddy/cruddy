class Cruddy.Layout.TabPane extends Cruddy.Layout.BaseFieldContainer
    className: "tab-pane"

    initialize: (options) ->
        super

        @title = @entity.get("title").singular if not options.title
        
        @$el.attr "id", @cid

        @listenTo @model, "request", -> @header.resetErrors() if @header

        return this

    activate: ->
        @header?.activate()

        after_break => @focus()

        return this

    getHeader: ->
        @header = new Cruddy.Layout.TabPane.Header model: this if not @header

        return @header

    handleValidationError: ->
        @header?.incrementErrors()

        super

class Cruddy.Layout.TabPane.Header extends Cruddy.View
    tagName: "li"

    events:
        "shown.bs.tab": ->
            after_break => @model.focus()

            return

    initialize: ->
        @errors = 0

        super

    incrementErrors: ->
        @$badge.text ++@errors

        return this

    resetErrors: ->
        @errors = 0
        @$badge.text ""

        return this

    render: ->
        @$el.html @template()

        @$badge = @$component "badge"

        super

    template: -> """
        <a href="##{ @model.cid }" role="tab" data-toggle="tab">
            #{ @model.title }
            <span class="badge" id="#{ @componentId "badge" }"></span>
        </a>"""

    activate: ->
        @$("a").tab("show")

        return this