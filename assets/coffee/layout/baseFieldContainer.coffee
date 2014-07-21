class Cruddy.Layout.BaseFieldContainer extends Cruddy.Layout.Container

    constructor: (options) ->
        @title = options.title ? null

        super

    field: (options) -> @append field.createView @model, @isDisabled(), this if (field = @entity.field(options.field)) and field.isVisible()

    row: (options) -> @append new Cruddy.Layout.Row options, this