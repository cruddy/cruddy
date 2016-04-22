Cruddy = window.Cruddy || {}

TRANSITIONEND = "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd"
NOT_AVAILABLE = "&mdash;"
TITLE_SEPARATOR = " / "
VALIDATION_FAILED_CODE = 422

moment.lang [ Cruddy.locale, "en" ]

Backbone.emulateHTTP = true
Backbone.emulateJSON = true

$(document)
    .ajaxSend (e, xhr, options) ->
        options.displayLoading = no if not Cruddy.app
        Cruddy.app.startLoading() if options.displayLoading

        xhr.setRequestHeader "X-CSRF-TOKEN", Cruddy.token

        return

    .ajaxComplete (e, xhr, options) ->
        Cruddy.app.doneLoading() if options.displayLoading

        return

$(document.body)
    .on "click", "[data-trigger=fancybox]", (e) ->
        return no if $.fancybox.open(e.currentTarget) isnt false

        return

$.extend $.fancybox.defaults,
    openEffect: "elastic"
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

value_if = (bool, value) -> if bool then value else ""

get = (path, obj = window) ->
    return obj if _.isEmpty path

    for key in path.split "."

        return unless key of obj

        obj = obj[key]

    return obj

add_query_to_url = (url, query) ->
    if _.isObject query
        painConverter = (value, param) ->
            "#{ encodeURIComponent(param) }=#{ encodeURIComponent(value) }"

        query = _. map(query, painConverter).join("&")

    return if query && query.length then "#{ url }?#{ query }" else url
class Factory
    create: (name, options) ->
        constructor = @[name]
        return new constructor options if constructor?

        console.error "Failed to resolve #{ name }."

        null
$.extend Cruddy,

    Fields: {}
    Columns: {}
    Filters: {}
    formatters: new Factory

    getHistoryRoot: -> @baseUrl.substr @root.length

    getApp: ->
        @app = (new App).init() unless @app

        return @app

    ready: (callback) -> @getApp().ready callback
class Cruddy.View extends Backbone.View
    componentId: (component) -> @cid + "-" + component

    $component: (component) -> @$ "#" + @componentId(component)
class AdvFormData
    constructor: (data) ->
        @original = new FormData
        @append data if data?

    append: (value, name) ->
        return if value is undefined

        return @original.append name, value if value instanceof File or value instanceof Blob

        if _.isObject(value) or _.isArray(value)
            return @original.append name, "" if _.isEmpty(value)

            _.each value, (value, key) => @append value, @key(name, key)

            return

        @original.append name, @process value

    process: (value) ->
        return "" if value is null
        return 1 if value is yes
        return 0 if value is no

        value

    key: (outer, inner) -> if outer then "#{ outer }[#{ inner }]" else inner
class Cruddy.Attribute extends Backbone.Model

    initialize: (options) ->
        @entity = options.entity

        this

    # Get field's type (i.e. css class name)
    getType: -> "attribute"

    # Get field's help
    getHelp: -> @attributes.help

    # Get whether a column is visible
    isVisible: -> @attributes.hide is no
class DataSource extends Backbone.Model
    defaults:
        page: 1
        per_page: null
        keywords: ""
        order_by: null
        order_dir: "asc"

    initialize: (attributes, options) ->
        @entity = entity = options.entity
        @defaults = _.clone @attributes

        @options =
            url: entity.url()
            dataType: "json"
            type: "get"
            displayLoading: yes

            success: (resp) =>
                @resp = resp

                @trigger "data", this, resp.items

            error: (xhr) => @trigger "error", this, xhr

        @on "change:keywords", => @set "page", 1
        @on "change", (model, options) => @fetch() unless options.noFetch

    hasData: -> @resp?

    isEmpty: -> not @hasData() or _.isEmpty @resp.items

    hasMore: -> @hasData() and @resp.page < @resp.lastPage

    isFull: -> not @hasMore()

    inProgress: -> @request?

    fetch: ->
        @request.abort() if @request?

        @options.data = @_getRequestData()

        @request = $.ajax @options

        @request.always => @request = null

        @trigger "request", this, @request

        @request

    next: ->
        return if @inProgress() or @isFull()

        @set page: @get("page") + 1

        return this

    prev: ->
        return if @inProgress() or (page = @get "page") <= 1

        @set page: page - 1

        return this

    _getRequestData: ->
        data = {}

        data[key] = value for key, value of @attributes when _.isNumber(value) or not _.isEmpty(value)

        data

    getData: -> @resp?.items
    getTotal: -> @resp?.total
    getFrom: -> @resp?.from
    getTo: -> @resp?.to
    getLastPage: -> @resp?.lastPage

class SearchDataSource extends Backbone.Model
    defaults:
        keywords: null
        constraint: null

    initialize: (attributes, options) ->
        @resetData = no
        @data = []
        @page = null
        @more = yes

        @options =
            url: options.url
            type: "get"
            dataType: "json"

            data:
                simple: 1

            success: (resp) =>
                if @resetData
                    @data = []
                    @resetData = no

                @data.push item for item in resp.items

                @page = resp.page
                @more = resp.page < resp.lastPage
                @request = null

                @trigger "data", @, @data

                this

            error: (xhr) =>
                @request = null
                @trigger "error", @, xhr

                this

        $.extend yes, @options, options.ajaxOptions if options.ajaxOptions?

        @on "change", @refresh, this

        this

    refresh: ->
        @resetData = yes

        @fetchPage 1

    fetchPage: (page) ->
        @request.abort() if @request?

        $.extend @options.data, @attributes, { page: page }

        @trigger "request", this, @request = $.ajax @options

        @request

    next: ->
        @fetchPage if @page? then @page + 1 else 1 if @more

        this

    inProgress: -> @request?

    isEmpty: -> @page is null and not @request

    getById: (id) ->
        id = id.toString() if not id.length

        return _.find @data, (item) -> item.id.toString() == id
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
        @model.set "page", n if n > 0 and n <= @model.getLastPage()

        this

    previous: -> @page @model.get("page") - 1

    next: -> @page @model.get("page") + 1

    navigate: (e) ->
        e.preventDefault()

        @page $(e.target).data "page" if !@model.inProgress()

    disable: ->
        @$("a").addClass "disabled"

        this

    render: ->
        if @model.hasData()
            last = @model.getLastPage()

            @$el.toggle last? and last > 1

            @$el.html @template @model.get("page"), last if last > 1

        this

    template: (current, last) ->
        html = ""
        html += @renderLink current - 1, "&larr; #{ Cruddy.lang.prev }", "previous" + if current > 1 then "" else " disabled"
        html += @renderStats() if @model.getTotal()?
        html += @renderLink current + 1, "#{ Cruddy.lang.next } &rarr;", "next" + if current < last then "" else " disabled"

        html

    renderStats: -> """<li class="stats"><span>#{ @model.getFrom() } - #{ @model.getTo() } / #{ @model.getTotal() }</span></li>"""

    renderLink: (page, label, className = "") -> """<li class="#{ className }"><a href="#" data-page="#{ page }">#{ label }</a></li>"""

class DataGrid extends Cruddy.View
    tagName: "table"
    className: "table table-hover dg"

    events: {
        "click .col__sortable": "setOrder"
        "click [data-action]": "executeAction"
    }

    constructor: (options) ->
        @className += " dg-" + options.entity.id

        super

    initialize: (options) ->
        @entity = options.entity

        @columns = @entity.columns.models.filter (col) -> col.isVisible()

        @addActionColumns @columns

        @listenTo @model, "data", => @renderBody() if @$items?
        @listenTo @model, "change:order_by change:order_dir", @markOrderColumn

        @listenTo @entity, "change:instance", @markActiveItem

    addActionColumns: (columns) ->
        @columns.unshift new Cruddy.Columns.ViewButton entity: @entity
        @columns.push new Cruddy.Columns.DeleteButton entity: @entity if @entity.deletePermitted()

        return this

    markOrderColumn: ->
        orderBy = @model.get("order_by")
        orderDir = @model.get("order_dir") or "asc"

        if @orderBy? and orderBy isnt @orderBy
            @$colCell(@orderBy).removeClass "asc desc"

        @$colCell(@orderBy = orderBy).removeClass("asc desc").addClass orderDir

        this

    markActiveItem: ->
        @$itemRow(model).removeClass "active" if model = @entity.previous "instance"
        @$itemRow(model).addClass "active" if model = @entity.get "instance"

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

    render: ->
        @$el.html @template()

        @$header = @$component "header"
        @$items = @$component "items"

        @renderHead()
        @renderBody()

        this

    renderHead: ->
        html = ""
        html += @renderHeadCell column for column in @columns

        @$header.html html

        @markOrderColumn()

    renderHeadCell: (column) -> """
        <th class="#{ column.getClass() }" id="#{ @colCellId column }" data-id="#{ column.id }">
            #{ @renderHeadCellValue column }
        </th>
    """

    renderHeadCellValue: (col) ->
        title = _.escape col.getHeader()

        if help = _.escape col.getHelp()
            title = """
                <span class="glyphicon glyphicon-question-sign" title="#{ help }"></span> #{ title }
            """

        return title

    renderBody: ->
        if @model.isEmpty()
            @$items.html @emptyTemplate()

            return this

        html = ""
        html += @renderRow item for item in @model.getData()

        @$items.html html

        @markActiveItem()

    renderRow: (item) ->
        html = """
            <tr class="item #{ @itemStates item }" id="#{ @itemRowId item }" data-id="#{ item.meta.id }">"""

        html += @renderCell columns, item for columns in @columns

        html += "</tr>"

    itemStates: (item) ->
        states = if item.attributes._states then item.attributes._states else ""

        states += " active" if (instance = @entity.get "instance")? and item.meta.id == instance.id

        return states

    renderCell: (column, item) -> """
        <td class="#{ column.getClass() }">
            #{ column.render item }
        </td>
    """

    executeAction: (e) ->
        $el = $ e.currentTarget
        action = $el.data "action"

        if action and action = this[action]
            e.preventDefault()

            action.call this, $el.closest(".item").data("id"), $el

        return

    deleteItem: (id, $el) ->
        return if not confirm Cruddy.lang.confirm_delete

        $row = $el.closest ".item"

        $el.attr "disabled", yes

        @entity.destroy id,

            displayLoading: yes

            success: =>
                $row.fadeOut()
                @model.fetch()

            fail: ->
                $el.attr "disabled", no

        return

    template: -> """
        <thead><tr id="#{ @componentId "header" }"></tr></thead>
        <tbody class="items" id="#{ @componentId "items" }"></tbody>
    """

    emptyTemplate: -> """
        <tr class="empty">
            <td colspan="#{ @columns.length }">
                #{ Cruddy.lang.no_results }
            </td>
        </tr>
    """

    colCellId: (col) -> @componentId "col-" + col.id

    $colCell: (id) -> @$component "col-" + id

    itemRowId: (item) -> @componentId "item-" + item.meta.id

    $itemRow: (item) -> @$component "item-" + item.meta.id
