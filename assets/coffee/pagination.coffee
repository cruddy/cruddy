class Pagination extends Backbone.View
    tagName: "ul"
    className: "pager"

    events:
        "click a": "navigate"

    initialize: (options) ->
        router = Cruddy.router

        @listenTo @model, "data", @render
        @listenTo @model, "request", @disable

        $(document).on "keydown.pagination", $.proxy this, "hotkeys"

        this

    hotkeys: (e) ->
        if e.ctrlKey and e.keyCode is 37
            @previous()

            return false

        if e.ctrlKey and e.keyCode is 39
            @next()

            return false

        this

    page: (n) ->
        @model.set "current_page", n if n > 0 and n <= @model.get "last_page"

        this

    previous: -> @page @model.get("current_page") - 1

    next: -> @page @model.get("current_page") + 1

    navigate: (e) ->
        e.preventDefault()

        @page $(e.target).data "page" if !@model.inProgress()

    disable: ->
        @$("a").addClass "disabled"

        this

    render: ->
        last = @model.get("last_page")

        @$el.toggle last? and last > 1

        @$el.html @template @model.get("current_page"), last if last > 1

        this

    template: (current, last) ->
        html = ""
        html += @renderLink current - 1, "&larr; #{ Cruddy.lang.prev }", "previous" + if current > 1 then "" else " disabled"
        html += @renderStats() if @model.get("total")?
        html += @renderLink current + 1, "#{ Cruddy.lang.next } &rarr;", "next" + if current < last then "" else " disabled"

        html

    renderStats: -> """<li class="stats"><span>#{ @model.get "from" } - #{ @model.get "to" } / #{ @model.get "total" }</span></li>"""

    renderLink: (page, label, className = "") -> """<li class="#{ className }"><a href="#" data-page="#{ page }">#{ label }</a></li>"""
