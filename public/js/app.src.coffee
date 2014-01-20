Cruddy = window.Cruddy || {}

Cruddy.baseUrl = Cruddy.root + "/" + Cruddy.uri

API_URL = "/backend/api/v1"
TRANSITIONEND = "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd"
moment.lang Cruddy.locale ? "en"

Backbone.emulateHTTP = true
Backbone.emulateJSON = true

#$(document).ajaxError (e, xhr, options) =>
#    location.href = "/login" if xhr.status is 403 and not options.dontRedirect

$(document)
    .ajaxSend((e, xhr, options) -> Cruddy.app.startLoading() if options.displayLoading)
    .ajaxComplete((e, xhr, options) -> Cruddy.app.doneLoading() if options.displayLoading)

$.extend $.fancybox.defaults,
    openEffect: "elastic"
humanize = (id) => id.replace(/_-/, " ")

entity_url = (id, extra) ->
    url = Cruddy.baseUrl + "/api/v1/entity/" + id;
    url += "/" + extra if extra

    url

after_break = (callback) -> setTimeout callback, 50

thumb = (src, width, height) ->
    url = "#{ Cruddy.baseUrl }/thumb?src=#{ encodeURIComponent(src) }"
    url += "&amp;width=#{ width }" if width
    url += "&amp;height=#{ height }" if height

    url

class Alert extends Backbone.View
    tagName: "span"
    className: "alert"

    initialize: (options) ->
        @$el.addClass @className + "-" + options.type ? "info"
        @$el.text options.message

        setTimeout (=> @remove()), options.timeout if options.timeout?

        this
class AdvFormData
    constructor: (data) ->
        @original = new FormData
        @append data if data?

    append: (name, value) ->
        if value is undefined
            value = name
            name = null

        return @original.append name, value if value instanceof File or value instanceof Blob

        if _.isArray value
            return @append name, "" if _.isEmpty value

            @append @key(name, key), _value for _value, key in value

            return

        if _.isObject value
            @append @key(name, key), _value for key, _value of value

            return

        @original.append name, @process value

    process: (value) ->
        return "" if value is null
        return 1 if value is yes
        return 0 if value is no

        value

    key: (outer, inner) -> if outer then "#{ outer }[#{ inner }]" else inner
class Factory
    create: (name, options) ->
        constructor = @[name]
        return new constructor options if constructor?

        console.error "Failed to resolve #{ name }."

        null
class Attribute extends Backbone.Model
class DataSource extends Backbone.Model
    defaults:
        data: []
        search: ""

    initialize: (attributes, options) ->
        @entity = options.entity
        @columns = options.columns if options.columns?
        @filter = options.filter if options.filter?

        @options =
            url: @entity.url()
            dataType: "json"
            type: "get"
            displayLoading: yes

            success: (resp) =>
                @_hold = true
                @set resp.data
                @_hold = false

                @trigger "data", this, resp.data.data

            error: (xhr) => @trigger "error", this, xhr

        @listenTo @filter, "change", @fetch if @filter?

        @on "change", => @fetch() unless @_hold
        @on "change:search", => @set current_page: 1, silent: yes

    hasData: -> not _.isEmpty @get "data"

    hasMore: -> @get("current_page") < @get("last_page")

    isFull: -> !@hasMore()

    inProgress: -> @request?

    fetch: ->
        @request.abort() if @request?

        @options.data = @data()

        @request = $.ajax @options

        @request.always => @request = null

        @trigger "request", this, @request

        @request

    more: ->
        return if @isFull()

        @set current_page: @get("current_page") + 1, silent: yes

        @fetch()

    data: ->
        data = {
            order_by: @get "order_by"
            order_dir: @get "order_dir"
            page: @get "current_page"
            per_page: @get "per_page"
            q: @get "search"
        }

        filters = @filterData()

        data.filters = filters unless _.isEmpty filters
        data.columns = @columns.join "," if @columns?

        data

    filterData: ->
        return null unless @filter?

        data = {}

        for key, value of @filter.attributes
            data[key] = value unless value is null or value is ""

        data
class SearchDataSource extends Backbone.Model
    defaults:
        search: ""

    initialize: (attributes, options) ->
        keyName = options.primaryKey ? "id"
        valueName = options.primaryColumn

        @options =
            url: options.url
            type: "get"
            dataType: "json"

            data:
                page: null
                q: ""
                columns: keyName + "," + valueName

            success: (resp) =>
                resp = resp.data

                @data.push { id: item[keyName], title: item[valueName] } for item in resp.data

                @page = resp.current_page
                @more = resp.current_page < resp.last_page
                @request = null

                @trigger "data", this, @data

                this

            error: (xhr) =>
                @request = null
                @trigger "error", this, xhr

                this

        $.extend @options, options.ajaxOptions if options.ajaxOptions?

        @reset()

        @on "change:search", => @reset().next()

        this

    reset: ->
        @data = []
        @page = null
        @more = yes

        this

    fetch: (q, page) ->
        @request.abort() if @request?

        $.extend @options.data, { page: page, q: q }

        @trigger "request", this, @request = $.ajax @options

        @request

    next: ->
        if @more
            page = if @page? then @page + 1 else 1

            @fetch @get("search"), page

        this

    inProgress: -> @request?
class Pagination extends Backbone.View
    tagName: "ul"
    className: "pager"

    events:
        "click a": "navigate"

    initialize: (options) ->
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
        html += @renderLink current - 1, "&larr; Назад", "previous" + if current > 1 then "" else " disabled"
        html += @renderStats() if @model.get("total")?
        html += @renderLink current + 1, "Вперед &rarr;", "next" + if current < last then "" else " disabled"

        html

    renderStats: -> """<li class="stats"><span>#{ @model.get "from" } - #{ @model.get "to" } / #{ @model.get "total" }</span></li>"""

    renderLink: (page, label, className = "") -> """<li class="#{ className }"><a href="#" data-page="#{ page }">#{ label }</a></li>"""

