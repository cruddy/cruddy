class Cruddy.Columns.Proxy extends Cruddy.Columns.Base

    initialize: (attributes) ->
        field = attributes.field ? attributes.id
        @field = attributes.entity.fields.get field

        super

    format: (value) -> @field.format value

    getClass: -> super + " col__" + @field.getType()