class FilterList extends Backbone.View
    className: "filter-list"

    tagName: "fieldset"

    events:
        "click .btn-apply": "apply"
        "click .btn-reset": "reset"

    initialize: (options) ->
        @entity = options.entity
        @availableFilters = options.filters

        @filterModel = new Backbone.Model
        @filterModel.entity = @entity

        @listenTo @model, "request", => @_toggleButtons yes
        @listenTo @model, "data", => @_toggleButtons no
        @listenTo @model, "change", => @_setDataFromDataSource() unless @_applying

        @_setDataFromDataSource()

        this

    _toggleButtons: (disabled) ->
        @$buttons.prop "disabled", disabled

        return

    apply: ->
        @_applying = yes

        @model.set $.extend @_prepareData(), page: 1

        @_applying = no

        return this

    _prepareData: ->
        data = {}

        for key, value of @filterModel.attributes when filter = @availableFilters.get(key)
            data[filter.getDataKey()] = filter.prepareData(value)

        return data

    _setDataFromDataSource: ->
        data = {}

        for filter in @availableFilters.models
            data[filter.id] = filter.parseData @model.get filter.getDataKey()

        @filterModel.set data

        return this

    reset: ->
        input.empty() for input in @filters

        @apply()

    render: ->
        @dispose()

        @$el.html @template()
        @items = @$ ".filter-list-container"

        for filter in @availableFilters.models
            continue unless input = filter.createFilterInput @filterModel

            @filters.push input
            @items.append input.render().el
            input.$el.wrap("""<div class="form-group #{ filter.getClass() }"></div>""").parent().before "<label>#{ filter.getLabel() }</label>"

        @$buttons = @$el.find ".fl-btn"

        this

    template: -> """
        <div class="filter-list-container"></div>
        <button type="button" class="btn fl-btn btn-primary btn-apply">#{ Cruddy.lang.filter_apply }</button>
        <button type="button" class="btn fl-btn btn-default btn-reset">#{ Cruddy.lang.filter_reset }</button>
    """

    dispose: ->
        filter.remove() for filter in @filters if @filters?

        @filters = []

        this

    remove: ->
        @dispose()

        super
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
class Cruddy.UploadQueue
    constructor: (storage) ->
        @_storage = storage
        @_q = []

        @_xhr = null
        @_file = null
        @_working = no

        @_uploaded = 0
        @_total = 0

    push: (files, path) ->
        files = [ files ] if files instanceof File

        for file in files
            @_q.push
                file: file
                path: path
                uploaded: 0
                total: file.size

            if @_working
                @_uploadStats.total += file.size

                @_notifyProgress()

        return this

    start: () ->
        return this if @_working || ! @_q.length

        @_working = yes
        @_uploaded = 0
        @_total = _.reduce @_q, ((sum, file) => sum + file.total), 0

        @_notifyProgress()

        @trigger "started"

        @_next()

    stop: () ->
        return this unless @_working

        @_working = no

        @_xhr and @_xhr.abort()

        @trigger "stopped"

        return this

    isEmpty: -> ! @_q.length

    isWorking: -> @_working

    isCompleted: -> ! @_q.length

    _next: () ->
        @_xhr = null
        @_file = null

        return @stop() if @isEmpty()

        # Cache currently uploaded amount
        uploaded = @_uploaded

        @_file = file = @_q.shift()

        @trigger "filestarted", file

        @_xhr = @_storage

        .upload file.file, file.path, (loaded, total) =>
            file.uploaded = loaded

            # Not sure whether it is needed; total is taken from file size
            # Maybe it might update when upload started
            if file.total != total
                @_total += total - file.total

                file.total = total

            @trigger "fileprogress", file

            @_uploaded = uploaded + loaded

            @_notifyProgress()

        .always =>
            uploaded += file.total

            if @_uploaded != uploaded
                @_uploaded = uploaded

                @_notifyProgress()

            @_next()

        .done (resp) => @trigger "filecompleted", resp, file

        .fail (xhr, error) =>
            file.uploaded = file.total

            if xhr.status is 422
                @trigger "fileinvalid", xhr.responseJSON.message, xhr.responseJSON.code, file

                return

            if error is "abort"
                @trigger "fileaborted", file

                return

            @trigger "fileerror", file, xhr, error

            return

        return this

    _notifyProgress: () ->
        stats =
            uploaded: @_uploaded
            total: @_total

        @trigger "progress", stats if stats.total > 0

        return this

_.extend Cruddy.UploadQueue.prototype, Backbone.Events
class Cruddy.FileStorage
    constructor: (id) ->
        @id = id
        @_queue = new Cruddy.UploadQueue this

    url: (path, query) ->
        url = "#{ Cruddy.baseUrl }/_files/#{ @id }/#{ path || "" }"

        add_query_to_url url, query

    upload: (file, path, progressCallback) ->
        if _.isFunction path
            progressCallback = path
            path = ""

        data = new FormData

        data.append "file", file

        $.ajax
            data: data
            url: @url path
            type: "POST"
            dataType: "json"
            processData: false
            contentType: false

            xhr: ->
                xhr = $.ajaxSettings.xhr()

                xhr.upload and xhr.upload.addEventListener "progress", (e) ->
                    return unless e.lengthComputable || ! progressCallback

                    progressCallback e.loaded, e.total

                xhr

    getUploadQueue: -> @_queue

    @instance: (id) -> new @ id
Cruddy.Layout = {}

class Cruddy.Layout.Element extends Cruddy.View

    constructor: (options, parent) ->
        @parent = parent
        @disable = options.disable ? no

        super

    initialize: ->
        @model = @parent.model if not @model and @parent
        @entity = @model.entity if @model

        super

    handleValidationError: (error) ->
        @parent.handleValidationError error if @parent

        return this

    isDisabled: ->
        return yes if @disable
        return @parent.isDisabled() if @parent

        return no

    # Get whether element is focusable
    isFocusable: -> no

    # Focus the element
    focus: -> return this
class Cruddy.Layout.Container extends Cruddy.Layout.Element

    initialize: (options) ->
        super

        @$container = @$el
        @items = []

        @createItems options.items if options.items

        return this

    create: (options) ->
        constructor = get options.class

        if not constructor or not _.isFunction constructor
            console.error "Couldn't resolve element of type ", options.class

            return

        @append new constructor options, this

    createItems: (items) ->
        @create item for item in items

        this

    append: (element) ->
        @items.push element if element

        return element

    renderElement: (element) ->
        @$container.append element.render().$el

        return this

    render: ->
        @renderElement element for element in @items if @items

        super

    remove: ->
        item.remove() for item in @items

        super

    getFocusable: -> _.find @items, (item) -> item.isFocusable()

    isFocusable: -> return @getFocusable()?

    focus: ->
        el.focus() if el = @getFocusable()

        return this
class Cruddy.Layout.BaseFieldContainer extends Cruddy.Layout.Container

    constructor: (options) ->
        @title = options.title ? null

        super
class Cruddy.Layout.FieldSet extends Cruddy.Layout.BaseFieldContainer
    tagName: "fieldset"

    render: ->
        @$el.html @template()

        @$container = @$component "body"

        super

    template: ->
        html = if @title then "<legend>" + _.escape(@title) + "</legend>" else ""

        return html + "<div id='" + @componentId("body") + "'></div>"
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
class Cruddy.Layout.Row extends Cruddy.Layout.Container
    className: "row"
class Cruddy.Layout.Col extends Cruddy.Layout.BaseFieldContainer

    initialize: (options) ->
        @$el.addClass "col-xs-" + options.span

        super
class Cruddy.Layout.Field extends Cruddy.Layout.Element

    initialize: (options) ->
        super

        @fieldView = null

        if not @field = @entity.field options.field
            console.error "The field #{ options.field } is not found in #{ @entity.id }."

        return this

    render: ->
        if @field and @field.isVisible()
            @fieldView = @field.createView @model, @isDisabled(), this

        @$el.html @fieldView.render().$el if @fieldView

        return this

    remove: ->
        @fieldView.remove() if @fieldView

        super

    isFocusable: -> @fieldView and @fieldView.isFocusable()

    focus: ->
        @fieldView.focus() if @fieldView

        return this
class Cruddy.Layout.Text extends Cruddy.Layout.Element
    tagName: "p"
    className: "text-node"

    initialize: (options) ->
        @$el.html options.contents if options.contents

        super
# Displays a list of entity's fields
class FieldList extends Cruddy.Layout.BaseFieldContainer
    className: "field-list"

    initialize: ->
        super

        @append new Cruddy.Layout.Field { field: field.id }, this for field in @entity.fields.models

        return this
class Cruddy.Layout.Layout extends Cruddy.Layout.Container

    initialize: ->
        super

        @setupLayout()

    setupLayout: ->
        if @entity.attributes.layout
            @createItems @entity.attributes.layout
        else
            @setupDefaultLayout()

        return this

    setupDefaultLayout: -> return this
Cruddy.Entity = {}

class Cruddy.Entity.Entity extends Backbone.Model

    initialize: (attributes, options) ->
        @fields = @createObjects attributes.fields
        @columns = @createObjects attributes.columns
        @filters = @createObjects attributes.filters
        @permissions = Cruddy.permissions[@id]
        @cache = {}

        return this

    createObjects: (items) ->
        data = []

        for options in items
            options.entity = this

            constructor = get options.class

            throw "The class #{ options.class } is not found" unless constructor

            data.push new constructor options

        new Backbone.Collection data

    # Create a datasource that will require specified columns and can be filtered
    # by specified filters
    createDataSource: (data) ->
        defaults =
            order_by: @get "order_by"

        defaults.order_dir = col.get "order_dir" if col = @columns.get defaults.order_by

        data = $.extend {}, defaults, data

        return new DataSource data, entity: this

    getDataSource: ->
        @dataSource = @createDataSource() unless @dataSource

        return @dataSource

    # Create filters for specified columns
    createFilters: (columns = @columns) ->
        filters = (col.createFilter() for col in columns.models when col.get("filter_type") is "complex")

        new Backbone.Collection filters

    # Create an instance for this entity
    createInstance: (data = {}, options = {}) ->
        options.entity = this

        attributes = _.extend {}, @get("defaults"), data.attributes

        instance = new Cruddy.Entity.Instance attributes, options

        instance.setMetaFromResponse data

    # Get a field with specified id
    field: (id) ->
        if not field = @fields.get id
            console.error "The field #{id} is not found."

            return

        return field

    getField: (id) -> @fields.get id

    search: (options = {}) -> new SearchDataSource {}, $.extend { url: @url() }, options

    # Load a model
    load: (id, options) ->
        defaults =
            cached: yes # whether to get record from the cache

        options = $.extend defaults, options

        return $.Deferred().resolve(@cache[id]).promise() if options.cached and id of @cache

        xhr = $.ajax
            url: @url(id)
            type: "GET"
            dataType: "json"
            displayLoading: yes

        xhr = xhr.then (resp) =>
            instance = @createInstance resp

            @cache[instance.id] = instance

            return instance

        return xhr

    # Destroy a model
    destroy: (id, options = {}) ->
        options.url = @url id
        options.type = "POST"
        options.dataType = "json"
        options.data = _method: "DELETE"
        options.displayLoading = yes

        return $.ajax options

    # Destroy a model
    executeAction: (id, action, options = {}) ->
        options.url = @url id + "/" + action
        options.type = "POST"
        options.dataType = "json"
        options.displayLoading = yes

        return $.ajax options

    # Get only those attributes are not unique for the model
    getCopyableAttributes: (model, copy) ->
        data = {}

        data[field.id] = field.copyAttribute(model, copy) for field in @fields.models when field.isCopyable()

        data

    hasChangedSinceSync: (model) ->
        for field in @fields.models when field.hasChangedSinceSync model
            console.log field
            
            return yes

        return no

    prepareAttributes: (attributes, model) ->
        result = {}
        result[key] = field.prepareAttribute value for key, value of attributes when field = @getField(key)

        return result

    # Get url that handles syncing
    url: (id) -> entity_url @id, id

    # Get link to this entity or to the item of the entity
    link: (id) ->
        link = @url()

        id = id.id if id instanceof Cruddy.Entity.Instance

        return if id then link + "?id=" + id else link

    createController: ->
        controllerClass = get(@attributes.controller_class) or Cruddy.Entity.Page

        throw "Failed to resolve page class #{ @attributes.view }" unless controllerClass

        return new controllerClass model: this

    # Get title in plural form
    getPluralTitle: -> @attributes.title.plural

    # Get title in singular form
    getSingularTitle: -> @attributes.title.singular

    getPermissions: -> @permissions

    readPermitted: -> @permissions.read

    updatePermitted: -> @permissions.update

    createPermitted: -> @permissions.create

    deletePermitted: -> @permissions.delete

    isSoftDeleting: -> @attributes.soft_deleting

    getPrimaryKey: -> @attributes.primary_key or "id"
