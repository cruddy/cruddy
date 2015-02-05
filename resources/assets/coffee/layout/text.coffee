class Cruddy.Layout.Text extends Cruddy.Layout.Element
    tagName: "p"
    className: "text-node"

    initialize: (options) ->
        @$el.html options.contents if options.contents

        super