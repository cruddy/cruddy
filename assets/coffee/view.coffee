class Cruddy.View extends Backbone.View
    componentId: (component) -> @cid + "-" + component

    $component: (component) -> @$ "#" + @componentId(component)