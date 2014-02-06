class Cruddy.Fields.HasOneView extends Backbone.View

    initialize: (options) ->
        @field = options.field

        @fieldList = new FieldList
            model: @model.get @field.id
            disabled: not @field.isEditable() or not @model.isSaveable()

        this

    render: ->
        @$el.html @fieldList.render().el

        this

    remove: ->
        @fieldList.remove()

        super

class Cruddy.Fields.HasOne extends Cruddy.Fields.BaseRelation
    viewConstructor: Cruddy.Fields.HasOneView