class DataGrid extends Backbone.View
    tagName: "table"
    className: "table table-hover table-condensed data-grid"

    events: {
        "click .sortable": "setOrder"
        "click .item": "navigate"
    }

    constructor: (options) ->
        @className += " data-grid-" + options.model.entity.id

        super

    initialize: (options) ->
        @entity = @model.entity
        @columns = @entity.columns.models.filter (col) -> col.get "visible"

        @listenTo @model, "data", @updateData
        @listenTo @model, "change:order_by change:order_dir", @onOrderChange

        @listenTo @entity, "change:instance", @onInstanceChange

    onOrderChange: ->
        orderBy = @model.get "order_by"
        orderDir = @model.get "order_dir"

        if @orderBy? and orderBy isnt @orderBy
            @$("#col-#{ @orderBy } .sortable").removeClass "asc desc"

        @orderBy = orderBy
        @$("#col-#{ @orderBy } .sortable").removeClass("asc desc").addClass orderDir

        this

    onInstanceChange: (entity, curr) ->
        prev = entity.previous "instance"

        if prev?
            @$("#item-#{ prev.id }").removeClass "active"
            prev.off null, null, this

        if curr?
            @$("#item-#{ curr.id }").addClass "active"
            curr.on "sync destroy", (=> @model.fetch()), this

        this

    setOrder: (e) ->
        orderBy = $(e.target).data "id"
        orderDir = @model.get "order_dir"

        if orderBy is @model.get "order_by"
            orderDir = if orderDir == 'asc' then 'desc' else 'asc'
        else
            orderDir = @entity.columns.get(orderBy).get "order_dir"

        @model.set { order_by: orderBy, order_dir: orderDir }

        this

    navigate: (e) ->
        Cruddy.router.navigate @entity.link($(e.currentTarget).data "id"), { trigger: true }

        this

    updateData: (datasource, data) ->
        @$(".items").replaceWith @renderBody @columns, data

        this

    render: ->
        data = @model.get "data"

        @$el.html @renderHead(@columns) + @renderBody(@columns, data)

        @onOrderChange @model

        this

    renderHead: (columns) ->
        html = "<thead><tr>"
        html += @renderHeadCell col for col in columns
        html += "</tr></thead>"

    renderHeadCell: (col) ->
        """<th class="#{ col.getClass() }" id="col-#{ col.id }">#{ col.renderHeadCell() }</th>"""

    renderBody: (columns, data) ->
        html = "<tbody class=\"items\">"

        if data? and data.length
            html += @renderRow columns, item for item in data
        else
            html += """<tr><td class="no-items" colspan="#{ columns.length }">Ничего не найдено</td></tr>"""

        html += "</tbody>"

    renderRow: (columns, item) ->
        instance = @entity.get "instance"
        active = if instance? and item.id == instance.id then "active" else ""

        html = "<tr class=\"item #{ active }\" id=\"item-#{ item.id }\" data-id=\"#{ item.id }\">"
        html += @renderCell col, item for col in columns
        html += "</tr>"

    renderCell: (col, item) ->
        """<td class="#{ col.getClass() }">#{ col.renderCell item[col.id] }</td>"""
# Displays a list of entity's fields
class FieldList extends Backbone.View
    className: "field-list"

    initialize: ->
        @listenTo @model.entity.fields, "add remove", @render

        this

    focus: ->
        @primary?.focus()

        this

    render: ->
        @$el.empty()

        @$el.append field.el for field in @createFields()

        this

    createFields: ->
        @dispose()

        @fields = (field.createView(@model).render() for field in @model.entity.fields.models)
        @primary = null

        for view in @fields when view.field.isEditable @model
            @primary = view
            break

        @fields

    dispose: ->
        field.remove() for field in @fields if @fields?

        this

    stopListening: ->
        @dispose()

        super
class FilterList extends Backbone.View
    className: "filter-list"

    tagName: "fieldset"

    initialize: (options) ->
        @entity = options.entity

        this

    render: ->
        @dispose()

        @$el.html @template()
        @items = @$ ".filter-list-container"

        @filters = []
        for col in @entity.columns.models when not col.get("searchable") and col.get("filterable")
            if input = col.createFilterInput @model
                @filters.push input
                @items.append input.render().el
                input.$el.wrap("""<div class="form-group filter #{ col.getClass() }"><div class="input-wrap"></div></div>""").parent().before "<label>#{ col.get "title" }</label>"

        this

    template: -> """<div class="filter-list-container"></div>"""

    dispose: ->
        filter.remove() for filter in @filters if @filters?

        this

    remove: ->
        @dispose()

        super
# Base class for input that will be bound to a model's attribute.
class BaseInput extends Backbone.View
    constructor: (options) ->
        @key = options.key

        super

    initialize: ->
        @listenTo @model, "change:" + @key, @applyChanges

        this

    # Apply changes when model's attribute changed.
    applyChanges: (model, data) -> this

    render: -> @applyChanges @model, @model.get @key

    focus: -> this
# Renders formatted text and doesn't have any editing features.
class StaticInput extends BaseInput
    tagName: "p"
    className: "form-control-static"

    initialize: (options) ->
        @formatter = options.formatter if options.formatter?

        super

    applyChanges: (model, data) -> @render()

    render: ->
        value = @model.get @key
        value = @formatter.format value if @formatter?

        @$el.html value

        this
# Renders an <input> value of which is bound to a model's attribute.
class TextInput extends BaseInput
    tagName: "input"

    events:
        "change": "change"
        "keydown": "keydown"

    constructor: (options) ->
        options.className ?= "form-control"
        options.className += " input-#{ options.size ? "sm" }"

        super

    keydown: (e) ->
        # Ctrl + Enter
        return @change() if e.ctrlKey and e.keyCode is 13

        this

    disable: ->
        @$el.prop "disabled", yes

        this

    enable: ->
        @$el.prop "disabled", no

        this

    change: ->
        @model.set @key, @el.value

        this

    applyChanges: (model, data) ->
        @$el.val data

        this

    focus: ->
        @el.focus()

        this

# Renders a <textarea> input.
class Textarea extends TextInput
    tagName: "textarea"
