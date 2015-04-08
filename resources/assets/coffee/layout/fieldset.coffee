class Cruddy.Layout.FieldSet extends Cruddy.Layout.BaseFieldContainer
    tagName: "fieldset"

    render: ->
        @$el.html @template()

        @$container = @$component "body"

        super

    template: ->
        html = if @title then "<legend>" + _.escape(@title) + "</legend>" else ""

        return html + "<div id='" + @componentId("body") + "'></div>"