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