# Renders a checkbox
class Checkbox extends BaseInput
    tagName: "label"
    label: ""

    events:
        "change": "change"

    initialize: (options) ->
        @label = options.label if options.label?

        super

    change: ->
        @model.set @key, @input.prop "checked"

        this

    applyChanges: (model, value) ->
        @input.prop "checked", value

        this

    render: ->
        @input = $ "<input>", { type: "checkbox", checked: @model.get @key }
        @$el.append @input
        @$el.append @label if @label?

        this
class BooleanInput extends BaseInput
    tripleState: false

    events:
        "click .btn": "check"

    initialize: (options) ->
        @tripleState = options.tripleState if options.tripleState?

        super

    check: (e) ->
        value = !!$(e.target).data "value"
        currentValue = @model.get @key

        value = null if value == currentValue and @tripleState

        @model.set @key, value

        this

    applyChanges: (model, value) ->
        value = switch value
            when yes then 0
            when no then 1
            else null

        @values.removeClass("active")
        @values.eq(value).addClass "active" if value?

        this

    render: ->
        @$el.html @template()

        @values = @$ ".btn"

        super

    template: ->
        """
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-info" data-value="1">да</button>
            <button type="button" class="btn btn-default" data-value="0">нет</button>
        </div>
        """

    itemTemplate: (label, value) -> """
        <label class="radio-inline">
            <input type="radio" name="#{ @cid }" value="#{ value }">
            #{ label }
        </label>
        """
class EntityDropdown extends BaseInput
    className: "entity-dropdown"

    events:
        "click .btn-remove": "removeItem"
        "keydown [type=search]": "searchKeydown"
        "show.bs.dropdown": "renderDropdown"

        "shown.bs.dropdown": ->
            after_break => @selector.focus()

            this

        "hidden.bs.dropdown": ->
            @opened = no

            this

    mutiple: false
    reference: null

    initialize: (options) ->
        @multiple = options.multiple if options.multiple?
        @reference = options.reference if options.reference?
        @active = false

        super

    removeItem: (e) ->
        if @multiple
            i = $(e.currentTarget).data "key"
            value = _.clone @model.get(@key)
            value.splice i, 1
        else
            value = null

        @model.set @key, value

        this

    searchKeydown: (e) ->
        if (e.keyCode is 27)
            @$el.dropdown "toggle"
            return false

    renderDropdown: ->
        @opened = yes

        return @toggleOpenDirection() if @selector?

        @selector = new EntitySelector
            model: @model
            key: @key
            multiple: @multiple
            reference: @reference

        @selector.render().entity.done => @$el.append @selector.el

        @toggleOpenDirection()

    toggleOpenDirection: ->
        return if not @opened

        wnd = $(window)
        space = wnd.height() - @$el.offset().top - wnd.scrollTop() - @$el.parent(".field-list").scrollTop()

        targetClass = if space > 292 then "open-down" else "open-up"

        @$el.removeClass("open-up open-down").addClass targetClass if not @$el.hasClass targetClass

        this

    applyChanges: (model, value) ->
        if @multiple
            @renderItems()
        else
            @updateItem()
            @$el.removeClass "open"

        @toggleOpenDirection()

        this

    render: ->
        @dispose()

        if @multiple then @renderMultiple() else @renderSingle()

        @$el.attr "id", @cid

        this

    renderMultiple: ->
        @$el.append @items = $ "<div>", class: "items"

        @$el.append """
            <button type="button" class="btn btn-default btn-sm btn-block dropdown-toggle" data-toggle="dropdown" data-target="##{ @cid }">
                Выбрать
                <span class="caret"></span>
            </button>
            """

        @renderItems()

    renderItems: ->
        html = ""
        html += @itemTemplate value.title, key for value, key in @model.get @key
        @items.html html
        @items.toggleClass "has-items", html isnt ""

        this

    renderSingle: ->
        @$el.html @itemTemplate "", "0"

        @itemTitle = @$ ".form-control"
        @itemDelete = @$ ".btn-remove"

        @updateItem()

    updateItem: ->
        value = @model.get @key
        @itemTitle.val if value then value.title else "Не выбрано"
        @itemDelete.toggle !!value

        this

    itemTemplate: (value, key = null) ->
        html = """
        <div class="input-group input-group-sm ed-item">
            <input type="text" class="form-control" #{ if not @multiple or key is null then "data-toggle='dropdown' data-target='##{ @cid }'" else "tab-index='-1'"} value="#{ _.escape value }" readonly>
            <div class="input-group-btn">
        """

        if not @multiple or key isnt null
            html += """
                <button type="button" class="btn btn-default btn-remove" data-key="#{ key }" tabindex="-1">
                    <span class="glyphicon glyphicon-remove"></span>
                </button>
                """

        if not @multiple or key is null
            html += """
                <button type="button" class="btn btn-default btn-dropdown dropdown-toggle" data-toggle="dropdown" data-target="##{ @cid }" tab-index="1">
                    <span class="glyphicon glyphicon-search"></span>
                </button>
                """

        html += "</div></div>"

    dispose: ->
        @selector?.remove()

        this

    remove: ->
        @dispose()

        super
