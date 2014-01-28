Cruddy.Columns = new Factory

class Cruddy.Columns.Base extends Attribute
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


class Cruddy.Columns.Field extends Cruddy.Columns.Base
    initialize: (attributes) ->
        field = attributes.field ? attributes.id
        @field = attributes.entity.fields.get field

        @set "title", @field.get "label" if attributes.title is null

        super

    renderCell: (value) -> if @formatter? then @formatter.format value else @field.format value

    createFilterInput: (model) -> @field.createFilterInput model, this

    getClass: -> super + " col-" + @field.get "type"

class Cruddy.Columns.Computed extends Cruddy.Columns.Base
    createFilterInput: (model) ->
        new Cruddy.Inputs.Text
            model: model
            key: @id
            attributes:
                placeholder: @get "title"

    getClass: -> super + " col-computed"