class Cruddy.Fields.HasOneView extends Backbone.View
    className: "has-one-view"

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

    createInstance: (attrs) -> if attrs instanceof Cruddy.Entity.Instance then attrs else @getReference().createInstance attrs

    applyValues: (model, data) -> model.set data.attributes

    hasChangedSinceSync: (model) -> model.hasChangedSinceSync()

    copy: (model) -> if @isUnique() then @getReference().createInstance() else model.copy()

    processErrors: (model, errors) -> model.trigger "invalid", model, errors

    triggerRelated: (event, model, args) -> model.trigger.apply [model, event, model].concat args