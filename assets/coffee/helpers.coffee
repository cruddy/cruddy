humanize = (id) => id.replace(/_-/, " ")

# Get url for an entity action
entity_url = (id, extra) ->
    url = Cruddy.baseUrl + "/" + id;
    url += "/" + extra if extra

    url

# Call callback after browser has taken a breath
after_break = (callback) -> setTimeout callback, 50

# Get thumb link
thumb = (src, width, height) ->
    url = "#{ Cruddy.thumbUrl }?src=#{ encodeURIComponent(src) }"
    url += "&amp;width=#{ width }" if width
    url += "&amp;height=#{ height }" if height

    url

b_icon = (icon) -> "<span class='glyphicon glyphicon-#{ icon }'></span>"

b_btn = (label, icon = null, className = "btn-default", type = 'button') ->
    label = b_icon(icon) + ' ' + label if icon
    className = "btn-" + className.join(" btn-") if _.isArray className

    "<button type='#{ type }' class='btn #{ className }'>#{ label.trim() }</button>"

render_divider = -> """<li class="divider"></li>"""

render_presentation_action = (url, title) -> """
        <li><a href="#{ url }" target="_blank">#{ title }</a></li>
    """

render_presentation_actions = (items) ->
    html = ""
    html += render_presentation_action(href, title) for title, href of items

    return html

class_if = (bool, className) -> if bool then className else ""

get = (path, obj = window) ->
    return obj if _.isEmpty path

    for key in path.split "."

        return unless key of obj

        obj = obj[key]

    return obj

class Alert extends Backbone.View
    tagName: "span"
    className: "alert"

    initialize: (options) ->
        @$el.addClass @className + "-" + options.type ? "info"
        @$el.text options.message

        setTimeout (=> @remove()), options.timeout if options.timeout?

        this

    render: ->
        after_break => @$el.addClass "show"

        this

    remove: ->
        @$el.one TRANSITIONEND, => super

        @$el.removeClass "show"

        this