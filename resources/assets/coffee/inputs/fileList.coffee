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