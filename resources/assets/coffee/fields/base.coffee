class Cruddy.Fields.Base extends Cruddy.Attribute

    viewConstructor: Cruddy.Fields.InputView

    # Create a view that will represent this field in field list
    createView: (model, forceDisable = no, parent) -> new @viewConstructor { model: model, field: this, forceDisable: forceDisable }, parent

    # Create an input that is used by default view
    createInput: (model, inputId, forceDisable = no) ->
        input = @createEditableInput model, inputId if not forceDisable and @isEditable(model)

        input or @createStaticInput(model)

    # Create an input that will display a static value without possibility to edit
    createStaticInput: (model) -> new Cruddy.Inputs.Static
        model: model
        key: @id
        formatter: this

    # Create an input that is used when field is editable
    createEditableInput: (model, inputId) -> null

    # Create filter input that
    createFilterInput: (model) -> null

    # Get a label for filter input
    getFilterLabel: -> @attributes.label

    # Format value as static text
    format: (value) -> value or NOT_AVAILABLE

    # Get field's label
    getLabel: -> @attributes.label

    # Get whether the field is editable for specified model
    isEditable: (model) -> model.canBeSaved() and @attributes.disabled isnt yes and @attributes.disabled isnt model.action()

    # Get whether field is required
    isRequired: (model) -> @attributes.required is yes or @attributes.required == model.action()

    # Get whether the field is unique
    isUnique: -> @attributes.unique

    hasChangedSinceSync: (model) -> not @valuesEqual model.get(@id), model.getOriginal(@id)

    valuesEqual: (a, b) -> a is b

    isCopyable: -> not @isUnique()

    copyAttribute: (model, copy) -> model.get @id

    parse: (model, value) -> value

    prepareAttribute: (value) -> value

    prepareFilterData: (value) -> @prepareAttribute value

    parseFilterData: (value) -> value