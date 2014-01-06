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