class EntitySelector extends BaseInput
    className: "entity-selector"

    events:
        "click .item": "check"
        "click .more": "more"
        "click [type=search]": -> false

    initialize: (options) ->
        super

        @filter = options.filter ? false
        @multiple = options.multiple ? false
        @search = options.search ? true

        @data = []
        @buildSelected @model.get @key

        @entity = Cruddy.app.entity(options.reference)

        @entity.done (entity) =>
            @primaryKey = "id"
            @primaryColumn = entity.get "primary_column"

            @dataSource = entity.search()

            @listenTo @dataSource, "request", @loading
            @listenTo @dataSource, "data",    @renderItems
            @listenTo @dataSource, "error",   @displayError

        @entity.fail $.proxy this, "displayError"

        this

    checkForMore: ->
        @more() if @moreElement? and @items.parent().height() + 50 > @moreElement.position().top

        this

    check: (e) ->
        id = $(e.target).data "id"
        uncheck = id of @selected
        item = _.find @dataSource.data, (item) -> item.id.toString() == id

        if @multiple
            if uncheck
                value = _.filter @model.get(@key), (item) -> item.id.toString() != id
            else
                value = _.clone @model.get(@key)
                value.push item
        else
            value = item

        @model.set @key, value

        false

    more: ->
        return if not @dataSource or @dataSource.inProgress()

        @dataSource.next()

        false

    applyChanges: (model, data) ->
        @buildSelected data
        @renderItems()

    buildSelected: (data) ->
        @selected = {}

        if @multiple
            @selected[item.id] = yes for item in data
        else
            @selected[data.id] = yes if data?

        this

    displayError: (xhr) ->
        return if xhr.status isnt 403

        @$el.html "<span class=error>Ошибка доступа</span>"

        this

    loading: ->
        @moreElement?.addClass "loading"

        this

    renderItems: ->
        @moreElement = null

        html = ""

        if @dataSource.data.length or @dataSource.more
            html += @renderItem item for item in @dataSource.data

            html += """<li class="more #{ if @dataSource.inProgress() then "loading" else "" }">еще</li>""" if @dataSource.more
        else
            html += "<li class='empty'>нет результатов</li>"

        @items.html html

        if @dataSource.more
            @moreElement = @items.children ".more"
            @checkForMore()

        this

    renderItem: (item) ->
        className = if item.id of @selected then "selected" else ""

        """<li class="item #{ className }" data-id="#{ item.id }">#{ item.title }</li>"""

    render: ->
        @dispose()

        @$el.html @template()

        @items = @$ ".items"

        @entity.done =>
            @renderItems()

            @items.parent().on "scroll", $.proxy this, "checkForMore"

            @renderSearch()

        this

    renderSearch: ->
        @searchInput = new SearchInput
            model: @dataSource
            key: "search"

        @$el.prepend @searchInput.render().el

        @searchInput.$el.wrap "<div class=search-input-container></div>"

        this

    template: -> """<div class="items-container"><ul class="items"><li class="more loading"></li></ul></div>"""

    focus: ->
        @searchInput?.focus() or @entity.done => @searchInput.focus()

        this

    dispose: ->
        @searchInput?.remove()

        this

    remove: ->
        @dispose()

        super

class FileList extends BaseInput
    className: "file-list"

    events:
        "change [type=file]": "appendFiles"
        "click .action-delete": "deleteFile"

    initialize: (options) ->
        @multiple = options.multiple ? false
        @formatter = options.formatter ? format: (value) -> if value instanceof File then value.name else value
        @accepts = options.accepts ? ""

        super

    deleteFile: (e) ->
        if @multiple
            value = _.clone @model.get @key
            value.splice $(e.currentTarget).data("index"), 1
        else
            value = ''

        @model.set @key, value

        false

    appendFiles: (e) ->
        return if e.target.files.length is 0

        if @multiple
            value = _.clone @model.get @key
            value.push file for file in e.target.files
        else
            value = e.target.files[0]

        @model.set @key, value

        this

    applyChanges: -> @render()

    render: ->
        value = @model.get @key
        html = ""

        if @multiple then html += @renderItem item, i for item, i in value else html += @renderItem value if value

        html = @wrapItems html if html

        html += @renderInput if @multiple then "<span class='glyphicon glyphicon-plus'></span> Добавить" else "Выбрать"

        @$el.html html

        this

    wrapItems: (html) -> """<ul class="list-group">#{ html }</ul>"""

    renderInput: (label) ->
        """
        <div class="btn btn-sm btn-default file-list-input-wrap">
            <input type="file" accept="#{ @accepts } "#{ "multiple" if @multiple }>
            #{ label }
        </div>
        """

    renderItem: (item, i = 0) ->
        label = @formatter.format item

        """
        <li class="list-group-item">
            <a href="#" class="action-delete pull-right" data-index="#{ i }"><span class="glyphicon glyphicon-remove"></span></a>

            #{ label }
        </li>
        """

class ImageList extends FileList
    className: "image-list"

    constructor: ->
        @readers = []

        super

    initialize: (options) ->
        @width = options.width ? 0
        @height = options.height ? 80

        super

    render: ->
        super

        reader.readAsDataURL reader.item for reader in @readers
        @readers = []

        @$(".fancybox").fancybox();

        this

    wrapItems: (html) -> """<ul class="image-group">#{ html }</ul>"""

    renderItem: (item, i = 0) ->
        """
        <li class="image-group-item">
            #{ @renderImage item, i }
            <a href="#" class="action-delete" data-index="#{ i }"><span class="glyphicon glyphicon-remove"></span></a>
        </li>
        """

    renderImage: (item, i = 0) ->
        id = @key + i

        if item instanceof File
            image = item.data or ""
            @readers.push @createPreviewLoader item, id if not item.data?
        else
            image = thumb item, @width, @height

        """
        <a href="#{ if item instanceof File then item.data or "#" else item }" class="fancybox">
            <img src="#{ image }" id="#{ id }">
        </a>
        """

    createPreviewLoader: (item, id) ->
        reader = new FileReader
        reader.item = item
        reader.onload = (e) ->
            e.target.item.data = e.target.result
            $("#" + id).attr("src", e.target.result).parent().attr "href", e.target.result

        reader
# Search input implements "change when type" and also allows to clear text with Esc
class SearchInput extends TextInput

    attributes:
        type: "search"
        placeholder: "поиск"

    scheduleChange: ->
        clearTimeout @timeout if @timeout?
        @timeout = setTimeout (=> @change()), 300

        this

    keydown: (e) ->

        # Backspace
        if e.keyCode is 8
            @model.set @key, ""
            return false

        @scheduleChange()

        super
