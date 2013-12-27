Cruddy.columns = new Factory

class Column extends Attribute
    renderHeadCell: ->
        title = @get "title"
        help = @get "help"
        title = "<span class=\"sortable\" data-id=\"#{ @id }\">#{ title }</span>" if @get "sortable"
        if help then "<span class=\"glyphicon glyphicon-question-sign\" title=\"#{ help }\"></span> #{ title }" else title

    renderCell: (value) -> value

    createFilterInput: (model) -> null

    getClass: -> "col-" + @id


class Cruddy.columns.Field extends Column
    initialize: (attributes) ->
        field = attributes.field ? attributes.id
        @field = attributes.entity.fields.get field

        @set "title", @field.get "label" if attributes.title is null

        super

    renderCell: (value) -> @field.format value

    createFilterInput: (model) -> @field.createFilterInput model, this

    getClass: -> super + " col-" + @field.get "type"

Cruddy.columns.register "Field", Cruddy.columns.Field

class Cruddy.columns.Computed extends Column
    createFilterInput: (model) ->
        new TextInput
            model: model
            key: @id
            attributes:
                placeholder: @get "title"

    getClass: -> super + " col-computed"

Cruddy.columns.register "Computed", Cruddy.columns.Computed