class Cruddy.Columns.ViewButton extends Cruddy.Columns.Base

    id: "__viewButton"

    getHeader: -> ""

    getClass: -> "col__view-button col__auto-hide"

    canOrder: -> false

    render: (item) -> @wrapWithActions item, """
        <a href="#{ @entity.link item.meta.id }" class="btn btn-default btn-view btn-xs auto-hide-target">
            #{ b_icon("pencil") }
        </a>
    """

    wrapWithActions: (item, html) ->
        return html unless item.meta.externalUrl

        html = """<div class="btn-group btn-group-xs auto-hide-target">""" + html
        html += @dropdownToggleTemplate()
        html += @renderActions item
        html += "</div>"

        return html

    dropdownToggleTemplate: -> """
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            <span class="caret"></span>
        </button>
    """

    renderActions: (item) ->
        html = """<ul class="dropdown-menu" role="menu">"""

        html += @renderExternalLink item.meta.externalUrl if item.meta.externalUrl

        html += "</ul>"

        return html

    renderExternalLink: (url) -> """
        <li><a href="#{ url }" target="_blank">#{ Cruddy.lang.view_external }</a></li>
    """