class SlugInput extends Backbone.View
    events:
        "click .btn": "toggleSyncing"

    constructor: (options) ->
        @input = new TextInput _.clone options

        options.className ?= "input-group"
        options.className += " input-group-#{ options.size ? "sm" }"

        delete options.attributes if options.attributes?

        super

    initialize: (options) ->
        chars = options.chars ? "a-z0-9\-_"

        @regexp = new RegExp "[^#{ chars }]+", "g"
        @separator = options.separator ? "-"

        @key = options.key
        @ref = options.ref if options.ref?

        super

    toggleSyncing: ->
        if @syncButton.hasClass "active" then @unlink() else @link()

        this

    link: ->
        return if not @ref

        @listenTo @model, "change:" + @ref, @sync
        @syncButton.addClass "active"
        @input.disable()

        @sync()

    unlink: ->
        @stopListening @model, null, @sync if @ref?
        @syncButton.removeClass "active"
        @input.enable()

        this

    linkable: ->
        refValue = @convert @model.get @ref
        refValue == @model.get @key

    convert: (value) -> if value then value.toLocaleLowerCase().replace(/\s+/g, @separator).replace(@regexp, "") else value

    change: ->
        @unlink()

        @$el.val @convert @$el.val()

        super

    sync: ->
        @model.set @key, @convert @model.get @ref

        this

    render: ->
        @$el.html @template()
        @$el.prepend @input.render().el

        if @ref?
            @syncButton = @$ ".btn"
            @link() if @linkable()

        this

    template: ->
        return "" if not @ref?

        """
        <div class="input-group-btn">
            <button type="button" tabindex="-1" class="btn btn-default" title="Связать с полем #{ @model.entity.fields.get(@ref).get "label" }"><span class="glyphicon glyphicon-link"></span></button>
        </div>
        """
class SelectInput extends TextInput
    tagName: "select"

    initialize: (options) ->
        @items = options.items ? {}
        @prompt = options.prompt ? null

        super

    applyChanges: (model, data) ->
        @$("[value='#{ data }']").prop "selected", yes

        this

    render: ->
        @$el.html @template()

        super

    template: ->
        html = ""
        html += @optionTemplate "", @prompt ? ""
        html += @optionTemplate key, value for key, value of @items
        html

    optionTemplate: (value, title) ->
        """<option value="#{ _.escape value }">#{ _.escape title }</option>"""
Cruddy.fields = new Factory

class FieldView extends Backbone.View
    className: "field"

    constructor: (options) ->
        @inputId = options.model.entity.id + "_" + options.field.id

        base = " " + @className + "-"
        classes = [ options.field.attributes.type, options.field.id, @inputId ]
        @className += base + classes.join base

        @className += " required" if options.field.get "required"

        super

    initialize: (options) ->
        @field = options.field

        @listenTo @field, "change:visible",     @toggleVisibility
        @listenTo @field, "change:editable",    @render

        @listenTo @model, "sync",       @render
        @listenTo @model, "request",    @hideError
        @listenTo @model, "invalid",    @showError

        this

    hideError: ->
        @error.hide()
        @inputHolder.removeClass "has-error"

    showError: (model, errors) ->
        error = errors[@field.get "id"]

        if error
            @inputHolder.addClass "has-error"
            @error.text(error).show()

    # Render a field.
    render: ->
        @dispose()

        @$el.html @template()

        @inputHolder = @$ ".input-holder"

        @input = @field.createInput @model
        @inputHolder.append @input.render().el if @input?

        @inputHolder.append @error = $ @errorTemplate()

        @toggleVisibility()

        this

    helpTemplate: ->
        help = @field.get "help"
        if help then """<span class="glyphicon glyphicon-question-sign field-help" title="#{ help }"></span>""" else ""

    errorTemplate: -> """<span class="help-block error"></span>"""

    label: (label) ->
        label ?= @field.get "label"
        """<label for="#{ @inputId }">#{ label }</label>"""

    # The default template that is shown when field is editable.
    template: ->
        """
        #{ @helpTemplate() }
        <div class="form-group input-holder">
            #{ @label() }
        </div>
        """

    # Get whether this field view is visible.
    isVisible: -> @field.get("visible") and (@field.get("editable") and @field.get("updateable") or not @model.isNew())

    toggleVisibility: -> @$el.toggle @isVisible()

    # Focus the input that this field view holds.
    focus: ->
        @input.focus() if @input?

        this

    dispose: ->
        @input?.remove()

        this

    stopListening: ->
        @dispose()

        super

class Field extends Attribute
    viewConstructor: FieldView

    createView: (model) -> new @viewConstructor { model: model, field: this }

    createInput: (model) ->
        input = @createEditableInput model if @isEditable model

        if input? then input else new StaticInput { model: model, key: @id, formatter: this }

    createEditableInput: (model) -> null

    format: (value) -> if value then value else "n/a"

    isEditable: (model) -> @get("editable") and (@get("updateable") or not model.isNew()) and model.isSaveable()
class Cruddy.fields.Input extends Field
    createEditableInput: (model) ->
        attributes = placeholder: @get "label"
        type = @get "input_type"

        if type is "textarea"
            attributes.rows = @get "rows"

            new Textarea
                model: model
                key: @id
                attributes: attributes
        else
            attributes.type = type

            new TextInput
                model: model
                key: @id
                attributes: attributes

    format: (value) -> if @get("input_type") is "textarea" then "<pre>#{ super }</pre>" else super

    createFilterInput: (model, column) ->
        new TextInput
                model: model
                key: @id
                attributes:
                    placeholder: @get "label"
# DATE AND TIME FIELD TYPE

###
class Cruddy.fields.DateTimeView extends Cruddy.fields.InputView
    format: (value) -> moment.unix(value).format @field.get "format"
    unformat: (value) -> moment(value, @field.get "format").unix()
###

class Cruddy.fields.DateTime extends Cruddy.fields.Input
    #viewConstructor: Cruddy.fields.DateTimeView

    format: (value) -> if value is null then "никогда" else moment.unix(value).calendar()
class Cruddy.fields.Boolean extends Field
    createEditableInput: (model) -> new BooleanInput { model: model, key: @id }

    createFilterInput: (model) -> new BooleanInput { model: model, key: @id, tripleState: yes }

    format: (value) -> if value then "да" else "нет"
class Cruddy.fields.Relation extends Field
    createEditableInput: (model) ->
        new EntityDropdown
            model: model
            key: @id
            multiple: @get "multiple"
            reference: @get "reference"

    createFilterInput: (model) ->
        new EntityDropdown
            model: model
            key: @id
            reference: @get "reference"

    format: (value) ->
        return "не указано" if _.isEmpty value
        if @attributes.multiple then _.pluck(value, "title").join ", " else value.title