class Cruddy.Entity.Instance extends Backbone.Model
    # Cid is used for identification during validation and we need this to be
    # numeric value rather than string to allow laravel's asterisk validation
    # We prefix with 0 to make sure that cid doesn't interfere with default
    # numeric primary keys
    cidPrefix: "0"

    idAttribute: "__id"

    constructor: (attributes, options) ->
        @entity = options.entity
        @meta = {}

        super

    initialize: (attributes, options) ->
        @syncOriginalAttributes()

        @on "error", @handleErrorEvent, this
        @on "sync", @handleSyncEvent, this
        @on "destroy", @handleDestroyEvent, this

        this

    syncOriginalAttributes: ->
        @original = _.clone @attributes

        return this

    handleSyncEvent: (model, resp) ->
        @syncOriginalAttributes()

        @setMetaFromResponse resp.model if resp?.model?

        this

    setMetaFromResponse: (resp) ->
        @meta = _.clone resp.meta if resp.meta?

        return this

    handleErrorEvent: (model, xhr) ->
        @trigger "invalid", this, xhr.responseJSON if xhr.status is VALIDATION_FAILED_CODE

        return

    handleDestroyEvent: (model) ->
        @isDeleted = yes

        return

    validate: ->
        @set "errors", {}

        return null

    link: -> @entity.link @id or "create"

    url: -> @entity.url @id

    set: (key, val, options) ->
        if _.isObject key
            for attributeId, value of key when field = @entity.getField attributeId
                key[attributeId] = field.parse this, value

        super

    sync: (method, model, options) ->
        if method in ["update", "create"]
            # Form data will allow us to upload files via AJAX request
            options.data = new AdvFormData(@entity.prepareAttributes options.attrs ? @attributes, this).original

            # Set the content type to false to let browser handle it
            options.contentType = false
            options.processData = false

        super

    parse: (resp) -> resp.model.attributes

    copy: ->
        copy = @entity.createInstance()

        copy.set @entity.getCopyableAttributes(this, copy), silent: yes

        copy

    hasChangedSinceSync: -> return @entity.hasChangedSinceSync this

    # Get whether is allowed to save instance
    canBeSaved: ->
        isNew = @isNew()

        return ((isNew and @entity.createPermitted()) or (not isNew and @entity.updatePermitted())) and (not @isDeleted or not isNew)

    serialize: ->
        data = if @isDeleted then {} else @entity.prepareAttributes @attributes, this

        return $.extend data, { __id: @id, __d: @isDeleted }

    # Get current action on the model
    action: -> if @isNew() then "create" else "update"

    getTitle: -> if @isNew() then Cruddy.lang.model_new_record else @meta.title

    getOriginal: (key) -> @original[key]
class Cruddy.Entity.Page extends Cruddy.View
    className: "page entity-page"

    events: {
        "click .ep-btn-create": "create"
        "click .ep-btn-refresh": "refreshData"
    }

    constructor: (options) ->
        @className += " entity-page-" + options.model.id

        super

    initialize: (options) ->
        @dataSource = @_setupDataSource()

        # Make sure that those events not fired twice
        after_break =>
            @listenTo Cruddy.router, "route:index", @_updateFromQuery

        super

    _updateFromQuery: ->
        @_updateDataSourceFromQuery()

        @_displayForm().fail => @_updateModelIdInQuery replace: yes

        return this

    _setupDataSource: ->
        @dataSource = dataSource = @model.getDataSource()

        @_updateFromQuery()

        dataSource.fetch() unless dataSource.inProgress() or dataSource.hasData()

        @listenTo dataSource, "change",  @_refreshQuery

        return dataSource

    _refreshQuery: ->
        dataSource = @dataSource

        Cruddy.router.refreshQuery dataSource.attributes, dataSource.defaults, trigger: no

        return this

    _updateDataSourceFromQuery: (options) ->
        data = $.extend {}, @dataSource.defaults, _.omit Cruddy.router.query.keys, [ "id" ]

        data[key] = null for key of @dataSource.attributes when not (key of data)

        @dataSource.set data, options

        return

    _updateModelIdInQuery: (options) ->
        router = Cruddy.router

        options = $.extend { trigger: no, replace: no }, options

        if @form
            router.setQuery "id", @form.model.id or "new", options
        else
            router.removeQuery "id", options

        return this

    _displayForm: (instanceId) ->
        return @loadingForm if @loadingForm

        instanceId = instanceId ? Cruddy.router.getQuery("id")

        if instanceId instanceof Cruddy.Entity.Instance
            instance = instanceId
            instanceId = instance.id or "new"

        @loadingForm = dfd = $.Deferred()

        @loadingForm.always => @loadingForm = null

        if @form
            compareId = if @form.model.isNew() then "new" else @form.model.id

            if instanceId is compareId or not @form.confirmClose()

                dfd.reject()

                return dfd.promise()

        resolve = (instance) =>
            @_createAndRenderForm instance
            dfd.resolve instance

        instance = @model.createInstance() if instanceId is "new" and not instance

        if instance
            resolve instance

            return dfd.promise()

        if instanceId
            @model.load(instanceId).done(resolve).fail -> dfd.reject()
        else
            @form?.remove()
            dfd.resolve()

        return dfd.promise()

    _createAndRenderForm: (instance) ->
        @form?.remove()

        @form = form = Cruddy.Entity.Form.display instance

        form.on "close", => Cruddy.router.removeQuery "id", trigger: no
        form.on "created", (model) -> Cruddy.router.setQuery "id", model.id

        form.on "remove", =>
            @form = null
            @model.set "instance", null

            @stopListening instance

        form.on "saved", =>
            @dataSource.fetch()
            @_updateModelIdInQuery replace: yes

        form.on "saved remove", -> Cruddy.app.updateTitle()

        @model.set "instance", instance

        Cruddy.app.updateTitle()

        this

    displayForm: (id) -> @_displayForm(id).done => @_updateModelIdInQuery()

    create: ->
        @displayForm "new"

        this

    refreshData: (e) ->
        btn = $ e.currentTarget
        btn.prop "disabled", yes

        @dataSource.fetch().always -> btn.prop "disabled", no

        this

    render: ->
        @$el.html @template()

        @searchInputView = @createSearchInputView()
        @dataView = @createDataView()
        @paginationView = @createPaginationView()
        @filterListView = @createFilterListView()

        @$component("search_input_view").append @searchInputView.render().$el   if @searchInputView
        @$component("filter_list_view").append @filterListView.render().el      if @filterListView
        @$component("data_view").append @dataView.render().el                   if @dataView
        @$component("pagination_view").append @paginationView.render().el       if @paginationView

        return this

    createDataView: -> new DataGrid
        model: @dataSource
        entity: @model

    createPaginationView: -> new Pagination model: @dataSource

    createFilterListView: ->
        return if (filters = @dataSource.entity.filters).isEmpty()

        return new FilterList
            model: @dataSource
            entity: @model
            filters: filters

    createSearchInputView: -> new Cruddy.Inputs.Search
        model: @dataSource
        key: "keywords"

    template: -> """
        <div class="content-header">
            <div class="column column-main">
                <h1 class="entity-title">#{ @model.getPluralTitle() }</h1>

                <div class="entity-title-buttons">
                    #{ @buttonsTemplate() }
                </div>
            </div>

            <div class="column column-extra">
                <div class="entity-search-box" id="#{ @componentId "search_input_view" }"></div>
            </div>
        </div>

        <div class="content-body">
            <div class="column column-main">
                <div id="#{ @componentId "data_view" }"></div>
                <div id="#{ @componentId "pagination_view" }"></div>
            </div>

            <div class="column column-extra" id="#{ @componentId "filter_list_view" }"></div>
        </div>
    """

    buttonsTemplate: ->
        html = """
            <button type="button" class="btn btn-default ep-btn-refresh" title="#{ Cruddy.lang.refresh }">
                #{ b_icon "refresh" }
            </button>
        """

        html += " "

        html += """
            <button type="button" class="btn btn-primary ep-btn-create" title="#{ Cruddy.lang.add }">
                #{ b_icon "plus" }
            </button>
        """ if @model.createPermitted()

        html

    remove: ->
        @form?.remove()

        @filterListView?.remove()
        @dataView?.remove()
        @paginationView?.remove()
        @searchInputView?.remove()

        super

    getPageTitle: ->
        title = @model.getPluralTitle()

        title = @form.model.getTitle() + TITLE_SEPARATOR + title if @form?

        title

    executeCustomAction: (actionId, modelId, el) ->
        if el and not $(el).parent().is "disabled"
            @model.executeAction modelId, actionId, success: (resp) =>
                @dataSource.fetch() if resp.successful

                Cruddy.getApp().displayActionResult resp

        return this

    pageUnloadConfirmationMessage: -> return @form?.pageUnloadConfirmationMessage()
