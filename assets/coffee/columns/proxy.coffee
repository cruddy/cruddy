class Cruddy.Columns.Proxy extends Cruddy.Columns.Base
    initialize: (attributes) ->
        field = attributes.field ? attributes.id
        @field = attributes.entity.fields.get field

        @set "header", @field.get "label" if attributes.header is null

        super

    format: (value) -> if @formatter? then @formatter.format value else @field.format value

    createFilter: (model) -> @field.createFilterInput model, this

    getFilterLabel: -> @field.getFilterLabel()

    canFilter: -> @field.canFilter()

    getClass: -> super + " col-" + @field.get "type"