class Cruddy.fields.File extends Field
    createEditableInput: (model) -> new FileList
        model: model
        key: @id
        multiple: @get "multiple"
        accepts: @get "accepts"

    format: (value) -> if value instanceof File then value.name else value
class Cruddy.fields.Image extends Cruddy.fields.File
    createEditableInput: (model) -> new ImageList
        model: model
        key: @id
        width: @get "width"
        height: @get "height"
        multiple: @get "multiple"
        accepts: @get "accepts"

    format: (value) -> if value instanceof File then value.name else value
class Cruddy.fields.Slug extends Field
    createEditableInput: (model) ->
        new SlugInput
            model: model
            key: @id
            chars: @get "chars"
            ref: @get "ref"
            separator: @get "separator"
            attributes:
                placeholder: @get "label"

    createFilterInput: (model, column) ->
        new TextInput
            model: model
            key: @id
            attributes:
                placeholder: @get "label"
class Cruddy.fields.Enum extends Field
    createEditableInput: (model) ->
        new SelectInput
            model: model
            key: @id
            prompt: @get "prompt"
            items: @get "items"

    createFilterInput: (model) ->
        new SelectInput
            model: model
            key: @id
            prompt: "Любое значение"
            items: @get "items"

    format: (value) ->
        items = @get "items"

        if value of items then items[value] else "n/a"
Cruddy.columns = new Factory

class Column extends Attribute
    initialize: (options) ->
        @formatter = Cruddy.formatters.create options.formatter, options.formatterOptions if options.formatter?

        super

    renderHeadCell: ->
        title = @get "title"
        help = @get "help"
        title = "<span class=\"sortable\" data-id=\"#{ @id }\">#{ title }</span>" if @get "sortable"
        if help then "<span class=\"glyphicon glyphicon-question-sign\" title=\"#{ help }\"></span> #{ title }" else title

    renderCell: (value) -> if @formatter? then @formatter.format value else value

    createFilterInput: (model) -> null

    getClass: -> "col-" + @id


class Cruddy.columns.Field extends Column
    initialize: (attributes) ->
        field = attributes.field ? attributes.id
        @field = attributes.entity.fields.get field

        @set "title", @field.get "label" if attributes.title is null

        super

    renderCell: (value) -> if @formatter? then @formatter.format value else @field.format value

    createFilterInput: (model) -> @field.createFilterInput model, this

    getClass: -> super + " col-" + @field.get "type"

class Cruddy.columns.Computed extends Column
    createFilterInput: (model) ->
        new TextInput
            model: model
            key: @id
            attributes:
                placeholder: @get "title"

    getClass: -> super + " col-computed"
Cruddy.formatters = new Factory

class BaseFormatter
    defaultOptions: {}

    constructor: (options = {}) ->
        @options = $.extend {}, @defaultOptions, options

        this

    format: (value) -> value
class Cruddy.formatters.Image extends BaseFormatter
    defaultOptions:
        width: 40
        height: 40

    format: (value) ->
        return "" if _.isEmpty value
        value = value[0] if _.isArray value

        """
        <img src="#{ thumb value, @options.width, @options.height }" width="#{ @options.width or @defaultOptions.width }" height="#{ @options.height or @defaultOptions.height }" alt="#{ _.escape value }">
        """
class Cruddy.formatters.Plain extends BaseFormatter
    format: (value) -> value
Cruddy.related = new Factory

class Related extends Backbone.Model
    resolve: ->
        return @resolver if @resolver?

        @resolver = Cruddy.app.entity @get "related"
        @resolver.done (entity) => @related = entity

class Cruddy.related.One extends Related
    associate: (parent, child) ->
        child.set @get("foreign_key"), parent.id

        this

class Cruddy.related.MorphOne extends Cruddy.related.One
    associate: (parent, child) ->
        child.set @get("morph_type"), @get("morph_class")

        super
class Entity extends Backbone.Model

    initialize: (attributes, options) ->
        @fields = @createCollection Cruddy.fields, attributes.fields
        @columns = @createCollection Cruddy.columns, attributes.columns
        @related = @createCollection Cruddy.related, attributes.related

        @set "label", humanize @id if @get("label") is null

    createCollection: (factory, items) ->
        data = []
        for options in items
            options.entity = this
            instance = factory.create options.class, options
            data.push instance if instance?

        new Backbone.Collection data

    # Create a datasource that will require specified columns and can be filtered
    # by specified filters
    createDataSource: (columns = null) ->
        data = { order_by: @get("order_by") || @get("primary_column") }
        data.order_dir = if data.order_dir? then @columns.get(data.order_by).get "order_dir" else "asc"

        new DataSource data, { entity: this, columns: columns, filter: new Backbone.Model }

    # Create filters for specified columns
    createFilters: (columns = @columns) ->
        filters = (col.createFilter() for col in columns.models when col.get "filterable")

        new Backbone.Collection filters

    # Create an instance for this entity
    createInstance: (attributes = {}, relatedData = {}) ->
        related = {}
        related[item.id] = item.related.createInstance(relatedData[item.id]) for item in @related.models

        new EntityInstance _.extend({}, @get("defaults"), attributes), { entity: this, related: related }

    search: ->
        return @searchDataSource if @searchDataSource?

        @searchDataSource = new SearchDataSource {},
            url: @url "search"
            primaryColumn: @get "primary_column"

        @searchDataSource.next()

    # Load a model
    load: (id) ->
        $.getJSON(@url(id)).then (resp) =>
            resp = resp.data

            @createInstance resp.model, resp.related

    # Load a model and set it as current
    update: (id) ->
        @load(id).then (instance) =>
            @set "instance", instance

            instance

    getCopyableAttributes: (attributes) ->
        data = {}
        data[field.id] = attributes[field.id] for field in @fields.models when field.get("copyable") and field.id of attributes

        data

    url: (id) -> entity_url @id, id

    link: (id) -> "#{ @id}" + if id? then "/#{ id }" else ""
