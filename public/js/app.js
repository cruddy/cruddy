(function() {
  var API_URL, Alert, App, Attribute, BaseInput, BooleanInput, Checkbox, Column, Cruddy, DataGrid, DataSource, Entity, EntityDropdown, EntityForm, EntityInstance, EntityPage, EntitySelector, Factory, Field, FieldList, FieldView, FilterList, Related, Router, StaticInput, TRANSITIONEND, TextInput, Textarea, humanize, _ref, _ref1, _ref10, _ref11, _ref12, _ref13, _ref14, _ref15, _ref16, _ref17, _ref18, _ref19, _ref2, _ref20, _ref21, _ref22, _ref23, _ref24, _ref3, _ref4, _ref5, _ref6, _ref7, _ref8, _ref9,
    _this = this,
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  Cruddy = window.Cruddy || {};

  API_URL = "/backend/api/v1";

  TRANSITIONEND = "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd";

  moment.lang((_ref = Cruddy.locale) != null ? _ref : "en");

  Backbone.emulateHTTP = true;

  Backbone.emulateJSON = true;

  $(document).ajaxError(function(e, xhr) {
    if (xhr.status === 403) {
      return location.href = "login";
    }
  });

  humanize = function(id) {
    return id.replace(/_-/, " ");
  };

  Alert = (function(_super) {
    __extends(Alert, _super);

    function Alert() {
      _ref1 = Alert.__super__.constructor.apply(this, arguments);
      return _ref1;
    }

    Alert.prototype.tagName = "span";

    Alert.prototype.className = "alert";

    Alert.prototype.initialize = function(options) {
      var _ref2,
        _this = this;
      this.$el.addClass((_ref2 = this.className + "-" + options.type) != null ? _ref2 : "info");
      this.$el.text(options.message);
      if (options.timeout != null) {
        setTimeout((function() {
          return _this.remove();
        }), options.timeout);
      }
      return this;
    };

    return Alert;

  })(Backbone.View);

  Factory = (function() {
    function Factory() {}

    Factory.prototype.types = {};

    Factory.prototype.register = function(name, constructor) {
      return this.types[name] = constructor;
    };

    Factory.prototype.create = function(name, options) {
      var constructor;
      constructor = this.types[name];
      if (constructor != null) {
        return new constructor(options);
      }
    };

    return Factory;

  })();

  Attribute = (function(_super) {
    __extends(Attribute, _super);

    function Attribute() {
      _ref2 = Attribute.__super__.constructor.apply(this, arguments);
      return _ref2;
    }

    return Attribute;

  })(Backbone.Model);

  DataSource = (function(_super) {
    __extends(DataSource, _super);

    function DataSource() {
      _ref3 = DataSource.__super__.constructor.apply(this, arguments);
      return _ref3;
    }

    DataSource.prototype.defaults = {
      data: []
    };

    DataSource.prototype.initialize = function(attributes, options) {
      var _this = this;
      this.entity = options.entity;
      if (options.columns != null) {
        this.columns = options.columns;
      }
      if (options.filter != null) {
        this.filter = options.filter;
      }
      if (this.filter != null) {
        this.listenTo(this.filter, "change", this.fetch);
      }
      return this.on("change", function() {
        if (!_this._fetching) {
          return _this.fetch();
        }
      });
    };

    DataSource.prototype.hasData = function() {
      return !_.isEmpty(this.get("data"));
    };

    DataSource.prototype.isFull = function() {
      return this.get("current_page") === this.get("last_page");
    };

    DataSource.prototype.fetch = function() {
      var _this = this;
      if (this.request != null) {
        this.request.abort();
      }
      this.request = $.getJSON("" + API_URL + "/" + this.entity.id, this.data(), function(resp) {
        _this._fetching = true;
        _this.set(resp.data);
        _this._fetching = false;
        return _this.trigger("data", _this, resp.data.data);
      });
      this.request.fail(function(xhr) {
        return _this.trigger("error", _this, xhr);
      });
      this.request.always(function() {
        return _this.request = null;
      });
      this.trigger("request", this, this.request);
      return this.request;
    };

    DataSource.prototype.data = function() {
      var data, filters;
      data = {
        order_by: this.get("order_by"),
        order_dir: this.get("order_dir"),
        page: this.get("current_page"),
        per_page: this.get("per_page")
      };
      filters = this.filterData();
      if (!_.isEmpty(filters)) {
        data.filters = filters;
      }
      if (this.columns != null) {
        data.columns = this.columns.join(",");
      }
      return data;
    };

    DataSource.prototype.filterData = function() {
      var data, key, value, _ref4;
      if (this.filter == null) {
        return null;
      }
      data = {};
      _ref4 = this.filter.attributes;
      for (key in _ref4) {
        value = _ref4[key];
        if (!(value === null || value === "")) {
          data[key] = value;
        }
      }
      return data;
    };

    return DataSource;

  })(Backbone.Model);

  DataGrid = (function(_super) {
    __extends(DataGrid, _super);

    DataGrid.prototype.tagName = "table";

    DataGrid.prototype.className = "table table-hover table-condensed data-grid";

    DataGrid.prototype.events = {
      "click .sortable": "setOrder",
      "click .item": "navigate"
    };

    function DataGrid(options) {
      this.className += " data-grid-" + options.model.entity.id;
      DataGrid.__super__.constructor.apply(this, arguments);
    }

    DataGrid.prototype.initialize = function(options) {
      this.entity = this.model.entity;
      this.listenTo(this.model, "change:data", this.updateData);
      this.listenTo(this.model, "change:order_by change:order_dir", this.onOrderChange);
      return this.listenTo(this.entity, "change:instance", this.onInstanceChange);
    };

    DataGrid.prototype.onOrderChange = function() {
      var orderBy, orderDir;
      orderBy = this.model.get("order_by");
      orderDir = this.model.get("order_dir");
      if ((this.orderBy != null) && orderBy !== this.orderBy) {
        this.$("#col-" + this.orderBy + " .sortable").removeClass("asc desc");
      }
      this.orderBy = orderBy;
      this.$("#col-" + this.orderBy + " .sortable").removeClass("asc desc").addClass(orderDir);
      return this;
    };

    DataGrid.prototype.onInstanceChange = function(entity, curr) {
      var prev,
        _this = this;
      prev = entity.previous("instance");
      if (prev != null) {
        this.$("#item-" + prev.id).removeClass("active");
        prev.off(null, null, this);
      }
      if (curr != null) {
        this.$("#item-" + curr.id).addClass("active");
        curr.on("sync destroy", (function() {
          return _this.model.fetch();
        }), this);
      }
      return this;
    };

    DataGrid.prototype.setOrder = function(e) {
      var orderBy, orderDir;
      orderBy = $(e.target).data("id");
      orderDir = this.model.get("order_dir");
      if (orderBy === this.model.get("order_by")) {
        orderDir = orderDir === 'asc' ? 'desc' : 'asc';
      } else {
        orderDir = this.entity.columns.get(orderBy).get("order_dir");
      }
      this.model.set({
        order_by: orderBy,
        order_dir: orderDir
      });
      return this;
    };

    DataGrid.prototype.navigate = function(e) {
      Cruddy.router.navigate(this.entity.link($(e.currentTarget).data("id")), {
        trigger: true
      });
      return this;
    };

    DataGrid.prototype.updateData = function(datasource, data) {
      this.$(".items").replaceWith(this.renderBody(this.entity.columns.models, data));
      return this;
    };

    DataGrid.prototype.render = function() {
      var columns, data;
      columns = this.entity.columns.models;
      data = this.model.get("data");
      this.$el.html(this.renderHead(columns) + this.renderBody(columns, data));
      this.onOrderChange(this.model);
      return this;
    };

    DataGrid.prototype.renderHead = function(columns) {
      var col, html, _i, _len;
      html = "<thead><tr>";
      for (_i = 0, _len = columns.length; _i < _len; _i++) {
        col = columns[_i];
        html += this.renderHeadCell(col);
      }
      return html += "</tr></thead>";
    };

    DataGrid.prototype.renderHeadCell = function(col) {
      return "<th class=\"" + (col.getClass()) + "\" id=\"col-" + col.id + "\">" + (col.renderHeadCell()) + "</th>";
    };

    DataGrid.prototype.renderBody = function(columns, data) {
      var html, item, _i, _len;
      html = "<tbody class=\"items\">";
      if ((data != null) && data.length) {
        for (_i = 0, _len = data.length; _i < _len; _i++) {
          item = data[_i];
          html += this.renderRow(columns, item);
        }
      } else {
        html += "<tr><td class=\"no-items\" colspan=\"" + columns.length + "\">Ничего не найдено</td></tr>";
      }
      return html += "</tbody>";
    };

    DataGrid.prototype.renderRow = function(columns, item) {
      var active, col, html, instance, _i, _len;
      instance = this.entity.get("instance");
      active = (instance != null) && item.id === instance.id ? "active" : "";
      html = "<tr class=\"item " + active + "\" id=\"item-" + item.id + "\" data-id=\"" + item.id + "\">";
      for (_i = 0, _len = columns.length; _i < _len; _i++) {
        col = columns[_i];
        html += this.renderCell(col, item);
      }
      return html += "</tr>";
    };

    DataGrid.prototype.renderCell = function(col, item) {
      return "<td class=\"" + (col.getClass()) + "\">" + (col.renderCell(item[col.id])) + "</td>";
    };

    return DataGrid;

  })(Backbone.View);

  FieldList = (function(_super) {
    __extends(FieldList, _super);

    function FieldList() {
      _ref4 = FieldList.__super__.constructor.apply(this, arguments);
      return _ref4;
    }

    FieldList.prototype.className = "field-list";

    FieldList.prototype.initialize = function() {
      this.listenTo(this.model.entity.fields, "add remove", this.render);
      return this;
    };

    FieldList.prototype.focus = function() {
      var _ref5;
      if ((_ref5 = this.primary) != null) {
        _ref5.focus();
      }
      return this;
    };

    FieldList.prototype.render = function() {
      var field, _i, _len, _ref5;
      this.$el.empty();
      _ref5 = this.createFields();
      for (_i = 0, _len = _ref5.length; _i < _len; _i++) {
        field = _ref5[_i];
        this.$el.append(field.el);
      }
      return this;
    };

    FieldList.prototype.createFields = function() {
      var field, view, _i, _len, _ref5;
      this.dispose();
      this.fields = (function() {
        var _i, _len, _ref5, _results;
        _ref5 = this.model.entity.fields.models;
        _results = [];
        for (_i = 0, _len = _ref5.length; _i < _len; _i++) {
          field = _ref5[_i];
          _results.push(field.createView(this.model).render());
        }
        return _results;
      }).call(this);
      this.primary = null;
      _ref5 = this.fields;
      for (_i = 0, _len = _ref5.length; _i < _len; _i++) {
        view = _ref5[_i];
        if (!(view.field.isEditable(this.model))) {
          continue;
        }
        this.primary = view;
        break;
      }
      return this.fields;
    };

    FieldList.prototype.dispose = function() {
      var field, _i, _len, _ref5;
      if (this.fields != null) {
        _ref5 = this.fields;
        for (_i = 0, _len = _ref5.length; _i < _len; _i++) {
          field = _ref5[_i];
          field.remove();
        }
      }
      return this;
    };

    FieldList.prototype.stopListening = function() {
      this.dispose();
      return FieldList.__super__.stopListening.apply(this, arguments);
    };

    return FieldList;

  })(Backbone.View);

  FilterList = (function(_super) {
    __extends(FilterList, _super);

    function FilterList() {
      _ref5 = FilterList.__super__.constructor.apply(this, arguments);
      return _ref5;
    }

    FilterList.prototype.className = "filter-list";

    FilterList.prototype.tagName = "fieldset";

    FilterList.prototype.initialize = function(options) {
      this.entity = options.entity;
      return this;
    };

    FilterList.prototype.render = function() {
      var col, input, _i, _len, _ref6;
      this.dispose();
      this.$el.html(this.template());
      this.items = this.$(".filter-list-container");
      this.filters = [];
      _ref6 = this.entity.columns.models;
      for (_i = 0, _len = _ref6.length; _i < _len; _i++) {
        col = _ref6[_i];
        if (col.get("filterable")) {
          if (input = col.createFilterInput(this.model)) {
            this.filters.push(input);
            this.items.append(input.render().el);
            input.$el.wrap("<div class=\"form-group filter " + (col.getClass()) + "\"><div class=\"input-wrap\"></div></div>").parent().before("<label>" + (col.get("title")) + "</label>");
          }
        }
      }
      return this;
    };

    FilterList.prototype.template = function() {
      return "<div class=\"filter-list-container\"></div>";
    };

    FilterList.prototype.dispose = function() {
      var filter, _i, _len, _ref6;
      if (this.filters != null) {
        _ref6 = this.filters;
        for (_i = 0, _len = _ref6.length; _i < _len; _i++) {
          filter = _ref6[_i];
          filter.remove();
        }
      }
      return this;
    };

    FilterList.prototype.remove = function() {
      this.dispose();
      return FilterList.__super__.remove.apply(this, arguments);
    };

    return FilterList;

  })(Backbone.View);

  BaseInput = (function(_super) {
    __extends(BaseInput, _super);

    function BaseInput(options) {
      this.key = options.key;
      BaseInput.__super__.constructor.apply(this, arguments);
    }

    BaseInput.prototype.initialize = function() {
      this.listenTo(this.model, "change:" + this.key, this.applyChanges);
      return this;
    };

    BaseInput.prototype.applyChanges = function(model, data) {
      return this;
    };

    BaseInput.prototype.render = function() {
      return this.applyChanges(this.model, this.model.get(this.key));
    };

    BaseInput.prototype.focus = function() {
      return this;
    };

    return BaseInput;

  })(Backbone.View);

  StaticInput = (function(_super) {
    __extends(StaticInput, _super);

    function StaticInput() {
      _ref6 = StaticInput.__super__.constructor.apply(this, arguments);
      return _ref6;
    }

    StaticInput.prototype.tagName = "p";

    StaticInput.prototype.className = "form-control-static";

    StaticInput.prototype.initialize = function(options) {
      if (options.formatter != null) {
        this.formatter = options.formatter;
      }
      return StaticInput.__super__.initialize.apply(this, arguments);
    };

    StaticInput.prototype.applyChanges = function(model, data) {
      return this.render();
    };

    StaticInput.prototype.render = function() {
      var value;
      value = this.model.get(this.key);
      if (this.formatter != null) {
        value = this.formatter.format(value);
      }
      this.$el.html(value);
      return this;
    };

    return StaticInput;

  })(BaseInput);

  TextInput = (function(_super) {
    __extends(TextInput, _super);

    TextInput.prototype.tagName = "input";

    TextInput.prototype.className = "form-control";

    TextInput.prototype.size = "sm";

    TextInput.prototype.events = {
      "change": "change",
      "keydown": "keydown"
    };

    function TextInput(options) {
      if (options.size != null) {
        this.size = options.size;
      }
      this.className += " input-" + this.size;
      TextInput.__super__.constructor.apply(this, arguments);
    }

    TextInput.prototype.scheduleChange = function() {
      var _this = this;
      if (this.timeout != null) {
        clearTimeout(this.timeout);
      }
      this.timeout = setTimeout((function() {
        return _this.change();
      }), 300);
      return this;
    };

    TextInput.prototype.keydown = function(e) {
      if (e.ctrlKey && e.keyCode === 13) {
        this.change();
        return false;
      }
      if (e.keyCode === 27) {
        this.model.set(this.key, "");
        return false;
      }
    };

    TextInput.prototype.change = function() {
      this.model.set(this.key, this.el.value);
      return this;
    };

    TextInput.prototype.applyChanges = function(model, data) {
      this.$el.val(data);
      return this;
    };

    TextInput.prototype.focus = function() {
      this.el.focus();
      return this;
    };

    return TextInput;

  })(BaseInput);

  Textarea = (function(_super) {
    __extends(Textarea, _super);

    function Textarea() {
      _ref7 = Textarea.__super__.constructor.apply(this, arguments);
      return _ref7;
    }

    Textarea.prototype.tagName = "textarea";

    return Textarea;

  })(TextInput);

  Checkbox = (function(_super) {
    __extends(Checkbox, _super);

    function Checkbox() {
      _ref8 = Checkbox.__super__.constructor.apply(this, arguments);
      return _ref8;
    }

    Checkbox.prototype.tagName = "label";

    Checkbox.prototype.label = "";

    Checkbox.prototype.events = {
      "change": "change"
    };

    Checkbox.prototype.initialize = function(options) {
      if (options.label != null) {
        this.label = options.label;
      }
      return Checkbox.__super__.initialize.apply(this, arguments);
    };

    Checkbox.prototype.change = function() {
      this.model.set(this.key, this.input.prop("checked"));
      return this;
    };

    Checkbox.prototype.applyChanges = function(model, value) {
      this.input.prop("checked", value);
      return this;
    };

    Checkbox.prototype.render = function() {
      this.input = $("<input>", {
        type: "checkbox",
        checked: this.model.get(this.key)
      });
      this.$el.append(this.input);
      if (this.label != null) {
        this.$el.append(this.label);
      }
      return this;
    };

    return Checkbox;

  })(BaseInput);

  BooleanInput = (function(_super) {
    __extends(BooleanInput, _super);

    function BooleanInput() {
      _ref9 = BooleanInput.__super__.constructor.apply(this, arguments);
      return _ref9;
    }

    BooleanInput.prototype.tripleState = false;

    BooleanInput.prototype.events = {
      "click input": "check"
    };

    BooleanInput.prototype.initialize = function(options) {
      if (options.tripleState != null) {
        return this.tripleState = options.tripleState;
      }
    };

    BooleanInput.prototype.check = function(e) {
      this.model.set(this.key, (function() {
        switch (e.target.value) {
          case "1":
            return true;
          case "0":
            return false;
          default:
            return null;
        }
      })());
      return this;
    };

    BooleanInput.prototype.applyChanges = function(model, value) {
      value = (function() {
        switch (value) {
          case true:
            return "1";
          case false:
            return "0";
          default:
            return "";
        }
      })();
      this.$("[value=\"" + value + "\"]").prop("checked", true);
      return this;
    };

    BooleanInput.prototype.render = function() {
      this.$el.empty();
      if (this.tripleState) {
        this.$el.append(this.itemTemplate("неважно", ""));
      }
      this.$el.append(this.itemTemplate("да", 1));
      this.$el.append(this.itemTemplate("нет", 0));
      return BooleanInput.__super__.render.apply(this, arguments);
    };

    BooleanInput.prototype.itemTemplate = function(label, value) {
      return "<label class=\"radio-inline\">\n    <input type=\"radio\" name=\"" + this.cid + "\" value=\"" + value + "\">\n    " + label + "\n</label>";
    };

    return BooleanInput;

  })(BaseInput);

  EntityDropdown = (function(_super) {
    __extends(EntityDropdown, _super);

    function EntityDropdown() {
      _ref10 = EntityDropdown.__super__.constructor.apply(this, arguments);
      return _ref10;
    }

    EntityDropdown.prototype.className = "entity-dropdown";

    EntityDropdown.prototype.events = {
      "click .btn-remove": "removeItem",
      "show.bs.dropdown": "renderDropdown"
    };

    EntityDropdown.prototype.mutiple = false;

    EntityDropdown.prototype.reference = null;

    EntityDropdown.prototype.initialize = function(options) {
      if (options.multiple != null) {
        this.multiple = options.multiple;
      }
      if (options.reference != null) {
        this.reference = options.reference;
      }
      this.active = false;
      return EntityDropdown.__super__.initialize.apply(this, arguments);
    };

    EntityDropdown.prototype.removeItem = function(e) {
      var i, value;
      if (this.multiple) {
        i = $(e.currentTarget).data("key");
        value = _.clone(this.model.get(this.key));
        value.splice(i, 1);
      } else {
        value = null;
      }
      this.model.set(this.key, value);
      return this;
    };

    EntityDropdown.prototype.renderDropdown = function() {
      if (this.selector != null) {
        return;
      }
      this.selector = new EntitySelector({
        model: this.model,
        key: this.key,
        multiple: this.multiple,
        reference: this.reference
      });
      this.dropdown = $("<div></div>", {
        "class": "selector-wrap"
      });
      this.$el.append(this.dropdown.append(this.selector.render().el));
      return this;
    };

    EntityDropdown.prototype.applyChanges = function(model, value) {
      if (this.multiple) {
        this.renderItems();
      } else {
        this.updateItem();
        this.$el.removeClass("open");
      }
      return this;
    };

    EntityDropdown.prototype.render = function() {
      this.dispose();
      if (this.multiple) {
        this.renderMultiple();
      } else {
        this.renderSingle();
      }
      this.$el.attr("id", this.cid);
      return this;
    };

    EntityDropdown.prototype.renderMultiple = function() {
      this.$el.append(this.items = $("<div>", {
        "class": "items"
      }));
      this.$el.append("<button type=\"button\" class=\"btn btn-default btn-sm btn-block dropdown-toggle\" data-toggle=\"dropdown\" data-target=\"#" + this.cid + "\">\n    Выбрать\n    <span class=\"caret\"></span>\n</button>");
      return this.renderItems();
    };

    EntityDropdown.prototype.renderItems = function() {
      var html, key, value, _i, _len, _ref11;
      html = "";
      _ref11 = this.model.get(this.key);
      for (key = _i = 0, _len = _ref11.length; _i < _len; key = ++_i) {
        value = _ref11[key];
        html += this.itemTemplate(value.title, key);
      }
      this.items.html(html);
      this.items.toggleClass("has-items", html !== "");
      return this;
    };

    EntityDropdown.prototype.renderSingle = function() {
      this.$el.html(this.itemTemplate("", ""));
      this.itemTitle = this.$(".form-control");
      this.itemDelete = this.$(".btn-remove");
      return this.updateItem();
    };

    EntityDropdown.prototype.updateItem = function() {
      var value;
      value = this.model.get(this.key);
      this.itemTitle.text(value ? value.title : "Не выбрано");
      this.itemDelete.toggle(!!value);
      return this;
    };

    EntityDropdown.prototype.itemTemplate = function(value, key) {
      var html;
      if (key == null) {
        key = null;
      }
      html = "<div class=\"input-group input-group-sm item\">\n    <p class=\"form-control\">" + (_.escape(value)) + "</p>\n    <div class=\"input-group-btn\">";
      if (!this.multiple || key !== null) {
        html += "<button type=\"button\" class=\"btn btn-default btn-remove\" data-key=\"" + key + "\">\n    <span class=\"glyphicon glyphicon-remove\"></span>\n</button>";
      }
      if (!this.multiple || key === null) {
        html += "<button type=\"button\" class=\"btn btn-default btn-dropdown dropdown-toggle\" data-toggle=\"dropdown\" data-target=\"#" + this.cid + "\">\n    <span class=\"caret\"></span>\n</button>";
      }
      return html += "</div></div>";
    };

    EntityDropdown.prototype.dispose = function() {
      if (this.selector) {
        this.selector.stopListening();
      }
      this.selector = null;
      return this;
    };

    EntityDropdown.prototype.stopListening = function() {
      this.dispose();
      return EntityDropdown.__super__.stopListening.apply(this, arguments);
    };

    return EntityDropdown;

  })(BaseInput);

  EntitySelector = (function(_super) {
    __extends(EntitySelector, _super);

    function EntitySelector() {
      _ref11 = EntitySelector.__super__.constructor.apply(this, arguments);
      return _ref11;
    }

    EntitySelector.prototype.className = "entity-selector";

    EntitySelector.prototype.events = {
      "click li": "check"
    };

    EntitySelector.prototype.initialize = function(options) {
      var _ref12, _ref13,
        _this = this;
      EntitySelector.__super__.initialize.apply(this, arguments);
      this.filter = (_ref12 = options.filter) != null ? _ref12 : false;
      this.multiple = (_ref13 = options.multiple) != null ? _ref13 : false;
      this.data = [];
      this.buildSelected(this.model.get(this.key));
      Cruddy.app.entity(options.reference).then(function(entity) {
        _this.entity = entity;
        _this.primaryKey = "id";
        _this.primaryColumn = entity.get("primary_column");
        _this.dataSource = entity.search();
        _this.listenTo(_this.dataSource, "request", _this.loading);
        return _this.listenTo(_this.dataSource, "data", _this.appendItems);
      });
      return this;
    };

    EntitySelector.prototype.check = function(e) {
      var id, item, uncheck, value;
      id = parseInt($(e.target).data("id"));
      uncheck = id in this.selected;
      item = _.find(this.data, function(item) {
        return item.id === id;
      });
      if (this.multiple) {
        if (uncheck) {
          value = _.filter(this.model.get(this.key), function(item) {
            return item.id !== id;
          });
        } else {
          value = _.clone(this.model.get(this.key));
          value.push(item);
        }
      } else {
        value = item;
      }
      this.model.set(this.key, value);
      return false;
    };

    EntitySelector.prototype.applyChanges = function(model, data) {
      this.buildSelected(data);
      return this.renderItems();
    };

    EntitySelector.prototype.buildSelected = function(data) {
      var item, _i, _len;
      this.selected = {};
      if (this.multiple) {
        for (_i = 0, _len = data.length; _i < _len; _i++) {
          item = data[_i];
          this.selected[item.id] = true;
        }
      } else {
        if (data != null) {
          this.selected[data.id] = true;
        }
      }
      return this;
    };

    EntitySelector.prototype.loading = function() {
      return this;
    };

    EntitySelector.prototype.appendItems = function(datasource, data) {
      var item, _i, _len;
      if (_.isEmpty(data)) {
        return;
      }
      for (_i = 0, _len = data.length; _i < _len; _i++) {
        item = data[_i];
        this.data.push({
          id: item[this.primaryKey],
          title: item[this.primaryColumn]
        });
      }
      this.renderItems();
      return this;
    };

    EntitySelector.prototype.renderItems = function() {
      var html, item, _i, _len, _ref12;
      html = "";
      _ref12 = this.data;
      for (_i = 0, _len = _ref12.length; _i < _len; _i++) {
        item = _ref12[_i];
        html += this.renderItem(item);
      }
      this.items.html(html);
      return this;
    };

    EntitySelector.prototype.renderItem = function(item) {
      var className;
      className = item.id in this.selected ? "selected" : "";
      return "<li class=\"" + className + "\" data-id=\"" + item.id + "\">" + item.title + "</li>";
    };

    EntitySelector.prototype.render = function() {
      this.dispose();
      this.$el.html(this.template());
      this.items = this.$(".items");
      if ((this.dataSource != null) && this.dataSource.hasData()) {
        this.appendItems(this.dataSource, this.dataSource.get("data"));
      }
      return this;
    };

    EntitySelector.prototype.template = function() {
      return "<div class=\"items-container\"><ul class=\"items\"></ul></div>";
    };

    EntitySelector.prototype.dispose = function() {
      return this;
    };

    EntitySelector.prototype.stopListening = function() {
      this.dispose();
      return EntitySelector.__super__.stopListening.apply(this, arguments);
    };

    return EntitySelector;

  })(BaseInput);

  Cruddy.fields = new Factory;

  FieldView = (function(_super) {
    __extends(FieldView, _super);

    FieldView.prototype.className = "field";

    function FieldView(options) {
      var base, classes;
      this.inputId = options.model.entity.id + "_" + options.field.id;
      base = " " + this.className + "-";
      classes = [options.field.attributes.type, options.field.id, this.inputId];
      this.className += base + classes.join(base);
      if (options.field.get("required")) {
        this.className += " required";
      }
      FieldView.__super__.constructor.apply(this, arguments);
    }

    FieldView.prototype.initialize = function(options) {
      this.field = options.field;
      this.listenTo(this.field, "change:visible", this.toggleVisibility);
      this.listenTo(this.field, "change:editable", this.render);
      this.listenTo(this.model, "sync", this.render);
      this.listenTo(this.model, "request", this.hideError);
      this.listenTo(this.model, "invalid", this.showError);
      return this;
    };

    FieldView.prototype.hideError = function() {
      this.error.hide();
      return this.inputHolder.removeClass("has-error");
    };

    FieldView.prototype.showError = function(model, errors) {
      var error;
      error = errors[this.field.get("id")];
      if (error) {
        this.inputHolder.addClass("has-error");
        return this.error.text(error).show();
      }
    };

    FieldView.prototype.render = function() {
      if (this.input != null) {
        this.input.remove();
      }
      this.$el.html(this.template());
      this.inputHolder = this.$(".input-holder");
      this.input = this.field.createInput(this.model);
      if (this.input != null) {
        this.inputHolder.append(this.input.render().el);
      }
      this.inputHolder.append(this.error = $(this.errorTemplate()));
      this.toggleVisibility();
      return this;
    };

    FieldView.prototype.helpTemplate = function() {
      var help;
      help = this.field.get("help");
      if (help) {
        return "<span class=\"glyphicon glyphicon-question-sign field-help\" title=\"" + help + "\"></span>";
      } else {
        return "";
      }
    };

    FieldView.prototype.errorTemplate = function() {
      return "<span class=\"help-block error\"></span>";
    };

    FieldView.prototype.label = function(label) {
      if (label == null) {
        label = this.field.get("label");
      }
      return "<label for=\"" + this.inputId + "\">" + label + "</label>";
    };

    FieldView.prototype.template = function() {
      return "" + (this.helpTemplate()) + "\n<div class=\"form-group input-holder\">\n    " + (this.label()) + "\n</div>";
    };

    FieldView.prototype.isVisible = function() {
      return this.field.get("visible") && (this.field.get("editable") && this.field.get("updatable") || !this.model.isNew());
    };

    FieldView.prototype.toggleVisibility = function() {
      return this.$el.toggle(this.isVisible());
    };

    FieldView.prototype.focus = function() {
      if (this.input != null) {
        this.input.focus();
      }
      return this;
    };

    FieldView.prototype.stopListening = function() {
      if (this.input != null) {
        this.input.stopListening();
      }
      return FieldView.__super__.stopListening.apply(this, arguments);
    };

    return FieldView;

  })(Backbone.View);

  Field = (function(_super) {
    __extends(Field, _super);

    function Field() {
      _ref12 = Field.__super__.constructor.apply(this, arguments);
      return _ref12;
    }

    Field.prototype.viewConstructor = FieldView;

    Field.prototype.createView = function(model) {
      return new this.viewConstructor({
        model: model,
        field: this
      });
    };

    Field.prototype.createInput = function(model) {
      var input;
      if (this.isEditable(model)) {
        input = this.createEditableInput(model);
      }
      if (input != null) {
        return input;
      } else {
        return new StaticInput({
          model: model,
          key: this.id,
          formatter: this
        });
      }
    };

    Field.prototype.createEditableInput = function(model) {
      return null;
    };

    Field.prototype.format = function(value) {
      if (value) {
        return value;
      } else {
        return "n/a";
      }
    };

    Field.prototype.isEditable = function(model) {
      return this.get("editable") && (this.get("updatable") || !model.isNew()) && model.isSaveable();
    };

    return Field;

  })(Attribute);

  Cruddy.fields.Input = (function(_super) {
    __extends(Input, _super);

    function Input() {
      _ref13 = Input.__super__.constructor.apply(this, arguments);
      return _ref13;
    }

    Input.prototype.createEditableInput = function(model) {
      var attributes, type;
      attributes = {
        placeholder: this.get("label")
      };
      type = this.get("input_type");
      if (type === "textarea") {
        attributes.rows = this.get("rows");
        return new Textarea({
          model: model,
          key: this.id,
          attributes: attributes
        });
      } else {
        attributes.type = type;
        return new TextInput({
          model: model,
          key: this.id,
          attributes: attributes
        });
      }
    };

    Input.prototype.format = function(value) {
      if (this.get("input_type") === "textarea") {
        return "<pre>" + (Input.__super__.format.apply(this, arguments)) + "</pre>";
      } else {
        return Input.__super__.format.apply(this, arguments);
      }
    };

    Input.prototype.createFilterInput = function(model, column) {
      return new TextInput({
        model: model,
        key: this.id,
        attributes: {
          placeholder: this.get("label")
        }
      });
    };

    return Input;

  })(Field);

  Cruddy.fields.register("Input", Cruddy.fields.Input);

  /*
  class Cruddy.fields.DateTimeView extends Cruddy.fields.InputView
      format: (value) -> moment.unix(value).format @field.get "format"
      unformat: (value) -> moment(value, @field.get "format").unix()
  */


  Cruddy.fields.DateTime = (function(_super) {
    __extends(DateTime, _super);

    function DateTime() {
      _ref14 = DateTime.__super__.constructor.apply(this, arguments);
      return _ref14;
    }

    DateTime.prototype.format = function(value) {
      if (value === null) {
        return "никогда";
      } else {
        return moment.unix(value).calendar();
      }
    };

    return DateTime;

  })(Cruddy.fields.Input);

  Cruddy.fields.register("DateTime", Cruddy.fields.DateTime);

  Cruddy.fields.Boolean = (function(_super) {
    __extends(Boolean, _super);

    function Boolean() {
      _ref15 = Boolean.__super__.constructor.apply(this, arguments);
      return _ref15;
    }

    Boolean.prototype.createEditableInput = function(model) {
      return new BooleanInput({
        model: model,
        key: this.id
      });
    };

    Boolean.prototype.createFilterInput = function(model) {
      return new BooleanInput({
        model: model,
        key: this.id,
        tripleState: true
      });
    };

    Boolean.prototype.format = function(value) {
      if (value) {
        return "да";
      } else {
        return "нет";
      }
    };

    return Boolean;

  })(Field);

  Cruddy.fields.register("Boolean", Cruddy.fields.Boolean);

  Cruddy.fields.Relation = (function(_super) {
    __extends(Relation, _super);

    function Relation() {
      _ref16 = Relation.__super__.constructor.apply(this, arguments);
      return _ref16;
    }

    Relation.prototype.createEditableInput = function(model) {
      return new EntityDropdown({
        model: model,
        key: this.id,
        multiple: this.get("multiple"),
        reference: this.get("reference")
      });
    };

    Relation.prototype.createFilterInput = function(model) {
      return this.createEditableInput(model);
    };

    Relation.prototype.format = function(value) {
      if (_.isEmpty(value)) {
        return "не указано";
      }
      if (this.attributes.multiple) {
        return _.pluck(value, "title").join(", ");
      } else {
        return value.title;
      }
    };

    return Relation;

  })(Field);

  Cruddy.fields.register("Relation", Cruddy.fields.Relation);

  Cruddy.columns = new Factory;

  Column = (function(_super) {
    __extends(Column, _super);

    function Column() {
      _ref17 = Column.__super__.constructor.apply(this, arguments);
      return _ref17;
    }

    Column.prototype.renderHeadCell = function() {
      var help, title;
      title = this.get("title");
      help = this.get("help");
      if (this.get("sortable")) {
        title = "<span class=\"sortable\" data-id=\"" + this.id + "\">" + title + "</span>";
      }
      if (help) {
        return "<span class=\"glyphicon glyphicon-question-sign\" title=\"" + help + "\"></span> " + title;
      } else {
        return title;
      }
    };

    Column.prototype.renderCell = function(value) {
      return value;
    };

    Column.prototype.createFilterInput = function(model) {
      return null;
    };

    Column.prototype.getClass = function() {
      return "col-" + this.id;
    };

    return Column;

  })(Attribute);

  Cruddy.columns.Field = (function(_super) {
    __extends(Field, _super);

    function Field() {
      _ref18 = Field.__super__.constructor.apply(this, arguments);
      return _ref18;
    }

    Field.prototype.initialize = function(attributes) {
      var field, _ref19;
      field = (_ref19 = attributes.field) != null ? _ref19 : attributes.id;
      this.field = attributes.entity.fields.get(field);
      if (attributes.title === null) {
        this.set("title", this.field.get("label"));
      }
      return Field.__super__.initialize.apply(this, arguments);
    };

    Field.prototype.renderCell = function(value) {
      return this.field.format(value);
    };

    Field.prototype.createFilterInput = function(model) {
      return this.field.createFilterInput(model, this);
    };

    Field.prototype.getClass = function() {
      return Field.__super__.getClass.apply(this, arguments) + " col-" + this.field.get("type");
    };

    return Field;

  })(Column);

  Cruddy.columns.register("Field", Cruddy.columns.Field);

  Cruddy.columns.Computed = (function(_super) {
    __extends(Computed, _super);

    function Computed() {
      _ref19 = Computed.__super__.constructor.apply(this, arguments);
      return _ref19;
    }

    Computed.prototype.createFilterInput = function(model) {
      return new TextInput({
        model: model,
        key: this.id,
        attributes: {
          placeholder: this.get("title")
        }
      });
    };

    Computed.prototype.getClass = function() {
      return Computed.__super__.getClass.apply(this, arguments) + " col-computed";
    };

    return Computed;

  })(Column);

  Cruddy.columns.register("Computed", Cruddy.columns.Computed);

  Entity = (function(_super) {
    __extends(Entity, _super);

    function Entity() {
      _ref20 = Entity.__super__.constructor.apply(this, arguments);
      return _ref20;
    }

    Entity.prototype.initialize = function(attributes, options) {
      var item, _i, _len, _ref21;
      this.fields = this.createCollection(Cruddy.fields, attributes.fields);
      this.columns = this.createCollection(Cruddy.columns, attributes.columns);
      this.related = {};
      _ref21 = attributes.related;
      for (_i = 0, _len = _ref21.length; _i < _len; _i++) {
        item = _ref21[_i];
        this.related[item.related] = new Related(item);
      }
      if (this.get("label") === null) {
        return this.set("label", humanize(this.id));
      }
    };

    Entity.prototype.createCollection = function(factory, items) {
      var data, instance, options, _i, _len;
      data = [];
      for (_i = 0, _len = items.length; _i < _len; _i++) {
        options = items[_i];
        options.entity = this;
        instance = factory.create(options["class"], options);
        if (instance != null) {
          data.push(instance);
        }
      }
      return new Backbone.Collection(data);
    };

    Entity.prototype.createDataSource = function(columns) {
      var data;
      if (columns == null) {
        columns = null;
      }
      data = {
        order_by: this.get("order_by") || this.get("primary_column")
      };
      data.order_dir = data.order_dir != null ? this.columns.get(data.order_by).get("order_dir") : "asc";
      return new DataSource(data, {
        entity: this,
        columns: columns,
        filter: new Backbone.Model
      });
    };

    Entity.prototype.createFilters = function(columns) {
      var col, filters;
      if (columns == null) {
        columns = this.columns;
      }
      filters = (function() {
        var _i, _len, _ref21, _results;
        _ref21 = columns.models;
        _results = [];
        for (_i = 0, _len = _ref21.length; _i < _len; _i++) {
          col = _ref21[_i];
          if (col.get("filterable")) {
            _results.push(col.createFilter());
          }
        }
        return _results;
      })();
      return new Backbone.Collection(filters);
    };

    Entity.prototype.createInstance = function(attributes, related) {
      var item, key;
      if (attributes == null) {
        attributes = {};
      }
      if (related == null) {
        related = null;
      }
      if (related === null) {
        related = (function() {
          var _ref21, _results;
          _ref21 = this.related;
          _results = [];
          for (key in _ref21) {
            item = _ref21[key];
            _results.push(item.related.createInstance());
          }
          return _results;
        }).call(this);
      }
      return new EntityInstance(_.extend({}, this.get("defaults"), attributes), {
        entity: this,
        related: related
      });
    };

    Entity.prototype.search = function() {
      if (this.searchInstance == null) {
        this.searchInstance = this.createDataSource(["id", this.get("primary_column")]);
      }
      this.searchInstance.set("current_page", 1);
      return this.searchInstance;
    };

    Entity.prototype.update = function(id) {
      var _this = this;
      return $.getJSON("" + API_URL + "/" + this.id + "/" + id).then(function(resp) {
        var instance, item, key, related;
        related = (function() {
          var _ref21, _results;
          _ref21 = this.related;
          _results = [];
          for (key in _ref21) {
            item = _ref21[key];
            _results.push(item.related.createInstance(resp.data.related[item.id]));
          }
          return _results;
        }).call(_this);
        _this.set("instance", instance = _this.createInstance(resp.data.instanceData, related));
        return instance;
      });
    };

    Entity.prototype.link = function(id) {
      return ("" + this.id) + (id != null ? "/" + id : "");
    };

    return Entity;

  })(Backbone.Model);

  EntityInstance = (function(_super) {
    __extends(EntityInstance, _super);

    function EntityInstance() {
      _ref21 = EntityInstance.__super__.constructor.apply(this, arguments);
      return _ref21;
    }

    EntityInstance.prototype.initialize = function(attributes, options) {
      var _this = this;
      this.entity = options.entity;
      this.related = options.related;
      this.original = _.clone(attributes);
      this.on("error", this.processError, this);
      this.on("sync", function() {
        return _this.original = _.clone(_this.attributes);
      });
      return this.on("destroy", function() {
        if (_this.entity.get("soft_deleting")) {
          return _this.set("deleted_at", moment().unix());
        }
      });
    };

    EntityInstance.prototype.processError = function(model, xhr) {
      if ((xhr.responseJSON != null) && xhr.responseJSON.error === "VALIDATION") {
        return this.trigger("invalid", this, xhr.responseJSON.data);
      }
    };

    EntityInstance.prototype.validate = function() {
      this.set("errors", {});
      return null;
    };

    EntityInstance.prototype.link = function() {
      return this.entity.link(this.id);
    };

    EntityInstance.prototype.url = function() {
      var url;
      url = "" + API_URL + "/" + this.entity.id;
      if (this.isNew()) {
        return url;
      } else {
        return url + "/" + this.id;
      }
    };

    EntityInstance.prototype.sync = function(method, model, options) {
      var _ref22;
      if (method === "update" || method === "create") {
        options.data = new FormData;
        this.append(options.data, this.entity.id, (_ref22 = options.attrs) != null ? _ref22 : this.attributes);
        options.contentType = false;
        options.processData = false;
      }
      return EntityInstance.__super__.sync.apply(this, arguments);
    };

    EntityInstance.prototype.save = function() {
      var queue, xhr,
        _this = this;
      xhr = EntityInstance.__super__.save.apply(this, arguments);
      if (_.isEmpty(this.related)) {
        return xhr;
      }
      queue = function(xhr) {
        var related, save, _i, _len, _ref22;
        save = [];
        if (xhr != null) {
          save.push(xhr);
        }
        _ref22 = _this.related;
        for (_i = 0, _len = _ref22.length; _i < _len; _i++) {
          related = _ref22[_i];
          if (related.isNew()) {
            _this.entity.related[related.entity.id].associate(_this, related);
          }
          save.push(related.save());
        }
        return $.when.apply(save);
      };
      if (this.isNew()) {
        return xhr.then(function(resp) {
          return queue();
        });
      } else {
        return queue(xhr);
      }
    };

    EntityInstance.prototype.append = function(data, key, value) {
      var i, _i, _key, _len, _value;
      if (value instanceof File) {
        data.append(key, value);
        return;
      }
      if (_.isArray(value)) {
        if (value.length === 0) {
          return this.append(data, key, "");
        }
        for (i = _i = 0, _len = value.length; _i < _len; i = ++_i) {
          _value = value[i];
          this.append(data, key + "[" + i + "]", _value);
        }
        return;
      }
      if (_.isObject(value)) {
        for (_key in value) {
          _value = value[_key];
          this.append(data, key + "[" + _key + "]", _value);
        }
        return;
      }
      data.append(key, this.convertValue(value));
      return this;
    };

    EntityInstance.prototype.convertValue = function(value) {
      if (value === null) {
        return "";
      }
      if (value === true) {
        return 1;
      }
      if (value === false) {
        return 0;
      }
      return value;
    };

    EntityInstance.prototype.parse = function(resp) {
      return resp.data;
    };

    EntityInstance.prototype.hasChangedSinceSync = function() {
      var key, related, value, _i, _len, _ref22, _ref23;
      _ref22 = this.attributes;
      for (key in _ref22) {
        value = _ref22[key];
        if (!_.isEqual(value, this.original[key])) {
          return true;
        }
      }
      if (!this.isNew()) {
        _ref23 = this.related;
        for (_i = 0, _len = _ref23.length; _i < _len; _i++) {
          related = _ref23[_i];
          if (related.hasChangedSinceSync()) {
            return true;
          }
        }
      }
      return false;
    };

    EntityInstance.prototype.isSaveable = function() {
      return (this.isNew() && this.entity.get("can_create")) || (!this.isNew() && this.entity.get("can_update"));
    };

    return EntityInstance;

  })(Backbone.Model);

  EntityPage = (function(_super) {
    __extends(EntityPage, _super);

    EntityPage.prototype.className = "entity-page";

    EntityPage.prototype.events = {
      "click .btn-create": "create"
    };

    function EntityPage(options) {
      this.className += " " + this.className + "-" + options.model.id;
      EntityPage.__super__.constructor.apply(this, arguments);
    }

    EntityPage.prototype.initialize = function(options) {
      this.listenTo(this.model, "change:instance", this.toggleForm);
      return EntityPage.__super__.initialize.apply(this, arguments);
    };

    EntityPage.prototype.toggleForm = function(entity, instance) {
      if (this.form != null) {
        this.stopListening(this.form.model);
        this.form.remove();
      }
      if (instance != null) {
        this.listenTo(instance, "sync", function() {
          return Cruddy.router.navigate(instance.link());
        });
        this.form = new EntityForm({
          model: instance
        });
        this.$el.append(this.form.render().$el);
        this.form.show();
      }
      return this;
    };

    EntityPage.prototype.create = function() {
      Cruddy.router.navigate(this.model.link("create"), {
        trigger: true
      });
      return this;
    };

    EntityPage.prototype.render = function() {
      this.dispose();
      this.$el.html(this.template());
      this.dataSource = this.model.createDataSource();
      this.dataGrid = new DataGrid({
        model: this.dataSource
      });
      this.filterList = new FilterList({
        model: this.dataSource.filter,
        entity: this.dataSource.entity
      });
      this.dataSource.fetch();
      this.$el.append(this.filterList.render().el);
      this.$el.append(this.dataGrid.render().el);
      return this;
    };

    EntityPage.prototype.template = function() {
      var html;
      html = "<h1 class=\"page-header\">\n    " + (this.model.get("title")) + "\n";
      if (this.model.get("can_create")) {
        html += "<button class=\"btn btn-default btn-create\" type=\"button\">\n    <span class=\"glyphicon glyphicon-plus\"</span>\n</button>";
      }
      return html += "</h1>";
    };

    EntityPage.prototype.dispose = function() {
      if (this.form != null) {
        this.form.remove();
      }
      if (this.filterList != null) {
        this.filterList.remove();
      }
      if (this.dataGrid != null) {
        this.dataGrid.remove();
      }
      if (this.dataSource != null) {
        this.dataSource.stopListening();
      }
      return this;
    };

    EntityPage.prototype.remove = function() {
      this.dispose();
      return EntityPage.__super__.remove.apply(this, arguments);
    };

    return EntityPage;

  })(Backbone.View);

  EntityForm = (function(_super) {
    __extends(EntityForm, _super);

    EntityForm.prototype.className = "entity-form";

    EntityForm.prototype.events = {
      "click .btn-save": "save",
      "click .btn-close": "close",
      "click .btn-destroy": "destroy"
    };

    function EntityForm(options) {
      this.className += " " + this.className + "-" + options.model.entity.id;
      EntityForm.__super__.constructor.apply(this, arguments);
    }

    EntityForm.prototype.initialize = function() {
      var related, _i, _len, _ref22;
      this.listenTo(this.model, "destroy", this.handleDestroy);
      this.signOn(this.model);
      _ref22 = this.model.related;
      for (_i = 0, _len = _ref22.length; _i < _len; _i++) {
        related = _ref22[_i];
        this.signOn(related);
      }
      this.hotkeys = $(document).on("keydown." + this.cid, "body", $.proxy(this, "hotkeys"));
      return this;
    };

    EntityForm.prototype.signOn = function(model) {
      this.listenTo(model, "change", this.enableSubmit);
      return this.listenTo(model, "invalid", this.displayInvalid);
    };

    EntityForm.prototype.hotkeys = function(e) {
      if (e.ctrlKey && e.keyCode === 90) {
        this.model.set(this.model.previousAttributes());
        return false;
      }
      if (e.ctrlKey && e.keyCode === 13) {
        this.save();
        return false;
      }
      if (e.keyCode === 27) {
        this.close();
        return false;
      }
      return this;
    };

    EntityForm.prototype.enableSubmit = function() {
      this.submit.attr("disabled", this.model.hasChangedSinceSync() === false);
      return this;
    };

    EntityForm.prototype.displayAlert = function(message, type) {
      if (this.alert != null) {
        this.alert.remove();
      }
      this.alert = new Alert({
        message: message,
        className: "flash",
        type: type,
        timeout: 3000
      });
      this.footer.prepend(this.alert.render().el);
      return this;
    };

    EntityForm.prototype.displaySuccess = function(resp) {
      return this.displayAlert("Получилось!", "success");
    };

    EntityForm.prototype.displayInvalid = function() {
      return this.displayAlert("Не получилось...", "warning");
    };

    EntityForm.prototype.displayError = function(xhr) {
      if (!((xhr.responseJSON != null) && xhr.responseJSON.error === "VALIDATION")) {
        return this.displayAlert("Ошибка", "danger");
      }
    };

    EntityForm.prototype.handleDestroy = function() {
      if (this.model.entity.get("soft_deleting")) {
        this.update();
      } else {
        Cruddy.router.navigate(this.model.entity.link(), {
          trigger: true
        });
      }
      return this;
    };

    EntityForm.prototype.show = function() {
      var _this = this;
      setTimeout((function() {
        _this.$el.toggleClass("opened", true);
        return _this.tabs[0].focus();
      }), 50);
      return this;
    };

    EntityForm.prototype.save = function() {
      var _this = this;
      if ((this.request != null) || !this.model.hasChangedSinceSync()) {
        return;
      }
      this.request = this.model.save().then($.proxy(this, "displaySuccess"), $.proxy(this, "displayError"));
      this.request.always(function() {
        _this.request = null;
        return _this.update();
      });
      this.update();
      return this;
    };

    EntityForm.prototype.close = function() {
      var confirmed;
      if (this.request) {
        confirmed = confirm("Вы точно хотите закрыть форму и отменить операцию?");
      } else {
        confirmed = this.model.hasChangedSinceSync() ? confirm("Вы точно хотите закрыть форму? Все изменения будут утеряны!") : true;
      }
      if (confirmed) {
        if (this.request) {
          this.request.abort();
        }
        Cruddy.router.navigate(this.model.entity.link(), {
          trigger: true
        });
      }
      return this;
    };

    EntityForm.prototype.destroy = function() {
      var confirmed, softDeleting;
      if (this.request || this.model.isNew()) {
        return;
      }
      softDeleting = this.model.entity.get("soft_deleting");
      confirmed = !softDeleting ? confirm("Точно удалить? Восстановить не получится!") : true;
      this.request = this.softDeleting && this.model.get("deleted_at") ? this.model.restore : confirmed ? this.model.destroy({
        wait: true
      }) : void 0;
      return this;
    };

    EntityForm.prototype.render = function() {
      var related, _i, _len, _ref22;
      this.dispose();
      this.$el.html(this.template());
      this.nav = this.$(".nav");
      this.footer = this.$("footer");
      this.submit = this.$(".btn-save");
      this.destroy = this.$(".btn-destroy");
      this.tabs = [];
      this.renderTab(this.model, true);
      _ref22 = this.model.related;
      for (_i = 0, _len = _ref22.length; _i < _len; _i++) {
        related = _ref22[_i];
        this.renderTab(related);
      }
      return this.update();
    };

    EntityForm.prototype.renderTab = function(model, active) {
      var fieldList, id;
      this.tabs.push(fieldList = new FieldList({
        model: model
      }));
      id = "tab-" + model.entity.id;
      fieldList.render().$el.insertBefore(this.footer).wrap($("<div></div>", {
        id: id,
        "class": "wrap" + (active ? " active" : "")
      }));
      this.nav.append(this.navTemplate(model.entity.get("singular"), id, active));
      return this;
    };

    EntityForm.prototype.update = function() {
      this.$el.toggleClass("loading", this.request != null);
      this.submit.text(this.model.isNew() ? "Создать" : "Сохранить");
      this.submit.attr("disabled", this.model.hasChangedSinceSync() === false || this.request === true);
      this.submit.toggle(this.model.entity.get(this.model.isNew() ? "can_create" : "can_update"));
      this.destroy.attr("disabled", this.request === true);
      this.destroy.text(this.model.entity.get("soft_deleting" && this.model.get("deleted_at")) ? "Восстановить" : "Удалить");
      this.destroy.toggle(!this.model.isNew() && this.model.entity.get("can_delete"));
      return this;
    };

    EntityForm.prototype.template = function() {
      return "<header>\n    <ul class=\"nav nav-pills\"></ul>\n</header>\n\n<footer>\n    <button class=\"btn btn-default btn-close btn-sm\" type=\"button\">Закрыть</button>\n    <button class=\"btn btn-default btn-destroy btn-sm\" type=\"button\">Удалить</button>\n    <button class=\"btn btn-primary btn-save btn-sm\" type=\"button\" disabled></button>\n</footer>";
    };

    EntityForm.prototype.navTemplate = function(label, target, active) {
      active = active ? " class=\"active\"" : "";
      return "<li" + active + "><a href=\"#" + target + "\" data-toggle=\"tab\">" + label + "</a></li>";
    };

    EntityForm.prototype.remove = function() {
      var _this = this;
      this.$el.one(TRANSITIONEND, function() {
        _this.dispose();
        $(document).off("." + _this.cid);
        return EntityForm.__super__.remove.apply(_this, arguments);
      }).removeClass("opened");
      return this;
    };

    EntityForm.prototype.dispose = function() {
      var fieldList, _i, _len, _ref22;
      if (this.tabs != null) {
        _ref22 = this.tabs;
        for (_i = 0, _len = _ref22.length; _i < _len; _i++) {
          fieldList = _ref22[_i];
          fieldList.remove();
        }
      }
      return this;
    };

    return EntityForm;

  })(Backbone.View);

  Related = (function(_super) {
    __extends(Related, _super);

    function Related() {
      _ref22 = Related.__super__.constructor.apply(this, arguments);
      return _ref22;
    }

    Related.prototype.resolve = function() {
      var _this = this;
      return Cruddy.app.entity(this.get("related")).then(function(entity) {
        return _this.related = entity;
      });
    };

    Related.prototype.associate = function(parent, child) {
      return child.set(this.get("foreign_key"), parent.id);
    };

    return Related;

  })(Backbone.Model);

  $(".navbar").on("click", ".entity", function(e) {
    var baseUrl, href;
    e.preventDefault();
    baseUrl = Cruddy.root + "/" + Cruddy.uri + "/";
    href = e.currentTarget.href.substr(baseUrl.length);
    return Cruddy.router.navigate(href, {
      trigger: true
    });
  });

  App = (function(_super) {
    __extends(App, _super);

    function App() {
      _ref23 = App.__super__.constructor.apply(this, arguments);
      return _ref23;
    }

    App.prototype.entities = {};

    App.prototype.initialize = function() {
      this.container = $("#container");
      return this.on("change:entity", this.displayEntity, this);
    };

    App.prototype.displayEntity = function(model, entity) {
      if (this.page != null) {
        this.page.remove();
      }
      if (entity != null) {
        return this.container.append((this.page = new EntityPage({
          model: entity
        })).render().el);
      }
    };

    App.prototype.entity = function(id) {
      var promise,
        _this = this;
      if (id in this.entities) {
        promise = $.Deferred().resolve(this.entities[id]).promise();
      } else {
        promise = this.fields(id).then(function(resp) {
          var entity, key, related, wait;
          _this.entities[id] = entity = new Entity(resp.data);
          if (_.isEmpty(entity.related)) {
            return entity;
          }
          wait = (function() {
            var _ref24, _results;
            _ref24 = entity.related;
            _results = [];
            for (key in _ref24) {
              related = _ref24[key];
              _results.push(related.resolve());
            }
            return _results;
          })();
          return $.when.apply($, wait).then(function() {
            return entity;
          });
        });
      }
      return promise;
    };

    App.prototype.fields = function(id) {
      return $.getJSON("" + API_URL + "/" + id + "/entity");
    };

    return App;

  })(Backbone.Model);

  Cruddy.app = new App;

  Router = (function(_super) {
    __extends(Router, _super);

    function Router() {
      _ref24 = Router.__super__.constructor.apply(this, arguments);
      return _ref24;
    }

    Router.prototype.routes = {
      ":page": "page",
      ":page/create": "create",
      ":page/:id": "update"
    };

    Router.prototype.page = function(page) {
      return Cruddy.app.entity(page).then(function(entity) {
        entity.set("instance", null);
        Cruddy.app.set("entity", entity);
        return entity;
      });
    };

    Router.prototype.create = function(page) {
      return this.page(page).then(function(entity) {
        return entity.set("instance", entity.createInstance());
      });
    };

    Router.prototype.update = function(page, id) {
      return this.page(page).then(function(entity) {
        return entity.update(id);
      });
    };

    return Router;

  })(Backbone.Router);

  Cruddy.router = new Router;

  Backbone.history.start({
    root: Cruddy.uri + "/",
    pushState: true,
    hashChange: false
  });

}).call(this);

/*
//@ sourceMappingURL=app.js.map
*/