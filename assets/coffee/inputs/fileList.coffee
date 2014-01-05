class FileList extends BaseInput
    className: "file-list"

    events:
        "change [type=file]": "appendFiles"
        "click .action-delete": "deleteFile"

    initialize: (options) ->
        @multiple = options.multiple ? false
        @formatter = options.formatter ? format: (value) -> if value instanceof File then value.name else value

        super

    deleteFile: (e) ->
        if @multiple
            value = _.clone @model.get @key
            value.splice $(e.currentTarget).data("index"), 1
        else
            value = ''

        @model.set @key, value

        this

    appendFiles: (e) ->
        return if _.isEmpty e.target.files

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

        html += @renderInput if @multiple then "Добавить" else "Выбрать"

        @$el.html html

        this

    wrapItems: (html) -> """<ul class="list-group">#{ html }</ul>"""

    renderInput: (label) ->
        """
        <div class="btn btn-sm btn-default file-list-input-wrap">
            <input type="file" #{ "multiple" if @multiple }>
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