class EntityInstance extends Backbone.Model
    initialize: (attributes, options) ->
        @entity = options.entity
        @related = options.related
        @original = _.clone attributes

        @on "error", @processError, this
        @on "sync", => @original = _.clone @attributes
        @on "destroy", => @set "deleted_at", moment().unix() if @entity.get "soft_deleting"

    processError: (model, xhr) ->
        @trigger "invalid", this, xhr.responseJSON.data if xhr.responseJSON? and xhr.responseJSON.error is "VALIDATION"

    validate: ->
        @set "errors", {}
        null

    link: -> @entity.link if @isNew() then "create" else @id

    url: -> @entity.url @id

    sync: (method, model, options) ->
        if method in ["update", "create"]
            # Form data will allow us to upload files via AJAX request
            options.data = new AdvFormData(options.attrs ? @attributes).original

            # Set the content type to false to let browser handle it
            options.contentType = false
            options.processData = false

        super

    save: ->
        xhr = super

        return xhr if _.isEmpty @related

        queue = (xhr) =>
            save = []

            save.push xhr if xhr?

            for key, model of @related
                @entity.related.get(key).associate @, model if model.isNew()

                save.push model.save() if model.hasChangedSinceSync()

            $.when.apply $, save

        # Create related models after the main model is saved
        if @isNew() then xhr.then (resp) -> queue() else queue xhr

    parse: (resp) -> resp.data

    copy: ->
        copy = @entity.createInstance()

        copy.set @getCopyableAttributes(), silent: yes
        copy.related[key].set item.getCopyableAttributes(), silent: yes for key, item of @related

        copy

    getCopyableAttributes: -> @entity.getCopyableAttributes @attributes

    hasChangedSinceSync: ->
        return yes for key, value of @attributes when not _.isEqual value, @original[key]

        # Related models do not affect the result unless model is created
        return yes for key, related of @related when related.hasChangedSinceSync() unless @isNew()

        no

    isSaveable: -> (@isNew() and @entity.get("can_create")) or (!@isNew() and @entity.get("can_update"))
class EntityPage extends Backbone.View
    className: "entity-page"

    events: {
        "click .btn-create": "create"
    }

    constructor: (options) ->
        @className += " " + @className + "-" + options.model.id

        super

    initialize: (options) ->
        @listenTo @model, "change:instance", @toggleForm

        super

    toggleForm: (entity, instance) ->
        if @form?
            @stopListening @form.model
            @form.remove()

        if instance?
            @listenTo instance, "sync", -> Cruddy.router.navigate instance.link()

            @form = new EntityForm model: instance
            @$el.append @form.render().$el

            after_break => @form.show()

        this

    create: ->
        Cruddy.router.navigate @model.link("create"), trigger: true

        this

    render: ->
        @dispose()

        @$el.html @template()

        @dataSource = @model.createDataSource()

        @dataGrid = new DataGrid
            model: @dataSource

        @pagination = new Pagination
            model: @dataSource

        @filterList = new FilterList
            model: @dataSource.filter
            entity: @dataSource.entity

        @search = new SearchInput
            model: @dataSource
            key: "search"

        @dataSource.fetch()

        @$(".col-search").append @search.render().el
        @$(".col-filters").append @filterList.render().el
        @$el.append @dataGrid.render().el
        @$el.append @pagination.render().el

        this

    template: ->
        html = """
        <h1 class="page-header">
            #{ @model.get "title" }

        """

        if @model.get "can_create"
            html += """
                <button class="btn btn-default btn-create" type="button">
                    <span class="glyphicon glyphicon-plus"</span>
                </button>
            """

        html += "</h1>"

        html += """<div class="row row-search"><div class="col-xs-2 col-search"></div><div class="col-xs-10 col-filters"></div></div>"""

    dispose: ->
        @form.remove() if @form?
        @filterList.remove() if @filterList?
        @dataGrid.remove() if @dataGrid?
        @pagination.remove() if @pagination?
        @search.remove() if @search?
        @dataSource.stopListening() if @dataSource?

        this

    remove: ->
        @dispose()

        super