# View that displays a form for an entity instance
class Cruddy.Entity.Form extends Cruddy.Layout.Layout
    className: "entity-form"

    events:
        "click [data-action]": "executeAction"

    constructor: (options) ->
        @className += " " + @className + "-" + options.model.entity.id

        super

    initialize: (options) ->
        super

        @saveOptions =
            displayLoading: yes

            xhr: =>
                xhr = $.ajaxSettings.xhr()
                xhr.upload.addEventListener('progress', $.proxy @, "progressCallback") if xhr.upload

                xhr

        @listenTo @model, "destroy", @handleModelDestroyEvent
        @listenTo @model, "invalid", @handleModelInvalidEvent

        @hotkeys = $(document).on "keydown." + @cid, "body", $.proxy this, "hotkeys"

        return this

    setupDefaultLayout: ->
        tab = @append new Cruddy.Layout.TabPane { title: @model.entity.get("title").singular }, this

        tab.append new Cruddy.Layout.Field { field: field.id }, tab for field in @entity.fields.models

        return this

    hotkeys: (e) ->
        # Ctrl + Z
        if e.ctrlKey and e.keyCode is 90 and e.target is document.body
            @model.set @model.previousAttributes()
            return false

        # Ctrl + Enter
        if e.ctrlKey and e.keyCode is 13
            @saveModel()
            return false

        # Escape
        if e.keyCode is 27
            @closeForm()
            e.preventDefault()

            return false

        this

    displayAlert: (message, type, timeout) ->
        @alert.remove() if @alert?

        @alert = new Alert
            message: message
            className: "flash"
            type: type
            timeout: timeout

        @$footer.prepend @alert.render().el

        this

    displaySuccess: (resp) ->
        @displayAlert Cruddy.lang.form_saved, "success", 3000

        Cruddy.getApp().displayActionResult resp.actionResult if resp.actionResult

        return this

    displayError: (xhr) ->
        unless xhr.status is VALIDATION_FAILED_CODE
            @displayAlert Cruddy.lang.form_failed, "danger", 5000

        return this

    handleModelInvalidEvent: -> @displayAlert Cruddy.lang.form_invalid, "warning", 5000

    handleModelDestroyEvent: ->
        @updateModelState()

        @trigger "destroyed", @model

        this

    show: ->
        @$el.toggleClass "opened", true

        @items[0].activate()

        @focus()

        this

    save: (options) ->
        return if @request?

        isNew = @model.isNew()

        @setupRequest @model.save null, $.extend {}, @saveOptions, options

        @request.done (resp) =>
            @trigger (if isNew then "created" else "updated"), @model, resp
            @trigger "saved", @model, resp
            @updateModelState()

        return this

    saveModel: -> @save()

    saveWithAction: ($el) -> @save url: @model.entity.url @model.id + "/" + $el.data "actionId"

    destroyModel: ->
        return if @request or @model.isNew()

        softDeleting = @model.entity.get "soft_deleting"

        confirmed = if not softDeleting then confirm(Cruddy.lang.confirm_delete) else yes

        if confirmed
            @request = if @softDeleting and @model.get "deleted_at" then @model.restore else @model.destroy wait: true

            @request.always => @request = null

        this

    copyModel: ->
        Cruddy.app.entityView.displayForm @model.copy()

        this

    refreshModel: ->
        return if @request?

        @setupRequest @model.fetch() if @confirmClose()

        @request.done => @updateModelMetaState()

        return this

    setupRequest: (request) ->
        request.done($.proxy this, "displaySuccess").fail($.proxy this, "displayError")

        request.always =>
            @request = null
            @updateRequestState()

        @request = request

        @updateRequestState()

    progressCallback: (e) ->
        if e.lengthComputable
            width = (e.loaded * 100) / e.total

            @$progressBar.width(width + '%').parent().show()

            @$progressBar.parent().hide() if width is 100

        this

    closeForm: ->
        if @confirmClose()
            @remove()
            @trigger "close"

        this

    pageUnloadConfirmationMessage: ->
        return if @model.isDeleted

        return Cruddy.lang.onclose_abort if @request

        return Cruddy.lang.onclose_discard if @model.hasChangedSinceSync()

    confirmClose: ->
        unless @model.isDeleted
            return confirm Cruddy.lang.confirm_abort if @request
            return confirm Cruddy.lang.confirm_discard if @model.hasChangedSinceSync()

        return yes

    render: ->
        @$el.html @template()

        @$container = @$component "body"

        @$nav = @$component "nav"
        @$footer = @$component "footer"
        @$btnSave = @$component "save"
        @$deletedMsg = @$component "deleted-message"
        @$progressBar = @$component "progress"

        @$serviceMenu = @$component "service-menu"
        @$serviceMenuItems = @$component "service-menu-items"

        @updateModelState()

        super

    renderElement: (el) ->
        @$nav.append el.getHeader().render().$el

        super

    updateRequestState: ->
        isLoading = @request?

        @$el.toggleClass "loading", isLoading
        @$btnSave.attr "disabled", isLoading

        if @$btnExtraActions
            @$btnExtraActions.attr "disabled", isLoading
            @$btnExtraActions.children(".btn").attr "disabled", isLoading

        this

    updateModelState: ->
        entity = @model.entity
        isNew = @model.isNew()
        isDeleted = @model.isDeleted or false

        @$el.toggleClass "destroyed", isDeleted

        @$btnSave.text if isNew then Cruddy.lang.create else Cruddy.lang.save
        @$btnSave.toggle not isDeleted and if isNew then entity.createPermitted() else entity.updatePermitted()

        @updateModelMetaState()

    updateModelMetaState: ->
        isNew = @model.isNew()
        isDeleted = @model.isDeleted or false

        @$serviceMenu.toggle not isNew
        @$serviceMenuItems.html @renderServiceMenuItems() unless isNew

        @$btnExtraActions?.remove()
        @$btnExtraActions = null

        if @model.entity.updatePermitted()
            @$btnSave.before @$btnExtraActions = $ html if not isNew and not isDeleted and html = @renderExtraActionsButton()

        return this

    template: -> """
        <div class="navbar navbar-default navbar-static-top" role="navigation">
            <div class="container-fluid">
                <ul id="#{ @componentId "nav" }" class="nav navbar-nav"></ul>

                <ul id="#{ @componentId "service-menu" }" class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                            <span class="glyphicon glyphicon-option-horizontal"></span>
                        </a>

                        <ul class="dropdown-menu" role="menu" id="#{ @componentId "service-menu-items" }"></ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content" id="#{ @componentId "body" }"></div>

        <footer id="#{ @componentId "footer" }">
            <span class="fs-deleted-message">#{ Cruddy.lang.model_deleted }</span>

            <button data-action="closeForm" id="#{ @componentId "close" }" type="button" class="btn btn-default">#{ Cruddy.lang.close }</button><!--
            --><button data-action="saveModel" id="#{ @componentId "save" }" type="button" class="btn btn-primary btn-save"></button>

            <div class="progress">
                <div id="#{ @componentId "progress" }" class="progress-bar form-save-progress"></div>
            </div>
        </footer>
    """

    renderServiceMenuItems: ->
        entity = (model = @model).entity

        html = ""

        unless (isDeleted = model.isDeleted) or _.isEmpty items = model.meta.links
            html += render_presentation_actions items
            html += render_divider()

        html += """
            <li class="#{ value_if isDeleted, "disabled" }">
                <a data-action="refreshModel" href="#">
                    #{ Cruddy.lang.model_refresh }
                </a>
            </li>
        """

        html += """
            <li class="#{ value_if not entity.createPermitted(), "disabled" }">
                <a data-action="copyModel" href="#">
                    #{ Cruddy.lang.model_copy }
                </a>
            </li>
        """

        html += """
            <li class="divider"></li>

            <li class="#{ value_if isDeleted or not entity.deletePermitted(), "disabled" }">
                <a data-action="destroyModel" href="#">
                    <span class="glyphicon glyphicon-trash"></span> #{ Cruddy.lang.model_delete }
                </a>
            </li>
        """

        return html

    renderExtraActionsButton: ->
        return if _.isEmpty @model.meta.actions

        mainAction = _.find(@model.meta.actions, (item) -> not item.disabled) or _.first(@model.meta.actions)

        button = """
            <button data-action="saveWithAction" data-action-id="#{ mainAction.id }" type="button" class="btn btn-#{ mainAction.state }" #{ value_if mainAction.isDisabled, "disabled" }>
                #{ mainAction.title }
            </button>
        """

        return @wrapWithExtraActions(button, mainAction)

    wrapWithExtraActions: (button, mainAction) ->
        actions = _.filter @model.meta.actions, (action) -> action isnt mainAction

        return button if _.isEmpty actions

        html = ""
        html += """
            <li class="#{ value_if action.disabled, "disabled" }">
                <a data-action="saveWithAction" data-action-id="#{ action.id }" href="#">#{ action.title }</a>
            </li>
        """ for action in actions

        return """
            <div class="btn-group dropup">
                #{ button }

                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                </button>

                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    #{ html }
                </ul>
            </div>
        """

    remove: ->
        @trigger "remove", @

        @request.abort() if @request
        $(document).off "." + @cid

        @$el.one TRANSITIONEND, =>
            @trigger "removed", @

            super

        @$el.removeClass "opened"

        super

    executeAction: (e) ->
        return if e.isDefaultPrevented()

        if (action = ($el = $ e.currentTarget).data "action") and action of this
            e.preventDefault()

            this[action].call this, $el

        return

Cruddy.Entity.Form.display = (instance) ->
    form = new Cruddy.Entity.Form model: instance

    $(document.body).append form.render().$el

    after_break => form.show()

    return form
Cruddy.Inputs = {}

# Base class for input that will be bound to a model's attribute.
class Cruddy.Inputs.Base extends Cruddy.View
    constructor: (options) ->
        @key = options.key

        super

    initialize: ->
        @listenTo @model, "change:" + @key, (model, value, { input }) ->
            @handleValueChanged value, input is this

        this

    # Apply changes when model's attribute changed.
    # external is true when value is changed not by input itself.
    handleValueChanged: (newValue, bySelf) -> this

    render: -> @handleValueChanged @getValue(), no

    # Focus an element.
    focus: -> this

    # Get current value.
    getValue: -> @model.get @key

    # Set current value.
    setValue: (value, options = {}) ->
        options.input = this

        @model.set @key, value, options

        this

    emptyValue: -> null

    empty: -> @setValue @emptyValue()
# Renders formatted text and doesn't have any editing features.
class Cruddy.Inputs.Static extends Cruddy.Inputs.Base
    tagName: "p"
    className: "form-control-static"

    initialize: (options) ->
        @formatter = options.formatter if options.formatter?

        super

    handleValueChanged: -> @render()

    render: ->
        value = @getValue()
        value = @formatter.format value if @formatter?

        @$el.html value

        this
class Cruddy.Inputs.BaseText extends Cruddy.Inputs.Base
    className: "form-control"

    events:
        "change": "submitValue"
        "keydown": "handleKeydown"

    handleKeydown: (e) ->
        # Ctrl + Enter
        return @submitValue() if e.ctrlKey and e.keyCode is 13

        this

    disable: ->
        @$el.prop "disabled", yes

        this

    enable: ->
        @$el.prop "disabled", no

        this

    submitValue: -> @setValue @$el.val()

    handleValueChanged: (newValue, bySelf) ->
        @$el.val newValue unless bySelf

        this

    focus: ->
        @el.focus()

        this

# Renders an <input> value of which is bound to a model's attribute.
class Cruddy.Inputs.Text extends Cruddy.Inputs.BaseText
    tagName: "input"

    initialize: (options) ->
        # Apply mask
        options.mask and @$el.mask options.mask

        super

# Renders a <textarea> input.
class Cruddy.Inputs.Textarea extends Cruddy.Inputs.BaseText
    tagName: "textarea"
# Renders a checkbox
class Cruddy.Inputs.Checkbox extends Cruddy.Inputs.Base
    tagName: "label"

    events:
        "change :checkbox": "handleCheckboxChanged"

    initialize: (options) ->
        @label = options.label || null

        super

    handleCheckboxChanged: -> @setValue @input.prop "checked"

    handleValueChanged: (newValue, bySelf) ->
        @input.prop "checked", newValue unless bySelf

        this

    render: ->
        @input = $ "<input>", { type: "checkbox", checked: @getValue() }
        @$el.append @input
        @$el.append @label if @label?

        this
class Cruddy.Inputs.Boolean extends Cruddy.Inputs.Base
    events:
        "click .btn": "check"

    initialize: (options) ->
        @tripleState = options.tripleState ? false

        super

    check: (e) ->
        currentValue = @getValue()
        value = !!$(e.target).data "value"

        value = null if value is currentValue and @tripleState

        @setValue value

    handleValueChanged: (value) ->
        value = switch value
            when yes then 0
            when no then 1
            else null

        @values.removeClass "active"
        @values.eq(value).addClass "active" if value?

        this

    render: ->
        @$el.html @template()

        @values = @$ ".btn"

        super

    template: ->
        """
        <div class="btn-group">
            <button type="button" class="btn btn-default" data-value="1">#{ Cruddy.lang.yes }</button>
            <button type="button" class="btn btn-default" data-value="0">#{ Cruddy.lang.no }</button>
        </div>
        """

    focus: ->
        @values?[0].focus()

        this
