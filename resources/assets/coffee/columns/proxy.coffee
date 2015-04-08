class Cruddy.Columns.Proxy extends Cruddy.Columns.Base

    initialize: (attributes) ->
        field = attributes.field ? attributes.id
        @field = attributes.entity.fields.get field

        super

    format: (value) -> if @formatter? then @formatter.format value else @field.format value

    getClass: -> super + " col__" + @field.getType()