# View that displays a form for an entity instance
class EntityForm extends Backbone.View
    className: "entity-form"

    events:
        "click .btn-save": "save"
        "click .btn-close": "close"
        "click .btn-destroy": "destroy"
        "click .btn-copy": "copy"

    constructor: (options) ->
        @className += " " + @className + "-" + options.model.entity.id

        super

    initialize: ->
        @listenTo @model, "destroy", @handleDestroy

        @signOn @model
        @signOn related for key, related of @model.related

        @hotkeys = $(document).on "keydown." + @cid, "body", $.proxy this, "hotkeys"

        this

    signOn: (model) ->
        @listenTo model, "change", @enableSubmit
        @listenTo model, "invalid", @displayInvalid

    hotkeys: (e) ->
        # Ctrl + Z
        if e.ctrlKey and e.keyCode is 90 and e.target is document.body
            @model.set @model.previousAttributes()
            return false

        # Ctrl + Enter
        if e.ctrlKey and e.keyCode is 13
            @save()
            return false

        # Escape
        if e.keyCode is 27
            @close()
            return false

        this

    enableSubmit: ->
        @submit.attr "disabled", @model.hasChangedSinceSync() is no if not @request

        this

    displayAlert: (message, type) ->
        @alert.remove() if @alert?

        @alert = new Alert
            message: message
            className: "flash"
            type: type
            timeout: 3000

        @footer.prepend @alert.render().el

        this

    displaySuccess: -> @displayAlert "Получилось!", "success"

    displayInvalid: -> @displayAlert "Не получилось...", "warning"

    displayError: (xhr) -> @displayAlert "Ошибка", "danger" unless xhr.responseJSON?.error is "VALIDATION"

    handleDestroy: ->
        if @model.entity.get "soft_deleting"
            @update()
        else
            Cruddy.router.navigate @model.entity.link(), trigger: true

        this

    show: ->
        @$el.toggleClass "opened", true
        @tabs[0].focus()

        this

    save: ->
        return if @request? or not @model.hasChangedSinceSync()

        @request = @model.save(displayLoading: yes).done($.proxy this, "displaySuccess").fail($.proxy this, "displayError")

        @request.always =>
            @request = null
            @update()

        @update()

        this

    close: ->
        if @request
            confirmed = confirm "Вы точно хотите закрыть форму и отменить операцию?"
        else
            confirmed = if @model.hasChangedSinceSync() then confirm("Вы точно хотите закрыть форму? Все изменения будут утеряны!") else yes

        if confirmed
            @request.abort() if @request
            Cruddy.router.navigate @model.entity.link(), trigger: true

        this

    destroy: ->
        return if @request or @model.isNew()

        softDeleting = @model.entity.get "soft_deleting"

        confirmed = if not softDeleting then confirm("Точно удалить? Восстановить не получится!") else yes

        if confirmed
            @request = if @softDeleting and @model.get "deleted_at" then @model.restore else @model.destroy wait: true

            @request.always => @request = null

        this

    copy: ->
        @model.entity.set "instance", copy = @model.copy()
        Cruddy.router.navigate copy.link()

        this

    render: ->
        @dispose()

        @$el.html @template()

        @nav = @$ ".nav"
        @footer = @$ "footer"
        @submit = @$ ".btn-save"
        @destroy = @$ ".btn-destroy"
        @copy = @$ ".btn-copy"

        @tabs = []
        @renderTab @model, yes

        @renderTab related for key, related of @model.related

        @update()

    renderTab: (model, active) ->
        @tabs.push fieldList = new FieldList model: model

        id = "tab-" + model.entity.id
        fieldList.render().$el.insertBefore(@footer).wrap $ "<div></div>", { id: id, class: "wrap" + if active then " active" else "" }
        @nav.append @navTemplate model.entity.get("singular"), id, active

        this

    update: ->
        @$el.toggleClass "loading", @request?

        @submit.text if @model.isNew() then "Создать" else "Сохранить"
        @submit.attr "disabled", @request? or not @model.hasChangedSinceSync()
        @submit.toggle @model.entity.get if @model.isNew() then "can_create" else "can_update"

        @destroy.attr "disabled", @request?
        @destroy.html if @model.entity.get "soft_deleting" and @model.get "deleted_at" then "Восстановить" else "<span class='glyphicon glyphicon-trash' title='Удалить'></span>"
        @destroy.toggle not @model.isNew() and @model.entity.get "can_delete"
        
        @copy.toggle not @model.isNew() and @model.entity.get "can_create"

        this

    template: ->
        """
        <header>
            <div class="btn-group btn-group-sm">
                <button type="button" tabindex="-1" class="btn btn-link btn-copy" title="Копировать">
                    <span class="glyphicon glyphicon-book"></span>
                </button>
            </div>
            <ul class="nav nav-pills"></ul>
        </header>

        <footer>
            <button type="button" class="btn btn-default btn-close btn-sm" type="button">Закрыть</button>
            <button type="button" class="btn btn-default btn-destroy btn-sm" type="button"></button>
            <button type="button" class="btn btn-primary btn-save btn-sm" type="button" disabled></button>
        </footer>
        """

    navTemplate: (label, target, active) ->
        active = if active then " class=\"active\"" else ""
        """
        <li#{ active }><a href="##{ target }" data-toggle="tab">#{ label }</a></li>
        """

    remove: ->
        @$el.one(TRANSITIONEND, =>
            @dispose()

            $(document).off "." + @cid

            super
        )
        .removeClass "opened"

        this

    dispose: ->
        fieldList.remove() for fieldList in @tabs if @tabs?

        this
# Backend application file

$(".navbar").on "click", ".entity", (e) =>
    e.preventDefault();

    baseUrl = Cruddy.root + "/" + Cruddy.uri + "/"
    href = e.currentTarget.href.substr baseUrl.length

    Cruddy.router.navigate href, trigger: true

class App extends Backbone.Model
    entities: {}

    initialize: ->
        @container = $ "#container"
        @loadingRequests = 0

        @on "change:entity", @displayEntity, this

    displayEntity: (model, entity) ->
        @dispose()

        @container.html (@page = new EntityPage model: entity).render().el if entity

    displayError: (xhr) ->
        error = if not xhr? or xhr.status is 403 then "Ошибка доступа" else "Ошибка"

        @dispose()
        @container.html "<p class='alert alert-danger'>#{ error }</p>"

        this

    startLoading: ->
        @loading = setTimeout (=>
            $(document.body).addClass "loading"
            @loading = no

        ), 1000 if @loadingRequests++ is 0

        this

    doneLoading: ->
        if @loadingRequests is 0
            console.error "Seems like doneLoading is called too many times."

            return

        if --@loadingRequests is 0
            if @loading
                clearTimeout @loading
                @loading = no
            else
                $(document.body).removeClass "loading"

        this

    entity: (id, options = {}) ->
        return @entities[id] if id of @entities

        options = $.extend {}, {
            url: entity_url id, "schema"
            type: "get"
            dataType: "json"
            displayLoading: yes

        }, options

        @entities[id] = $.ajax(options).then (resp) =>
            entity = new Entity resp.data

            return entity if _.isEmpty entity.related.models

            # Resolve all related entites
            wait = (related.resolve() for related in entity.related.models)

            $.when.apply($, wait).then -> entity

    dispose: ->
        @page?.remove()

        this

Cruddy.app = new App

class Router extends Backbone.Router

    routes: {
        ":page": "page"
        ":page/create": "create"
        ":page/:id": "update"
    }

    loading: (promise) ->
        Cruddy.app.startLoading()
        promise.always -> Cruddy.app.doneLoading()

    entity: (id) ->
        promise = Cruddy.app.entity(id).done (entity) ->
            entity.set "instance", null
            Cruddy.app.set "entity", entity

        promise.fail -> Cruddy.app.displayError.apply(Cruddy.app, arguments).set "entity", false

    page: (page) -> @entity page

    create: (page) -> @entity(page).done (entity) -> entity.set "instance", entity.createInstance()

    update: (page, id) -> @entity(page).then (entity) -> entity.update(id)

Cruddy.router = new Router

Backbone.history.start { root: Cruddy.uri + "/", pushState: true, hashChange: false }