class Cruddy.Inputs.EntityDropdown extends Cruddy.Inputs.Base
    className: "entity-dropdown"

    events:
        "click .ed-item>.input-group-btn>.btn-remove": "removeItem"
        "click .ed-item>.input-group-btn>.btn-edit": "editItem"
        "click .ed-item>.form-control": "executeFirstAction"
        "keydown .ed-item>.form-control": "handleItemKeydown"
        "keydown [type=search]": "handleSearchKeydown"
        "show.bs.dropdown": "handleDropdownShow"

        "shown.bs.dropdown": ->
            after_break => @selector.focus()

            this

        "hide.bs.dropdown": (e) ->
            @opened = no

            return

    initialize: (options) ->
        @multiple = options.multiple if options.multiple?
        @reference = options.reference if options.reference?
        @owner = options.owner if options.owner?

        # Whether to show edit button (pencil)
        @allowEdit = options.allowEdit ? yes and @reference.updatePermitted()

        @placeholder = options.placeholder ? Cruddy.lang.not_selected

        # Whether the drop down is enabled
        @enabled = options.enabled ? true

        # Whether the item is currently editing
        @editing = false

        # Whether to not allow to open a dropdown
        @disableDropdown = false

        # Whether the dropdown is opened
        @opened = false

        if options.constraint
            @constraint = options.constraint
            @listenTo @model, "change:" + @constraint.field, -> @checkToDisable().applyConstraint yes

        super

    getKey: (e) -> $(e.currentTarget).closest(".ed-item").data "key"

    getValue: -> super or if @multiple then [] else null

    removeItem: (e) ->
        if @multiple
            i = @getKey e
            value = _.clone @getValue()
            value.splice i, 1
        else
            value = null

        @setValue value

    executeFirstAction: (e) ->
        $(".btn:not(:disabled):last", $(e.currentTarget).next()).trigger "click"

        return false

    editItem: (e) ->
        return if @editing or not @allowEdit

        item = @model.get @key
        item = item[@getKey e] if @multiple

        return if not item

        btn = $(e.currentTarget)

        # We'll look for the button if it is form control that was clicked
        btn = btn.next().children(".btn-edit") if btn.is ".form-control"

        btn.prop "disabled", yes

        @editing = @reference.load(item.id).done (instance) =>
            @editingForm = form = Cruddy.Entity.Form.display instance

            form.once "saved", (model) =>
                btn.parent().siblings(".form-control").html @itemBody model.meta.simplified
                form.remove()

                @selector?.dataSource?.refresh()

            form.once "destroyed", (model) => @removeItem e
            form.once "remove", => @editingForm = null

        @editing.always =>
            @editing = null
            btn.prop "disabled", no

        this

    handleSearchKeydown: (e) ->
        if (e.keyCode is 27)
            @selector.$el.dropdown "toggle" if @selector

            return false

        return

    handleItemKeydown: (e) ->
        if (e.keyCode is 13)
            @executeFirstAction e

            return false

        return

    applyConstraint: (reset = no) ->
        if @selector
            field = @model.entity.getField @constraint.field
            value = @model.get @constraint.field
            @selector.dataSource?.set "constraint", field.prepareAttribute value
            @selector.attributesForNewModel[@constraint.otherField] = value

        @model.set(@key, if @multiple then [] else null) if reset

        this

    checkToDisable: ->
        if not @enabled or @constraint and _.isEmpty(@model.get @constraint.field) then @disable() else @enable()

        this

    disable: ->
        return this if @disableDropdown

        @disableDropdown = yes

        @toggleDisableControls()

    enable: ->
        return this if not @disableDropdown

        @disableDropdown = no

        @toggleDisableControls()

    toggleDisableControls: ->
        @dropdownBtn.prop "disabled", @disableDropdown
        @$el.toggleClass "disabled", @disableDropdown

        this

    handleDropdownShow: (e) ->
        if @disableDropdown
            e.preventDefault()

            return

        @opened = yes

        if not @selector
            @selector = new Cruddy.Inputs.EntitySelector
                model: @model
                key: @key
                multiple: @multiple
                reference: @reference
                allowCreate: @allowEdit
                owner: @owner

            @applyConstraint() if @constraint

            @$el.append @selector.render().el

        @toggleOpenDirection()

        return

    toggleOpenDirection: ->
        return if not @opened

        wnd = $(window)
        space = wnd.height() - @$el.offset().top - wnd.scrollTop() - @$el.parent(".field-list").scrollTop()

        targetClass = if space > 292 then "open-down" else "open-up"

        @$el.removeClass("open-up open-down").addClass targetClass if not @$el.hasClass targetClass

        this

    handleValueChanged: ->
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

        @dropdownBtn = @$ "##{ @cid }-dropdown"

        @$el.attr "id", @cid

        @checkToDisable()

        this

    renderMultiple: ->
        @$el.append @items = $ "<div>", class: "items"

        @$el.append """
            <button type="button" class="btn btn-default btn-block dropdown-toggle ed-dropdown-toggle" data-toggle="dropdown" id="#{ @cid }-dropdown" data-target="##{ @cid }">
                #{ Cruddy.lang.choose }
                <span class="caret"></span>
            </button>
            """ if @enabled

        @renderItems()

    renderItems: ->
        html = ""
        html += @itemTemplate @itemBody(value), key for value, key in @getValue()
        @items.html html
        @items.toggleClass "has-items", html isnt ""

        this

    renderSingle: ->
        @$el.html @itemTemplate @placeholder, "0"

        @itemTitle = @$ ".form-control"
        @itemDelete = @$ ".btn-remove"
        @itemEdit = @$ ".btn-edit"

        @updateItem()

    updateItem: ->
        value = @getValue()

        @itemTitle.html if value then @itemBody(value) else @placeholder

        @itemDelete.toggle !!value
        @itemEdit.toggle !!value

        this

    itemBody: (item) ->
        unless item.body
            data = @selector.dataSource.getById item.id

            item = data if data

        return item.body if item.body

        return item.id

    itemTemplate: (value, key = null) ->
        html = """
            <div class="input-group ed-item #{ if not @multiple then "ed-dropdown-toggle" else "" }" data-key="#{ key }">
                <div class="form-control">#{ value }</div>
            """

        html += """
            <div class="input-group-btn">
                #{ buttons }
            </div>
            """ if not _.isEmpty buttons = @buttonsTemplate()

        html += "</div>"

    buttonsTemplate: ->
        html = ""

        html += """
            <button type="button" class="btn btn-default btn-remove" tabindex="-1" title="#{ Cruddy.lang.reset }">
                #{ b_icon "remove" }
            </button>
            """ if @enabled

        html += """
            <button type="button" class="btn btn-default btn-edit" tabindex="-1" title="#{ Cruddy.lang.edit }">
                #{ b_icon "pencil" }
            </button>
            """ if @allowEdit

        html += """
            <button type="button" class="btn btn-default btn-dropdown dropdown-toggle" data-toggle="dropdown" id="#{ @cid }-dropdown" data-target="##{ @cid }" tab-index="1" title="#{ Cruddy.lang.list_show }">
                #{ b_icon "chevron-down" }
            </button>
            """ if not @multiple

        html

    focus: ->
        $el = @$component("dropdown")
        $el = $el.parent().prev() if not @multiple

        $el[0].focus()

        $el.trigger("click") if _.isEmpty @getValue()

        this

    emptyValue: -> if @multiple then [] else null

    dispose: ->
        @selector?.remove()
        @editingForm?.remove()

        this

    remove: ->
        @dispose()

        super
class Cruddy.Inputs.EntitySelector extends Cruddy.Inputs.Base
    className: "entity-selector"

    events:
        "click .items>.item": "checkItem"
        "click .more": "loadMore"
        "click .btn-add": "showNewForm"
        "click .btn-refresh": "refresh"
        "click [type=search]": -> false

    initialize: (options) ->
        super

        @filter = options.filter ? false
        @multiple = options.multiple ? false
        @reference = options.reference

        @allowSearch = options.allowSearch ? yes
        @allowCreate = options.allowCreate ? yes and @reference.createPermitted()

        @attributesForNewModel = {}

        @makeSelectedMap @getValue()

        if @reference.readPermitted()
            @dataSource = @reference.search ajaxOptions: data: owner: options.owner

            @listenTo @dataSource, "request", @displayLoading
            @listenTo @dataSource, "data",    @renderItems

        this

    getValue: -> super or if @multiple then [] else null

    displayLoading: (dataSource, xhr) ->
        @$el.addClass "loading"

        xhr.always => @$el.removeClass "loading"

        this

    maybeLoadMore: ->
        height = @items.parent().height()

        @loadMore() if @$more? and height > 0 and height + 50 > @$more.position().top

        this

    refresh: (e) ->
        if e
            e.preventDefault()
            e.stopPropagation()

        @dataSource.refresh()

        return

    checkItem: (e) ->
        e.preventDefault()
        e.stopPropagation()

        @selectItem @dataSource.getById $(e.currentTarget).data("id")

        return

    selectItem: (item) ->
        return if not item

        if @multiple
            if item.id of @selected
                value = _.filter @getValue(), (_item) -> _item.id.toString() != item.id.toString()
            else
                value = _.clone @getValue()
                value.push item
        else
            value = item

        @setValue value

    loadMore: ->
        return if not @dataSource or @dataSource.inProgress()

        @dataSource.next()

        false

    showNewForm: (e) ->
        if e
            e.preventDefault()
            e.stopPropagation()

        return if @newModelForm

        instance = @reference.createInstance attributes: @attributesForNewModel

        @newModelForm = form = Cruddy.Entity.Form.display instance

        form.once "remove", => @newModelForm = null

        form.once "created", (model, resp) =>
            @selectItem model.meta.simplified

            form.remove()

            @dataSource?.refresh()

            return

        this

    handleValueChanged: (newValue) ->
        @makeSelectedMap newValue
        @renderItems()

    makeSelectedMap: (data) ->
        @selected = {}

        return this unless data

        if @multiple
            @selected[item.id] = yes for item in data
        else
            @selected[data.id] = yes if data?

        this

    renderItems: ->
        @$more = null

        html = ""

        if @dataSource.data.length or @dataSource.more
            html += @renderItem item for item in @dataSource.data

            html += """<li class="more">#{ Cruddy.lang.more }</li>""" if @dataSource.more
        else
            html += """<li class="empty">#{ Cruddy.lang.no_results }</li>"""

        @items.html html

        if @dataSource.more
            @$more = @items.children ".more"
            @maybeLoadMore()

        this

    renderItem: (item) ->
        className = if item.id of @selected then "selected" else ""

        """<li class="item #{ className }" data-id="#{ item.id }">#{ item.body }</li>"""

    render: ->
        if @reference.readPermitted()
            @dispose()

            @$el.html @template()

            @items = @$ ".items"

            @renderItems()

            @items.parent().on "scroll", $.proxy this, "maybeLoadMore"

            @renderSearch() if @allowSearch

            @dataSource.refresh() if @dataSource.isEmpty()
        else
            @$el.html "<span class=error>#{ Cruddy.lang.forbidden }</span>"

        this

    renderSearch: ->
        @searchInput = new Cruddy.Inputs.Search
            model: @dataSource
            key: "keywords"

        @$el.prepend @searchInput.render().$el

        @searchInput.$el.wrap "<div class=search-input-container></div>"

        @searchInput.appendButton """
            <button type="button" class="btn btn-default btn-refresh" tabindex="-1" title="#{ Cruddy.lang.refresh }">
                #{ b_icon "refresh" }
            </button>
        """

        @searchInput.appendButton """
            <button type="button" class='btn btn-default btn-add' tabindex='-1' title="#{ Cruddy.lang.model_new_record }">
                #{ b_icon "plus" }
            </button>
        """ if @allowCreate

        this

    template: -> """<div class="items-container"><ul class="items"></ul></div>"""

    focus: ->
        @searchInput?.focus() or @entity.done => @searchInput.focus()

        this

    dispose: ->
        @searchInput?.remove()
        @newModelForm?.remove()

        this

    remove: ->
        @dispose()

        super

