class Cruddy.Filters.Proxy extends Cruddy.Filters.Base

    initialize: (attributes) ->
        field = attributes.field ? attributes.id
        @field = attributes.entity.fields.get field

        super

    createFilterInput: (model) -> @field.createFilterInput model