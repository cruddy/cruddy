class Cruddy.Inputs.NumberFilter extends Cruddy.Inputs.Base
    className: "input-group number-filter"

    events:
        "click .dropdown-menu a": "changeOperator"
        "change": "changeValue"

    initialize: ->
        @defaultOp = ">"

        @setValue @emptyValue(), silent: yes if not @getValue()

        super

    changeOperator: (e) ->
        e.preventDefault()

        op = $(e.currentTarget).data "op"
        value = @getValue()

        @setValue @makeValue op, value.val if value.op isnt op

        this

    changeValue: (e) ->
        value = @getValue()

        @setValue @makeValue value.op, e.target.value

        this

    applyChanges: (value, external) ->
        @$(".dropdown-menu li").removeClass "active"
        @$(".dropdown-menu a[data-op='#{ value.op }']").parent().addClass "active"

        @op.text value.op
        @input.val value.val if external

        this

    render: ->
        @$el.html @template()

        @op = @$component "op"
        @input = @$component "input"
        @reset = @$component "reset"

        super

    template: -> """
        <div class="input-group-btn">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                <span id="#{ @componentId("op") }" class="value">=</span>
                <span class="caret"></span>
            </button>

            <ul class="dropdown-menu">
                <li><a href="#" data-op="=">=</a></li>
                <li><a href="#" data-op="&gt;">&gt;</a></li>
                <li><a href="#" data-op="&lt;">&lt;</a></li>
            </ul>
        </div>

        <input type="text" class="form-control" id="#{ @componentId "input" }">
    """

    makeValue: (op, val) -> { op: op, val: val }

    emptyValue: -> @makeValue @defaultOp, ""