class Cruddy.Inputs.FileList extends Cruddy.Inputs.Base
    className: "file-list"

    events:
        "change [type=file]": "handleFilesChanged"
        "click .action-delete": "handleDeleteFile"

    initialize: (options) ->
        @multiple = options.multiple ? false
        @storage = Cruddy.FileStorage.instance options.storage
        @counter = 1

        @queue = @storage.getUploadQueue()

        @listenTo @queue, "started", -> @$el.addClass "--loading"
        @listenTo @queue, "stopped", -> @$el.removeClass "--loading"

        @listenTo @queue, "progress", (stats) ->
            @$uploadProgress.css "width", "#{ stats.uploaded / stats.total * 100}%"

        @listenTo @queue, "filestarted", (file) ->
            @$uploadProgress.text file.file.name

        @listenTo @queue, "filecompleted", (resp, file) ->
            @pushFile resp.path

        @listenTo @queue, "fileinvalid", (message, code) ->
            @showUploadError Cruddy.lang["upload_error_#{ code }"] || Cruddy.lang.upload_failed

        @listenTo @queue, "fileerror", () ->
            @showUploadError Cruddy.lang.upload_failed

        super

    showUploadError: (message) ->
        @$uploadError.text(message).show()

        setTimeout (=> @$uploadError.hide()), 3000

        return this

    pushFile: (file) ->
        if @multiple
            value = _.clone @getValue()
            value.push file
        else
            value = file

        @setValue value

    removeFile: (file) ->
        if @multiple
            @setValue _.reject @getValue(), (item) -> item is file
        else
            @setValue null

        return this

    handleDeleteFile: (e) ->
        e.preventDefault();

        @removeFile $(e.currentTarget).data "file"

    handleFilesChanged: (e) ->
        return unless e.target.files.length

        @queue.push(e.target.files).start();

        return this

    handleValueChanged: -> @render()

    render: ->
        html = ""

        unless _.isEmpty(files = @getValue())
            files = if _.isArray(files) then files else [ files ]
            html += @renderFile file for file in files

        html = @wrapItems html unless _.isEmpty html

        html += @renderInput()

        @$el.html html

        @$uploadProgress = @$component "progress"
        @$uploadError = @$component "error"

        this

    wrapItems: (html) -> """<ul class="list-group">#{ html }</ul>"""

    renderInput: ->
        if @multiple
            label = "#{ b_icon "plus" } #{ Cruddy.lang.add }"
        else
            label = Cruddy.lang.choose

        """
        <div class="progress">
            <div class="progress-bar" id="#{ @componentId "progress" }"></div>
        </div>

        <div class="help-block error" id="#{ @componentId "error" }" style="display:none;"></div>

        <div class="btn btn-sm btn-default file-list-input-wrap">
            <input type="file" id="#{ @componentId "input" }" accept="#{ @getAccept() }" #{ value_if @multiple, "multiple" }>
            #{ label }
        </div>
        """

    renderFile: (file) -> """
        <li class="list-group-item">
            <a href="#" class="action-delete pull-right" data-cid="#{ file }">
                <span class="glyphicon glyphicon-remove"></span>
            </a>

            <a href="#{ @storage.url file }" target="_blank">#{ file }</a>
        </li>
    """

    focus: ->
        @$component("input")[0].focus()

        this

    getAccept: -> ""
class Cruddy.Inputs.ImageList extends Cruddy.Inputs.FileList
    className: "file-list --images"

    initialize: (options) ->
        @width = options.width ? 0
        @height = options.height ? 80

        super

    wrapItems: (html) -> """<ul class="image-group">#{ html }</ul>"""

    renderFile: (file) -> """
        <li class="image-group-item">
            #{ @renderImage file }

            <a href="#" class="action-delete" data-cid="#{ file }">
                <span class="glyphicon glyphicon-remove"></span>
            </a>
        </li>
        """

    renderImage: (file) -> """
        <a href="#{ @storage.url file }" target="_blank" class="img-wrap" data-trigger="fancybox">
            <img src="#{ @storage.url file, { width: @width, height: @height } }">
        </a>
    """

    getAccept: -> "image/*,image/jpeg,image/png,image/gif,image/jpeg"
# Search input implements "change when type" and also allows to clear text with Esc
class Cruddy.Inputs.Search extends Cruddy.View
    className: "input-group"

    events:
        "click .btn-search": "handleBtnClick"

    initialize: (options) ->
        @input = new Cruddy.Inputs.Text
            model: @model
            key: options.key
            attributes:
                type: "search"
                placeholder: Cruddy.lang.search

        super

    handleBtnClick: (e) ->
        e.preventDefault()
        e.stopPropagation()

        @input.submitValue()

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
class Cruddy.Inputs.Slug extends Cruddy.Inputs.Base
    events:
        "click .btn": "toggleSyncing"

    constructor: (options) ->
        @input = new Cruddy.Inputs.Text _.clone options

        options.className ?= "input-group"

        delete options.attributes if options.attributes?

        super

    initialize: (options) ->
        @ref = if _.isArray(options.field) then options.field else [options.field] if options.field

        @$el.removeClass "input-group" unless @ref

        super

    handleValueChanged: ->
        @unlink() unless @linkable()

        return this

    toggleSyncing: ->
        if @syncButton.hasClass "active" then @unlink() else @link()

        this

    link: ->
        return unless @ref

        @listenTo @model, "change:" + @ref.join(" change:"), @sync
        @syncButton.addClass "active"

        @input.disable()

        @sync()

    unlink: ->
        @stopListening @model, null, @sync if @ref?
        @syncButton.removeClass "active"

        @input.enable()

        this

    linkable: ->
        modelValue = @getValue()
        value = @getSlug()

        return value == modelValue

    sync: -> @setValue @getSlug()

    getSlug: ->
        components = []

        for key in @ref
            refValue = @model.get key
            components.push refValue if refValue

        if components.length then components.join " " else null

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
            <button type="button" tabindex="-1" class="btn btn-default" title="#{ Cruddy.lang.slug_sync }"><span class="glyphicon glyphicon-link"></span></button>
        </div>
        """
class Cruddy.Inputs.Select extends Cruddy.Inputs.BaseText
    tagName: "select"

    initialize: (options) ->
        @items = options.items ? {}
        @prompt = options.prompt ? null
        @required = options.required ? no
        @multiple = options.multiple ? no

        @$el.attr "multiple", "multiple" if @multiple

        super

    render: ->
        @$el.html @template()

        @setValue @$el.val() if @required and not @getValue()

        super

    handleValueChanged: (newValue, bySelf) ->
        @$el.val @_transformValue newValue unless bySelf

        this

    _transformValue: (value) ->
        return @emptyValue() if (_.isString(value) or _.isArray(value)) and not value.length

        if _.isArray value
            if @multiple then value else value[0]
        else
            if @multiple then [ value ] else value

    emptyValue: -> if @multiple then [] else null

    template: ->
        html = ""
        html += @optionTemplate "", @prompt ? Cruddy.lang.not_selected, @required if @hasPrompt()
        html += @optionTemplate key, value for key, value of @items
        html

    optionTemplate: (value, title, disabled = no) -> """
        <option value="#{ _.escape value }" #{ value_if disabled, "disabled" }>
            #{ _.escape title }
        </option>
    """

    hasPrompt: -> not @multiple and (not @required or @prompt)
class Cruddy.Inputs.NumberFilter extends Cruddy.Inputs.Base
    className: "input-group number-filter"

    events:
        "click .dropdown-menu a": "handleOperatorSelected"
        "change": "handleInputChanged"

    initialize: ->
        @defaultOp = ">"

        @setValue @emptyValue(), silent: yes if not @getValue()

        super

    handleOperatorSelected: (e) ->
        e.preventDefault()

        op = $(e.currentTarget).data "op"
        value = @getValue()

        @setValue @makeValue op, value.val if value.op isnt op

        this

    handleInputChanged: (e) ->
        value = @getValue()

        @setValue @makeValue value.op, e.target.value

        this

    handleValueChanged: (value, external) ->
        @$(".dropdown-menu li").removeClass "active"
        @$(".dropdown-menu a[data-op='#{ value.op }']").parent().addClass "active"

        @op.text value.op
        @input.val value.val if external

        this

    render: ->
        @$el.html @template()

        @op = @$component "op"
        @input = @$component "input"
        @reset = @$component "reset"

        super

    template: -> """
        <div class="input-group-btn">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                <span id="#{ @componentId("op") }" class="value">=</span>
                <span class="caret"></span>
            </button>

            <ul class="dropdown-menu">
                <li><a href="#" data-op="=">=</a></li>
                <li><a href="#" data-op="&gt;">&gt;</a></li>
                <li><a href="#" data-op="&lt;">&lt;</a></li>
            </ul>
        </div>

        <input type="text" class="form-control" id="#{ @componentId "input" }">
    """

    makeValue: (op, val) -> { op: op, val: val }

    emptyValue: -> @makeValue @defaultOp, ""
class Cruddy.Inputs.DateTime extends Cruddy.Inputs.BaseText
    tagName: "input"

    initialize: (options) ->
        @format = options.format

        @$el.mask options.mask if options.mask?

        super

    handleValueChanged: (newValue, bySelf) ->
        @$el.val if newValue is null then "" else moment.unix(newValue).format @format unless bySelf

        this

    submitValue: ->
        value = @$el.val()
        value = if _.isEmpty value then null else moment(value, @format).unix()

        @setValue value

        # We will always set input value because it may not be always parsed properly
        @handleValueChanged value, yes
class Cruddy.Fields.BaseView extends Cruddy.Layout.Element

    constructor: (options) ->
        @field = field = options.field
        model = options.model

        @inputId = [ model.entity.id, field.id, model.cid ].join "__"

        className = "form-group field field__#{ field.getType() } field--#{ field.id } field--#{ model.entity.id }--#{ field.id }"

        @className = if @className then className + " " + @className else className

        @forceDisable = options.forceDisable ? false

        super

    initialize: (options) ->
        @listenTo @model, "sync",    @handleSync
        @listenTo @model, "request", @handleRequest
        @listenTo @model, "invalid", @handleInvalid

        @updateContainer()

    handleSync: -> @updateContainer()

    handleRequest: -> @hideError()

    handleInvalid: (model, errors) ->
        if @field.id of errors
            error = errors[@field.id]

            @showError if _.isArray error then _.first error else error

        this

    updateContainer: ->
        @isEditable = not @forceDisable and @field.isEditable(@model)

        @$el.toggle @isVisible()
        @$el.toggleClass "required", @field.isRequired @model

        this

    hideError: ->
        @error.hide()

        this

    showError: (message) ->
        @error.text(message).show()

        @handleValidationError message

        return this

    focus: -> this

    render: ->
        @$(".field-help").tooltip
            container: "body"
            placement: "left"

        @error = @$component "error"

        this

    helpTemplate: ->
        help = @field.getHelp()
        if help then """<span class="glyphicon glyphicon-question-sign field-help" title="#{ _.escape help }"></span>""" else ""

    errorTemplate: -> """<span class="help-block error" style="display:none;" id="#{ @componentId "error" }"></span>"""

    # Get whether the view is visible
    # The field is not visible when model is new and field is not editable or computed
    isVisible: -> @isEditable or not @model.isNew()

    isFocusable: -> @field.isEditable @model

    dispose: -> this

    remove: ->
        @dispose()

        super
# This is basic field view that will render in bootstrap's vertical form style.
class Cruddy.Fields.InputView extends Cruddy.Fields.BaseView

    updateContainer: ->
        isEditable = @isEditable

        super

        @render() if isEditable? and isEditable isnt @isEditable


    hideError: ->
        @$el.removeClass "has-error"

        super

    showError: ->
        @$el.addClass "has-error"

        super

    # Render a field
    render: ->
        @dispose()

        @$el.html @template()

        @input = @field.createInput @model, @inputId, @forceDisable

        @$el.append @input.render().el

        @$el.append @errorTemplate()

        super

    label: (label) ->
        label ?= @field.getLabel()

        """
        <label for="#{ @inputId }" class="field-label">
            #{ @helpTemplate() }#{ _.escape label }
        </label>
        """

    # The default template that is shown when field is editable.
    template: -> @label()

    # Focus the input that this field view holds.
    focus: ->
        @input?.focus()

        this

    dispose: ->
        @input?.remove()

        this
class Cruddy.Fields.Base extends Cruddy.Attribute

    viewConstructor: Cruddy.Fields.InputView

    # Create a view that will represent this field in field list
    createView: (model, forceDisable = no, parent) -> new @viewConstructor { model: model, field: this, forceDisable: forceDisable }, parent

    # Create an input that is used by default view
    createInput: (model, inputId, forceDisable = no) ->
        input = @createEditableInput model, inputId if not forceDisable and @isEditable(model)

        input or @createStaticInput(model)

    # Create an input that will display a static value without possibility to edit
    createStaticInput: (model) -> new Cruddy.Inputs.Static
        model: model
        key: @id
        formatter: this

    # Create an input that is used when field is editable
    createEditableInput: (model, inputId) -> null

    # Create filter input that
    createFilterInput: (model) -> null

    # Get a label for filter input
    getFilterLabel: -> @attributes.label

    # Format value as static text
    format: (value) -> value or NOT_AVAILABLE

    # Get field's label
    getLabel: -> @attributes.label

    # Get whether the field is editable for specified model
    isEditable: (model) -> model.canBeSaved() and @attributes.disabled isnt yes and @attributes.disabled isnt model.action()

    # Get whether field is required
    isRequired: (model) -> @attributes.required is yes or @attributes.required == model.action()

    # Get whether the field is unique
    isUnique: -> @attributes.unique

    hasChangedSinceSync: (model) -> not @valuesEqual model.get(@id), model.getOriginal(@id)

    valuesEqual: (a, b) -> a is b

    isCopyable: -> not @isUnique()

    copyAttribute: (model, copy) -> model.get @id

    parse: (model, value) -> value

    prepareAttribute: (value) -> value

    prepareFilterData: (value) -> @prepareAttribute value

    parseFilterData: (value) -> value
class Cruddy.Fields.Input extends Cruddy.Fields.Base

    createEditableInput: (model, inputId) ->
        input = @createBaseInput model, inputId

        if @attributes.prepend or @attributes.append
            return new Cruddy.Fields.Input.PrependAppendWrapper
                prepend: @attributes.prepend
                append: @attributes.append
                input: input

        return input

    createBaseInput: (model, inputId) -> new Cruddy.Inputs.Text
        model: model
        key: @id
        mask: @attributes.mask
        attributes:
            placeholder: @attributes.placeholder
            id: inputId
            type: @attributes.input_type or "input"

    createFilterInput: (model) -> @createBaseInput model

    format: (value) ->
        return NOT_AVAILABLE if value is null or value is ""

        value += " " + @attributes.append if @attributes.append
        value = @attributes.prepend + " " + value if @attributes.prepend

        return value

    prepareAttribute: (value) -> if _.isArray value then value.join "," else value

    getType: -> "string"


class Cruddy.Fields.Input.PrependAppendWrapper extends Cruddy.View
    className: "input-group"

    initialize: (options) ->
        @$el.append @createAddon options.prepend if options.prepend
        @$el.append (@input = options.input).$el
        @$el.append @createAddon options.append if options.append

    render: ->
        @input.render()

        return this

    createAddon: (text) -> "<span class=input-group-addon>" + _.escape(text) + "</span>"
class Cruddy.Fields.Text extends Cruddy.Fields.Base

    createEditableInput: (model, inputId) -> new Cruddy.Inputs.Textarea
        model: model
        key: @id
        attributes:
            placeholder: @attributes.placeholder
            id: inputId
            rows: @attributes.rows

    getType: -> "text"
class Cruddy.Fields.BaseDateTime extends Cruddy.Fields.Base

    inputFormat: null
    mask: null

    createEditableInput: (model, inputId) -> new Cruddy.Inputs.DateTime
        model: model
        key: @id
        format: @inputFormat
        mask: @mask
        attributes:
            id: @inputId

    formatDate: (value) -> moment.unix(value).format @inputFormat

    format: (value) -> if value is null then NOT_AVAILABLE else @formatDate value

    getType: -> "datetime"

class Cruddy.Fields.Date extends Cruddy.Fields.BaseDateTime
    inputFormat: "YYYY-MM-DD"
    mask: "9999-99-99"

class Cruddy.Fields.Time extends Cruddy.Fields.BaseDateTime
    inputFormat: "HH:mm:ss"
    mask: "99:99:99"

class Cruddy.Fields.DateTime extends Cruddy.Fields.BaseDateTime
    inputFormat: "YYYY-MM-DD HH:mm:ss"
    mask: "9999-99-99 99:99:99"

    formatDate: (value) -> moment.unix(value).fromNow()
class Cruddy.Fields.Boolean extends Cruddy.Fields.Base

    createEditableInput: (model) -> new Cruddy.Inputs.Boolean
        model: model
        key: @id

    createFilterInput: (model) -> new Cruddy.Inputs.Boolean
        model: model
        key: @id
        tripleState: yes

    format: (value) -> if value then Cruddy.lang.yes else Cruddy.lang.no

    prepareAttribute: (value) ->
        return 0 if value is false
        return 1 if value is true

        return null

    parseFilterData: (value) ->
        value = parseInt value

        return true if value is 1
        return false if value is 0

        return null

    getType: -> "bool"
class Cruddy.Fields.BaseRelation extends Cruddy.Fields.Base

    isVisible: -> @getReferencedEntity().readPermitted() and super

    # Get the referenced entity
    getReferencedEntity: ->
        @reference = Cruddy.app.entity @attributes.reference if not @reference

        @reference

    getFilterLabel: -> @getReferencedEntity().getSingularTitle()

    formatItem: (item) -> item.body

    format: (value) ->
        return NOT_AVAILABLE if _.isEmpty value

        if @attributes.multiple then _.map(value, (item) => @formatItem item).join ", " else @formatItem value

    getType: -> "relation"
class Cruddy.Fields.Relation extends Cruddy.Fields.BaseRelation

    createInput: (model, inputId, forceDisable = no) -> new Cruddy.Inputs.EntityDropdown
        model: model
        key: @id
        multiple: @attributes.multiple
        reference: @getReferencedEntity()
        owner: @entity.id + "." + @id
        constraint: @attributes.constraint
        enabled: not forceDisable and @isEditable(model)

    createFilterInput: (model) -> new Cruddy.Inputs.EntityDropdown
        model: model
        key: @id
        reference: @getReferencedEntity()
        allowEdit: no
        placeholder: Cruddy.lang.any_value
        owner: @entity.id + "." + @id
        constraint: @attributes.constraint
        multiple: yes

    isEditable: -> @getReferencedEntity().readPermitted() and super

    canFilter: -> @getReferencedEntity().readPermitted() and super

    formatItem: (item) ->
        ref = @getReferencedEntity()

        return item.body unless ref.readPermitted()

        """<a href="#{ ref.link item.id }">#{ item.body }</a>"""

    prepareAttribute: (value) ->
        return null unless value?

        return _.pluck(value, "id").join(",") if _.isArray value

        return value.id

    prepareFilterData: (value) ->
        value = super

        if _.isEmpty value then null else value

    parseFilterData: (value) ->
        return null unless _.isString(value) or _.isNumber(value)

        value = value.toString()

        return null unless value.length

        value = value.split ","

        return _.map value, (value) -> { id: value }
