class Cruddy.Inputs.Code extends Cruddy.Inputs.Base
    initialize: (options) ->
        @$el.height (options.height ? 100) + "px"

        @editor = ace.edit @el
        @editor.setTheme "ace/theme/#{ options.theme ? Cruddy.ace_theme }"

        session = @editor.getSession()

        session.setMode "ace/mode/#{ options.mode }" if options.mode
        session.setUseWrapMode true
        session.setWrapLimitRange null, null

        super

    applyChanges: (value, external) ->
        if external
            @editor.setValue value
            @editor.getSession().getSelection().clearSelection()

        this

    render: ->
        @editor.on "blur", => @model.set @key, @editor.getValue(), input: @

        super

    remove: ->
        @editor?.destroy()
        @editor = null

        super

    focus: ->
        @editor?.focus()

        this