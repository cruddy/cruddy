class Cruddy.Columns.ViewButton extends Cruddy.Columns.Base

    id: "__viewButton"

    getHeader: -> ""

    getClass: -> "col__view-button col__auto-hide"

    canOrder: -> false

    render: (model) -> @wrapWithActions model, """
        <a onclick="Cruddy.app.entityView.displayForm('#{ model.meta.id }', this);return false;" class="btn btn-default btn-view btn-xs auto-hide-target" href="#{ @entity.link model.meta.id }">
            #{ b_icon("pencil") }
        </a>
    """

    wrapWithActions: (item, html) ->
        return html if _.isEmpty(item.meta.links) and _.isEmpty(item.meta.actions)

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

    renderActions: (model) ->
        html = """<ul class="dropdown-menu" role="menu">"""

        unless noPresentationActions = _.isEmpty model.meta.links
            html += render_presentation_actions model.meta.links

        unless _.isEmpty model.meta.actions
            html += render_divider() unless noPresentationActions
            html += @renderAction action, model for action in model.meta.actions

        html += "</ul>"

        return html

    renderAction: (action, model) -> """
        <li class="#{ if action.disabled then "disabled" else "" }">
            <a onclick="Cruddy.app.entityView.executeCustomAction('#{ action.id }', '#{ model.meta.id }', this);return false;" href="javascript:void;">
                #{ _.escape action.title }
            </a>
        </li>
    """