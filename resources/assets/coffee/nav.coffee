$(document).on "started.cruddy", (e, app) ->

    $navbar = $ ".navbar"

    changeEntity = (entity) ->
        $navbar.find(".navbar-nav li.active").removeClass("active")

        return unless entity?

        $el = $navbar.find(".navbar-nav [data-entity=#{ entity.id }]")

        $el.addClass "active"

        $el.find(".badge").fadeOut()

    app.on "change:entity", (app, entity) ->
        changeEntity entity

        return

    changeEntity app.get "entity"

    return