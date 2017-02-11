class Cruddy.Layout.Layout extends Cruddy.Layout.Container
    className: "layout tab-content"
    
    activate: -> @items[0]?.activate()