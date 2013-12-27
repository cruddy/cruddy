class Related extends Backbone.Model
    resolve: -> Cruddy.app.entity(@get "related").then (entity) => @related = entity

    associate: (parent, child) -> child.set @get("foreign_key"), parent.id