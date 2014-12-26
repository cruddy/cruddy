class Cruddy.Filters.Proxy extends Cruddy.Filters.Base

    initialize: (attributes) ->
        field = attributes.field ? attributes.id
        @field = attributes.entity.fields.get field

        super

    createFilterInput: (model) -> @field.createFilterInput model

    prepareData: (value) -> @field.prepareFilterData value

    parseData: (value) -> @field.parseFilterData value