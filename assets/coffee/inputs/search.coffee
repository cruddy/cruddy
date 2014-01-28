# Search input implements "change when type" and also allows to clear text with Esc
class Cruddy.Inputs.Search extends Cruddy.Inputs.Text

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