class Cruddy.Fields.File extends Cruddy.Fields.Base

    createEditableInput: (model) -> new Cruddy.Inputs.FileList
        model: model
        key: @id
        multiple: @attributes.multiple
        storage: @attributes.storage

    getType: -> "file"
class Cruddy.Fields.Image extends Cruddy.Fields.File

    createEditableInput: (model) -> new Cruddy.Inputs.ImageList
        model: model
        key: @id
        width: @attributes.width
        height: @attributes.height
        multiple: @attributes.multiple
        storage: @attributes.storage

    createStaticInput: (model) -> new Cruddy.Inputs.Static
        model: model
        key: @id
        formatter: new Cruddy.Fields.Image.Formatter
            width: @attributes.width
            height: @attributes.height
            storage: @attributes.storage

    getType: -> "image"
class Cruddy.Fields.Image.Formatter

    constructor: (options) ->
        @options = { width: options.width || 0, height: options.height || 0 }
        @storage = Cruddy.FileStorage.instance options.storage

        return

    imageUrl: (image) -> @storage.url image

    imageThumb: (image) -> @storage.url image, @options

    format: (value) ->
        html = """<ul class="image-group">"""

        value = [ value ] if not _.isArray value

        for image in value
            html += """
                <li class="image-group-item">
                    <a href="#{ @imageUrl image }" class="img-wrap" target="_blank" data-trigger="fancybox">
                        <img src="#{ @imageThumb image }">
                    </a>
                </li>
            """

        return html + "</ul>"
class Cruddy.Fields.Slug extends Cruddy.Fields.Base

    createEditableInput: (model) -> new Cruddy.Inputs.Slug
        model: model
        key: @id
        field: @attributes.field

        attributes:
            placeholder: @attributes.placeholder

    getType: -> "slug"
class Cruddy.Fields.Enum extends Cruddy.Fields.Input

    createBaseInput: (model, inputId) -> new Cruddy.Inputs.Select
        model: model
        key: @id
        prompt: @attributes.prompt
        items: @attributes.items
        required: @attributes.required
        multiple: @attributes.multiple
        attributes:
            id: inputId

    createFilterInput: (model) -> new Cruddy.Inputs.Select
        model: model
        key: @id
        prompt: Cruddy.lang.any_value
        items: @attributes.items
        multiple: yes

    format: (value) ->
        items = @attributes.items

        value = [ value ] unless _.isArray value

        labels = ((if key of items then items[key] else key) for key in value)

        labels.join ", "

    parseFilterData: (value) -> if _.isString value then value.split "," else null

    getType: -> "enum"
class Cruddy.Fields.EmbeddedView extends Cruddy.Fields.BaseView
    className: "has-many-view"

    events:
        "click .btn-create": "create"

    initialize: (options) ->
        @views = {}

        @collection = collection = @model.get @field.id

        @listenTo collection, "add", @add
        @listenTo collection, "remove", @removeItem
        @listenTo collection, "removeSoftly restore", @update
        @listenTo collection, "reset", @render

        super

    handleInvalid: (model, errors) ->
        super if @field.id of errors and errors[@field.id].length

        this

    create: (e) ->
        e.preventDefault()
        e.stopPropagation()

        @collection.add @field.getReferencedEntity().createInstance(), focus: yes

        this

    add: (model, collection, options) ->
        itemOptions =
            model: model
            collection: @collection
            disable: not @isEditable

        @views[model.cid] = view = new Cruddy.Fields.EmbeddedItemView itemOptions, this

        @body.append view.render().el

        after_break( -> view.focus()) if options?.focus

        @focusable = view if not @focusable

        @update()

        this

    removeItem: (model) ->
        if view = @views[model.cid]
            view.remove()
            delete @views[model.cid]

        @update()

        this

    render: ->
        @dispose()

        @$el.html @template()
        @body = @$component "body"
        @createButton = @$ ".btn-create"

        @add model for model in @collection.models

        super

    update: ->
        @createButton.toggle @field.isMultiple() or @collection.hasSpots()

        this

    template: ->
        buttons = if @canCreate() then b_btn("", "plus", ["default", "create"]) else ""

        """
        <div class='header field-label'>
            #{ @helpTemplate() }#{ _.escape @field.getLabel() } #{ buttons }
        </div>
        <div class="error-container has-error">#{ @errorTemplate() }</div>
        <div class="body" id="#{ @componentId "body" }"></div>
        """

    canCreate: -> @isEditable and @field.getReferencedEntity().createPermitted()

    dispose: ->
        view.remove() for cid, view of @views
        @views = {}
        @focusable = null

        this

    remove: ->
        @dispose()

        super

    isFocusable: ->
        return no if not super

        return (@field.isMultiple() and @canCreate()) or (not @field.isMultiple() and @focusable?)

    focus: ->
        if @field.isMultiple() then @createButton[0]?.focus() else @focusable?.focus()

        this
class Cruddy.Fields.EmbeddedItemView extends Cruddy.Layout.Layout
    className: "has-many-item-view"

    events:
        "click .btn-toggle": "toggleItem"

    constructor: (options) ->
        @collection = options.collection

        @listenTo @collection, "restore removeSoftly", (m) ->
            return if m isnt @model

            @$container.toggle not @model.isDeleted
            @$btn.html @buttonContents()

        super

    toggleItem: (e) ->
        if @model.isDeleted then @collection.restore @model else @collection.removeSoftly @model

        return false

    buttonContents: ->
        if @model.isDeleted
            Cruddy.lang.restore
        else
            b_icon("trash") + " " + Cruddy.lang.delete

    setupDefaultLayout: ->
        @append new FieldList {}, this

        return this

    render: ->
        @$el.html @template()

        @$container = @$component "body"
        @$btn = @$component "btn"

        super

    template: ->
        html = """<div id="#{ @componentId "body" }"></div>"""

        if not @disabled and (@model.entity.deletePermitted() or @model.isNew())
            html += """
                <button type="button" class="btn btn-default btn-sm btn-toggle" id="#{ @componentId "btn" }">
                    #{ @buttonContents() }
                </button>
            """

        return html
