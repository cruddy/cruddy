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