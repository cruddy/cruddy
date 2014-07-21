class Cruddy.Layout.Fieldset extends Cruddy.Layout.BaseFieldContainer
    tagName: "fieldset"

    render: ->
        @$el.html @template()

        @$container = @$component "body"

        super

    template: ->
        html = if @title then "<legend>" + escape(@title) + "</legend>" else ""

        return html + "<div id='" + @componentId("body") + "'></div>"