class Cruddy.Fields.RelatedCollection extends Backbone.Collection

    model: Cruddy.Entity.Instance

    initialize: (items, options) ->
        @entity = options.entity
        @owner = options.owner
        @field = options.field
        @maxItems = options.maxItems

        # The flag is set when user has deleted some items
        @deleted = no
        @removedSoftly = 0

        @listenTo @owner, "sync", (model, resp, options) ->
            @deleted = no
            @_triggerItems "sync", {}, options

        @listenTo @owner, "request", (model, xhr, options) -> @_triggerItems "request", xhr, options
        @listenTo @owner, "invalid", @_handleInvalidEvent

        super

    _handleInvalidEvent: (model, errors) ->
        re = new RegExp("^"+@field.id+"\\.(\\d+)\\.(.+)$")
        innerErrors = {}

        for key, error of errors when match = key.match re
            cid = match[1]
            attr = match[2]

            (innerErrors[cid] = innerErrors[cid] || {})[attr] = error

        console.log re, innerErrors

        for cid, itemErrors of innerErrors when item = @get cid
            item.trigger "invalid", item, itemErrors

        return

    _triggerItems: (event, param1, param2) ->
        model.trigger event, model, param1, param2 for model in @models

        return

    add: ->
        @removeSoftDeleted() if @maxItems and @models.length >= @maxItems

        super

    removeSoftDeleted: -> @remove @filter((m) -> m.isDeleted)

    remove: (models) ->
        @deleted = yes

        if _.isArray models
            @removedSoftly-- for item in models when item.isDeleted
        else
            @removedSoftly-- if modes.isDeleted

        super

    removeSoftly: (m) ->
        return if m.isDeleted

        m.isDeleted = yes
        @removedSoftly++

        @trigger "removeSoftly", m

        return this

    restore: (m) ->
        return if not m.isDeleted

        m.isDeleted = no
        @removedSoftly--

        @trigger "restore", m

        return this

    hasSpots: (num = 1)-> not @maxItems? or @models.length - @removedSoftly + num <= @maxItems

    hasChangedSinceSync: ->
        return yes if @deleted or @removedSoftly
        return yes for item in @models when item.hasChangedSinceSync()

        no

    copy: (copy) ->
        items = if @field.isUnique() then [] else (item.copy() for item in @models)

        new Cruddy.Fields.RelatedCollection items,
            owner: copy
            field: @field

    serialize: ->
        models = @filter (model) -> model.canBeSaved()

        data = {}
        data[item.cid] = item.serialize() for item in models

        return data
class Cruddy.Fields.Embedded extends Cruddy.Fields.BaseRelation

    viewConstructor: Cruddy.Fields.EmbeddedView

    parse: (model, items) ->
        return items if items instanceof Cruddy.Fields.RelatedCollection

        items = [ items ] if items and not _.isArray(items)

        items = items or []

        # create default item if no data is available and field is required
        items.push {} if _.isEmpty(items) and @isRequired(model)

        ref = @getReferencedEntity()

        items = (ref.createInstance item for item in items)

        if collection = model.get @id
            collection.reset items

            return collection

        return new Cruddy.Fields.RelatedCollection items,
            entity: ref
            owner: model
            field: this
            maxItems: if @isMultiple() then null else 1

    hasChangedSinceSync: (model) -> model.get(@id).hasChangedSinceSync()

    copy: (copy, items) -> items.copy(copy)

    isMultiple: -> @attributes.multiple

    copyAttribute: (model, copy) -> model.get(@id).copy(copy)

    prepareAttribute: (value) -> if value then value.serialize() else null

    isCopyable: -> yes

    getType: -> "inline-relation"
class Cruddy.Fields.Number extends Cruddy.Fields.Input

    createFilterInput: (model) -> new Cruddy.Inputs.NumberFilter
        model: model
        key: @id

    prepareFilterData: (value) ->
        return null if _.isEmpty value.val

        return (if value.op is "=" then "" else value.op) + value.val

    parseFilterData: (value) ->
        op = ">"
        val = null

        if _.isString(value) and value.length
            op = value[0]
            if op in [ "=", "<", ">" ]
                val = value.substr 1
            else
                op = "="
                val = value

        else if _.isNumber value
            op = "="
            val = value

        return op: op, val: val

    getType: -> "number"
class Cruddy.Fields.Computed extends Cruddy.Fields.Base
    createInput: (model) -> new Cruddy.Inputs.Static { model: model, key: @id, formatter: this }

    isEditable: -> false

    getType: -> "computed"
class Cruddy.Columns.Base extends Cruddy.Attribute

    render: (item) -> @format item.attributes[@id]

    # Return value's text representation
    format: (value) -> value

    # Get column's header text
    getHeader: -> @attributes.header

    # Get column's class name
    getClass: -> "col-" + @id + if @canOrder() then " col__sortable" else ""

    # Get whether a column can order items
    canOrder: -> @attributes.can_order
class Cruddy.Columns.Proxy extends Cruddy.Columns.Base

    initialize: (attributes) ->
        field = attributes.field ? attributes.id
        @field = attributes.entity.fields.get field

        super

    format: (value) -> @field.format value

    getClass: -> super + " col__" + @field.getType()
class Cruddy.Columns.Computed extends Cruddy.Columns.Base
    getClass: -> super + " col__computed"
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
class Cruddy.Columns.DeleteButton extends Cruddy.Columns.Base

    id: "__deleteButton"

    getHeader: -> ""

    getClass: -> "col__delete-button col__button col__auto-hide"

    canOrder: -> false

    render: (item) -> """
        <a href="#" data-action="deleteItem" class="btn btn-default btn-xs auto-hide-target">
            #{ b_icon "trash" }
        </a>
    """
class Cruddy.Filters.Base extends Cruddy.Attribute

    getLabel: -> @attributes.label

    getClass: -> "filter filter__" + @attributes.type + " filter--" + @id

    createFilterInput: -> throw "Implement required"

    prepareData: (value) -> value

    parseData: (value) -> value

    getDataKey: -> @get("data_key") or @id
class Cruddy.Filters.Proxy extends Cruddy.Filters.Base

    initialize: (attributes) ->
        field = attributes.field ? attributes.id
        @field = attributes.entity.fields.get field

        super

    createFilterInput: (model) -> @field.createFilterInput model

    prepareData: (value) -> @field.prepareFilterData value

    parseData: (value) -> @field.parseFilterData value
# Backend application file

class App extends Backbone.Model

    initialize: ->
        @container = $ "body"
        @mainContent = $ "#content"
        @loadingRequests = 0
        @entities = {}
        @dfd = $.Deferred()

        @$title = $ "title"

        @$error = $(@errorTemplate()).appendTo @container

        @$error.on "click", ".close", => @$error.stop(yes).fadeOut()

        @on "change:entity", @displayEntity, this

        $(document).ajaxError (event, xhr, xhrOptions) => @handleAjaxError xhr, xhrOptions
        $(window).on "beforeunload", => @pageUnloadConfirmationMessage()

        this

    errorTemplate: -> """
        <p class="alert alert-danger cruddy-global-error">
            <button type="button" class="close">&times;</button>
            <span class="data"></span>
        </p>
    """

    init: ->
        @_loadSchema()

        return this

    ready: (callback) -> @dfd.done callback

    _loadSchema: ->
        req = $.ajax
            url: Cruddy.schemaUrl
            displayLoading: yes

        req.done (resp) =>
            for entity in resp
                modelClass = get(entity.model_class) or Cruddy.Entity.Entity

                @entities[entity.id] = new modelClass entity

            @dfd.resolve @

            $(document).trigger "started.cruddy", @

            return

        req.fail =>
            @dfd.reject()

            @displayError Cruddy.lang.schema_failed

            return

        return req

    displayEntity: (model, entity) ->
        @dispose()

        @mainContent.hide()

        @container.append (@entityView = entity.createController()).render().el if entity

        @updateTitle()

    displayError: (error) ->
        @dispose()
        @mainContent.html("<p class='alert alert-danger'>#{ error }</p>").show()

        this

    displayActionResult: (result) ->
        console.log result

        message = result.message

        unless message
            message = if result.successful then Cruddy.lang.action_applied else Cruddy.lang.action_failed

        #method = if result.actionResponse.successful then "success" else "error"

        #@popup[method].call message
        alert message

        return this

    handleAjaxError: (xhr) ->
        return if xhr.status is VALIDATION_FAILED_CODE

        if xhr.responseJSON?.error
            if _.isObject error = xhr.responseJSON.error
                error = error.type + ": " + error.message
        else
            error = "Unknown error occurred"

        @$error.children(".data").text(error).end().stop(yes).fadeIn()

        return

    pageUnloadConfirmationMessage: -> return @entityView?.pageUnloadConfirmationMessage()

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

    entity: (id) ->
        throw "Unknown entity #{ id }" unless id of @entities

        @entities[id]

    dispose: ->
        @entityView?.remove()

        @entityView = null

        this

    updateTitle: ->
        title = Cruddy.brandName

        title = @entityView.getPageTitle() + TITLE_SEPARATOR + title if @entityView?

        @$title.text title

        return this

# Cruddy router

class Router extends Backbone.Router

    initialize: ->
        @query = $.query

        entities = Cruddy.entities

        @addRoute "index", entities

        root = Cruddy.baseUrl
        history = Backbone.history

        $(document).on "click", "a", (e) =>
            return if e.isDefaultPrevented()

            fragment = e.currentTarget.href

            return if fragment.indexOf(root) isnt 0

            fragment = history.getFragment fragment.slice root.length

            # Try to find a handler for the fragment and if it is found, navigate
            # to it and cancel the default event
            for handler in history.handlers when handler.route.test(fragment)
                e.preventDefault()
                history.navigate fragment, trigger: yes

                break

            return

        this

    execute: ->
        @query = $.query.parseNew location.search

        super

    navigate: (fragment) ->
        @query = @query.load fragment

        super

    # Get the query parameter value
    getQuery: (key) -> @query.GET key

    # Set the query parameter value
    setQuery: (key, value, options) -> @updateQuery @query.set(key, value), options

    refreshQuery: (params, defaults = {}, options) ->
        query = @query.copy()

        for key, value of params
            if value is null or (key of defaults and value == defaults[key])
                query.REMOVE key
            else
                query.SET key, value

        @updateQuery query, options

    # Remove the key from the query
    removeQuery: (key, options) -> @updateQuery @query.remove(key), options

    updateQuery: (query, options) ->
        if (qs = query.toString()) isnt @query.toString()
            @query = query

            path = location.pathname
            uri = "/" + Cruddy.uri + "/"
            path = path.slice uri.length if path.indexOf(uri) is 0

            Backbone.history.navigate path + qs, options

        return this

    addRoute: (name, entities, appendage = null) ->
        route = "^(#{ entities })"
        route += "/" + appendage if appendage
        route += "(\\?.*)?$"

        @route new RegExp(route), name

        this

    resolveEntity: (id, callback) -> Cruddy.ready (app) ->
        entity = app.entity(id)

        if entity.readPermitted()
            Cruddy.app.set "entity", entity

            callback.call this, entity if callback
        else
            Cruddy.app.displayError Cruddy.lang.entity_forbidden

        return

    index: (entity) -> @resolveEntity entity

$ ->
    Cruddy.router = new Router

    Backbone.history.start
        root: Cruddy.getHistoryRoot()
        pushState: true
        hashChange: false
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