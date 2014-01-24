(function() {
  var API_URL, AdvFormData, Alert, App, Attribute, BaseFormatter, BaseInput, BooleanInput, Checkbox, Column, Cruddy, DataGrid, DataSource, Entity, EntityDropdown, EntityForm, EntityInstance, EntityPage, EntitySelector, Factory, Field, FieldList, FieldView, FileList, FilterList, ImageList, Pagination, Related, Router, SearchDataSource, SearchInput, SelectInput, SlugInput, StaticInput, TRANSITIONEND, TextInput, Textarea, after_break, entity_url, humanize, thumb, _ref, _ref1, _ref10, _ref11, _ref12, _ref13, _ref14, _ref15, _ref16, _ref17, _ref18, _ref19, _ref2, _ref20, _ref21, _ref22, _ref23, _ref24, _ref25, _ref26, _ref27, _ref28, _ref29, _ref3, _ref30, _ref31, _ref32, _ref33, _ref34, _ref35, _ref36, _ref37, _ref4, _ref5, _ref6, _ref7, _ref8, _ref9,
    _this = this,
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  Cruddy = window.Cruddy || {};

  Cruddy.baseUrl = Cruddy.root + "/" + Cruddy.uri;

  API_URL = "/backend/api/v1";

  TRANSITIONEND = "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd";

  moment.lang((_ref = Cruddy.locale) != null ? _ref : "en");

  Backbone.emulateHTTP = true;

  Backbone.emulateJSON = true;

  $(document).ajaxSend(function(e, xhr, options) {
    if (options.displayLoading) {
      return Cruddy.app.startLoading();
    }
  }).ajaxComplete(function(e, xhr, options) {
    if (options.displayLoading) {
      return Cruddy.app.doneLoading();
    }
  });

  $.extend($.fancybox.defaults, {
    openEffect: "elastic"
  });

  humanize = function(id) {
    return id.replace(/_-/, " ");
  };

  entity_url = function(id, extra) {
    var url;
    url = Cruddy.baseUrl + "/api/v1/entity/" + id;
    if (extra) {
      url += "/" + extra;
    }
    return url;
  };

  after_break = function(callback) {
    return setTimeout(callback, 50);
  };

  thumb = function(src, width, height) {
    var url;
    url = "" + Cruddy.baseUrl + "/thumb?src=" + (encodeURIComponent(src));
    if (width) {
      url += "&amp;width=" + width;
    }
    if (height) {
      url += "&amp;height=" + height;
    }
    return url;
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

  AdvFormData = (function() {
    function AdvFormData(data) {
      this.original = new FormData;
      if (data != null) {
        this.append(data);
      }
    }

    AdvFormData.prototype.append = function(name, value) {
      var key, _i, _len, _value;
      if (value === void 0) {
        value = name;
        name = null;
      }
      if (value instanceof File || value instanceof Blob) {
        return this.original.append(name, value);
      }
      if (_.isArray(value)) {
        if (_.isEmpty(value)) {
          return this.append(name, "");
        }
        for (key = _i = 0, _len = value.length; _i < _len; key = ++_i) {
          _value = value[key];
          this.append(this.key(name, key), _value);
        }
        return;
      }
      if (_.isObject(value)) {
        for (key in value) {
          _value = value[key];
          this.append(this.key(name, key), _value);
        }
        return;
      }
      return this.original.append(name, this.process(value));
    };

    AdvFormData.prototype.process = function(value) {
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

    AdvFormData.prototype.key = function(outer, inner) {
      if (outer) {
        return "" + outer + "[" + inner + "]";
      } else {
        return inner;
      }
    };

    return AdvFormData;

  })();

  Factory = (function() {
    function Factory() {}

    Factory.prototype.create = function(name, options) {
      var constructor;
      constructor = this[name];
      if (constructor != null) {
        return new constructor(options);
      }
      console.error("Failed to resolve " + name + ".");
      return null;
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
      data: [],
      search: ""
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
      this.options = {
        url: this.entity.url(),
        dataType: "json",
        type: "get",
        displayLoading: true,
        success: function(resp) {
          _this._hold = true;
          _this.set(resp.data);
          _this._hold = false;
          return _this.trigger("data", _this, resp.data.data);
        },
        error: function(xhr) {
          return _this.trigger("error", _this, xhr);
        }
      };
      if (this.filter != null) {
        this.listenTo(this.filter, "change", (function() {
          _this.set({
            current_page: 1,
            silent: true
          });
          return _this.fetch();
        }));
      }
      this.on("change", function() {
        if (!_this._hold) {
          return _this.fetch();
        }
      });
      return this.on("change:search", function() {
        return _this.set({
          current_page: 1,
          silent: true
        });
      });
    };

    DataSource.prototype.hasData = function() {
      return !_.isEmpty(this.get("data"));
    };

    DataSource.prototype.hasMore = function() {
      return this.get("current_page") < this.get("last_page");
    };

    DataSource.prototype.isFull = function() {
      return !this.hasMore();
    };

    DataSource.prototype.inProgress = function() {
      return this.request != null;
    };

    DataSource.prototype.fetch = function() {
      var _this = this;
      if (this.request != null) {
        this.request.abort();
      }
      this.options.data = this.data();
      this.request = $.ajax(this.options);
      this.request.always(function() {
        return _this.request = null;
      });
      this.trigger("request", this, this.request);
      return this.request;
    };

    DataSource.prototype.more = function() {
      if (this.isFull()) {
        return;
      }
      this.set({
        current_page: this.get("current_page") + 1,
        silent: true
      });
      return this.fetch();
    };

    DataSource.prototype.data = function() {
      var data, filters;
      data = {
        order_by: this.get("order_by"),
        order_dir: this.get("order_dir"),
        page: this.get("current_page"),
        per_page: this.get("per_page"),
        q: this.get("search")
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

  SearchDataSource = (function(_super) {
    __extends(SearchDataSource, _super);

    function SearchDataSource() {
      _ref4 = SearchDataSource.__super__.constructor.apply(this, arguments);
      return _ref4;
    }

    SearchDataSource.prototype.defaults = {
      search: ""
    };

    SearchDataSource.prototype.initialize = function(attributes, options) {
      var keyName, valueName, _ref5,
        _this = this;
      keyName = (_ref5 = options.primaryKey) != null ? _ref5 : "id";
      valueName = options.primaryColumn;
      this.options = {
        url: options.url,
        type: "get",
        dataType: "json",
        data: {
          page: null,
          q: "",
          columns: keyName + "," + valueName
        },
        success: function(resp) {
          var item, _i, _len, _ref6;
          resp = resp.data;
          _ref6 = resp.data;
          for (_i = 0, _len = _ref6.length; _i < _len; _i++) {
            item = _ref6[_i];
            _this.data.push({
              id: item[keyName].toString(),
              title: item[valueName]
            });
          }
          _this.page = resp.current_page;
          _this.more = resp.current_page < resp.last_page;
          _this.request = null;
          _this.trigger("data", _this, _this.data);
          return _this;
        },
        error: function(xhr) {
          _this.request = null;
          _this.trigger("error", _this, xhr);
          return _this;
        }
      };
      if (options.ajaxOptions != null) {
        $.extend(this.options, options.ajaxOptions);
      }
      this.reset();
      this.on("change:search", function() {
        return _this.reset().next();
      });
      return this;
    };

    SearchDataSource.prototype.reset = function() {
      this.data = [];
      this.page = null;
      this.more = true;
      return this;
    };

    SearchDataSource.prototype.fetch = function(q, page) {
      if (this.request != null) {
        this.request.abort();
      }
      $.extend(this.options.data, {
        page: page,
        q: q
      });
      this.trigger("request", this, this.request = $.ajax(this.options));
      return this.request;
    };

    SearchDataSource.prototype.next = function() {
      var page;
      if (this.more) {
        page = this.page != null ? this.page + 1 : 1;
        this.fetch(this.get("search"), page);
      }
      return this;
    };

    SearchDataSource.prototype.inProgress = function() {
      return this.request != null;
    };

    return SearchDataSource;

  })(Backbone.Model);

  Pagination = (function(_super) {
    __extends(Pagination, _super);

    function Pagination() {
      _ref5 = Pagination.__super__.constructor.apply(this, arguments);
      return _ref5;
    }

    Pagination.prototype.tagName = "ul";

    Pagination.prototype.className = "pager";

    Pagination.prototype.events = {
      "click a": "navigate"
    };

    Pagination.prototype.initialize = function(options) {
      this.listenTo(this.model, "data", this.render);
      this.listenTo(this.model, "request", this.disable);
      $(document).on("keydown.pagination", $.proxy(this, "hotkeys"));
      return this;
    };

    Pagination.prototype.hotkeys = function(e) {
      if (e.ctrlKey && e.keyCode === 37) {
        this.previous();
        return false;
      }
      if (e.ctrlKey && e.keyCode === 39) {
        this.next();
        return false;
      }
      return this;
    };

    Pagination.prototype.page = function(n) {
      if (n > 0 && n <= this.model.get("last_page")) {
        this.model.set("current_page", n);
      }
      return this;
    };

    Pagination.prototype.previous = function() {
      return this.page(this.model.get("current_page") - 1);
    };

    Pagination.prototype.next = function() {
      return this.page(this.model.get("current_page") + 1);
    };

    Pagination.prototype.navigate = function(e) {
      e.preventDefault();
      if (!this.model.inProgress()) {
        return this.page($(e.target).data("page"));
      }
    };

    Pagination.prototype.disable = function() {
      this.$("a").addClass("disabled");
      return this;
    };

    Pagination.prototype.render = function() {
      var last;
      last = this.model.get("last_page");
      this.$el.toggle((last != null) && last > 1);
      if (last > 1) {
        this.$el.html(this.template(this.model.get("current_page"), last));
      }
      return this;
    };

    Pagination.prototype.template = function(current, last) {
      var html;
      html = "";
      html += this.renderLink(current - 1, "&larr; Назад", "previous" + (current > 1 ? "" : " disabled"));
      if (this.model.get("total") != null) {
        html += this.renderStats();
      }
      html += this.renderLink(current + 1, "Вперед &rarr;", "next" + (current < last ? "" : " disabled"));
      return html;
    };

    Pagination.prototype.renderStats = function() {
      return "<li class=\"stats\"><span>" + (this.model.get("from")) + " - " + (this.model.get("to")) + " / " + (this.model.get("total")) + "</span></li>";
    };

    Pagination.prototype.renderLink = function(page, label, className) {
      if (className == null) {
        className = "";
      }
      return "<li class=\"" + className + "\"><a href=\"#\" data-page=\"" + page + "\">" + label + "</a></li>";
    };

    return Pagination;

  })(Backbone.View);

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
      this.columns = this.entity.columns.models.filter(function(col) {
        return col.get("visible");
      });
      this.listenTo(this.model, "data", this.updateData);
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
      this.$(".items").replaceWith(this.renderBody(this.columns, data));
      return this;
    };

    DataGrid.prototype.render = function() {
      var data;
      data = this.model.get("data");
      this.$el.html(this.renderHead(this.columns) + this.renderBody(this.columns, data));
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
      _ref6 = FieldList.__super__.constructor.apply(this, arguments);
      return _ref6;
    }

    FieldList.prototype.className = "field-list";

    FieldList.prototype.initialize = function() {
      this.listenTo(this.model.entity.fields, "add remove", this.render);
      return this;
    };

    FieldList.prototype.focus = function() {
      var _ref7;
      if ((_ref7 = this.primary) != null) {
        _ref7.focus();
      }
      return this;
    };

    FieldList.prototype.render = function() {
      var field, _i, _len, _ref7;
      this.$el.empty();
      _ref7 = this.createFields();
      for (_i = 0, _len = _ref7.length; _i < _len; _i++) {
        field = _ref7[_i];
        this.$el.append(field.el);
      }
      return this;
    };

    FieldList.prototype.createFields = function() {
      var field, view, _i, _len, _ref7;
      this.dispose();
      this.fields = (function() {
        var _i, _len, _ref7, _results;
        _ref7 = this.model.entity.fields.models;
        _results = [];
        for (_i = 0, _len = _ref7.length; _i < _len; _i++) {
          field = _ref7[_i];
          _results.push(field.createView(this.model).render());
        }
        return _results;
      }).call(this);
      this.primary = null;
      _ref7 = this.fields;
      for (_i = 0, _len = _ref7.length; _i < _len; _i++) {
        view = _ref7[_i];
        if (!(view.field.isEditable(this.model))) {
          continue;
        }
        this.primary = view;
        break;
      }
      return this.fields;
    };

    FieldList.prototype.dispose = function() {
      var field, _i, _len, _ref7;
      if (this.fields != null) {
        _ref7 = this.fields;
        for (_i = 0, _len = _ref7.length; _i < _len; _i++) {
          field = _ref7[_i];
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
      _ref7 = FilterList.__super__.constructor.apply(this, arguments);
      return _ref7;
    }

    FilterList.prototype.className = "filter-list";

    FilterList.prototype.tagName = "fieldset";

    FilterList.prototype.initialize = function(options) {
      this.entity = options.entity;
      return this;
    };

    FilterList.prototype.render = function() {
      var col, input, _i, _len, _ref8;
      this.dispose();
      this.$el.html(this.template());
      this.items = this.$(".filter-list-container");
      this.filters = [];
      _ref8 = this.entity.columns.models;
      for (_i = 0, _len = _ref8.length; _i < _len; _i++) {
        col = _ref8[_i];
        if (!col.get("searchable") && col.get("filterable")) {
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
      var filter, _i, _len, _ref8;
      if (this.filters != null) {
        _ref8 = this.filters;
        for (_i = 0, _len = _ref8.length; _i < _len; _i++) {
          filter = _ref8[_i];
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
      _ref8 = StaticInput.__super__.constructor.apply(this, arguments);
      return _ref8;
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

    TextInput.prototype.events = {
      "change": "change",
      "keydown": "keydown"
    };

    function TextInput(options) {
      var _ref9;
      if (options.className == null) {
        options.className = "form-control";
      }
      options.className += " input-" + ((_ref9 = options.size) != null ? _ref9 : "sm");
      TextInput.__super__.constructor.apply(this, arguments);
    }

    TextInput.prototype.keydown = function(e) {
      if (e.ctrlKey && e.keyCode === 13) {
        return this.change();
      }
      return this;
    };

    TextInput.prototype.disable = function() {
      this.$el.prop("disabled", true);
      return this;
    };

    TextInput.prototype.enable = function() {
      this.$el.prop("disabled", false);
      return this;
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
      _ref9 = Textarea.__super__.constructor.apply(this, arguments);
      return _ref9;
    }

    Textarea.prototype.tagName = "textarea";

    return Textarea;

  })(TextInput);

  Checkbox = (function(_super) {
    __extends(Checkbox, _super);

    function Checkbox() {
      _ref10 = Checkbox.__super__.constructor.apply(this, arguments);
      return _ref10;
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
      _ref11 = BooleanInput.__super__.constructor.apply(this, arguments);
      return _ref11;
    }

    BooleanInput.prototype.tripleState = false;

    BooleanInput.prototype.events = {
      "click .btn": "check"
    };

    BooleanInput.prototype.initialize = function(options) {
      if (options.tripleState != null) {
        this.tripleState = options.tripleState;
      }
      return BooleanInput.__super__.initialize.apply(this, arguments);
    };

    BooleanInput.prototype.check = function(e) {
      var currentValue, value;
      value = !!$(e.target).data("value");
      currentValue = this.model.get(this.key);
      if (value === currentValue && this.tripleState) {
        value = null;
      }
      this.model.set(this.key, value);
      return this;
    };

    BooleanInput.prototype.applyChanges = function(model, value) {
      value = (function() {
        switch (value) {
          case true:
            return 0;
          case false:
            return 1;
          default:
            return null;
        }
      })();
      this.values.removeClass("active");
      if (value != null) {
        this.values.eq(value).addClass("active");
      }
      return this;
    };

    BooleanInput.prototype.render = function() {
      this.$el.html(this.template());
      this.values = this.$(".btn");
      return BooleanInput.__super__.render.apply(this, arguments);
    };

    BooleanInput.prototype.template = function() {
      return "<div class=\"btn-group btn-group-sm\">\n    <button type=\"button\" class=\"btn btn-info\" data-value=\"1\">да</button>\n    <button type=\"button\" class=\"btn btn-default\" data-value=\"0\">нет</button>\n</div>";
    };

    BooleanInput.prototype.itemTemplate = function(label, value) {
      return "<label class=\"radio-inline\">\n    <input type=\"radio\" name=\"" + this.cid + "\" value=\"" + value + "\">\n    " + label + "\n</label>";
    };

    return BooleanInput;

  })(BaseInput);

  EntityDropdown = (function(_super) {
    __extends(EntityDropdown, _super);

    function EntityDropdown() {
      _ref12 = EntityDropdown.__super__.constructor.apply(this, arguments);
      return _ref12;
    }

    EntityDropdown.prototype.className = "entity-dropdown";

    EntityDropdown.prototype.events = {
      "click .btn-remove": "removeItem",
      "click .btn-edit": "editItem",
      "keydown [type=search]": "searchKeydown",
      "show.bs.dropdown": "renderDropdown",
      "shown.bs.dropdown": function() {
        var _this = this;
        after_break(function() {
          return _this.selector.focus();
        });
        return this;
      },
      "hidden.bs.dropdown": function() {
        this.opened = false;
        return this;
      }
    };

    EntityDropdown.prototype.mutiple = false;

    EntityDropdown.prototype.reference = null;

    EntityDropdown.prototype.initialize = function(options) {
      var _ref13;
      if (options.multiple != null) {
        this.multiple = options.multiple;
      }
      if (options.reference != null) {
        this.reference = options.reference;
      }
      this.allowEdit = (_ref13 = options.allowEdit) != null ? _ref13 : true;
      this.active = false;
      return EntityDropdown.__super__.initialize.apply(this, arguments);
    };

    EntityDropdown.prototype.getKey = function(e) {
      return $(e.currentTarget).closest(".ed-item").data("key");
    };

    EntityDropdown.prototype.removeItem = function(e) {
      var i, value;
      if (this.multiple) {
        i = this.getKey(e);
        value = _.clone(this.model.get(this.key));
        value.splice(i, 1);
      } else {
        value = null;
      }
      this.model.set(this.key, value);
      return this;
    };

    EntityDropdown.prototype.editItem = function(e) {
      var item, target, xhr,
        _this = this;
      item = this.model.get(this.key);
      if (this.multiple) {
        item = item[this.getKey(e)];
      }
      if (!item) {
        return;
      }
      target = $(e.currentTarget).prop("disabled", true);
      xhr = Cruddy.app.entity(this.reference).then(function(entity) {
        return entity.load(item.id).done(function(instance) {
          _this.innerForm = new EntityForm({
            model: instance,
            inner: true
          });
          _this.innerForm.render().$el.appendTo(document.body);
          after_break(function() {
            return _this.innerForm.show();
          });
          _this.listenTo(instance, "sync", function(model, resp) {
            if (resp.data) {
              target.parent().siblings("input").val(resp.data.title);
              return _this.innerForm.remove();
            } else {
              return _this.removeItem(e);
            }
          });
          return _this.listenTo(_this.innerForm, "remove", function() {
            return _this.innerForm = null;
          });
        });
      });
      xhr.always(function() {
        return target.prop("disabled", false);
      });
      return this;
    };

    EntityDropdown.prototype.searchKeydown = function(e) {
      if (e.keyCode === 27) {
        this.$el.dropdown("toggle");
        return false;
      }
    };

    EntityDropdown.prototype.renderDropdown = function() {
      var _this = this;
      this.opened = true;
      if (this.selector != null) {
        return this.toggleOpenDirection();
      }
      this.selector = new EntitySelector({
        model: this.model,
        key: this.key,
        multiple: this.multiple,
        reference: this.reference,
        allowCreate: this.allowEdit
      });
      this.selector.render().entity.done(function() {
        return _this.$el.append(_this.selector.el);
      });
      return this.toggleOpenDirection();
    };

    EntityDropdown.prototype.toggleOpenDirection = function() {
      var space, targetClass, wnd;
      if (!this.opened) {
        return;
      }
      wnd = $(window);
      space = wnd.height() - this.$el.offset().top - wnd.scrollTop() - this.$el.parent(".field-list").scrollTop();
      targetClass = space > 292 ? "open-down" : "open-up";
      if (!this.$el.hasClass(targetClass)) {
        this.$el.removeClass("open-up open-down").addClass(targetClass);
      }
      return this;
    };

    EntityDropdown.prototype.applyChanges = function(model, value) {
      if (this.multiple) {
        this.renderItems();
      } else {
        this.updateItem();
        this.$el.removeClass("open");
      }
      this.toggleOpenDirection();
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
      this.$el.append("<button type=\"button\" class=\"btn btn-default btn-sm btn-block dropdown-toggle ed-dropdown-toggle\" data-toggle=\"dropdown\" data-target=\"#" + this.cid + "\">\n    Выбрать\n    <span class=\"caret\"></span>\n</button>");
      return this.renderItems();
    };

    EntityDropdown.prototype.renderItems = function() {
      var html, key, value, _i, _len, _ref13;
      html = "";
      _ref13 = this.model.get(this.key);
      for (key = _i = 0, _len = _ref13.length; _i < _len; key = ++_i) {
        value = _ref13[key];
        html += this.itemTemplate(value.title, key);
      }
      this.items.html(html);
      this.items.toggleClass("has-items", html !== "");
      return this;
    };

    EntityDropdown.prototype.renderSingle = function() {
      this.$el.html(this.itemTemplate("", "0"));
      this.itemTitle = this.$(".form-control");
      this.itemDelete = this.$(".btn-remove");
      this.itemEdit = this.$(".btn-edit");
      return this.updateItem();
    };

    EntityDropdown.prototype.updateItem = function() {
      var value;
      value = this.model.get(this.key);
      this.itemTitle.val(value ? value.title : "Не выбрано");
      this.itemDelete.toggle(!!value);
      this.itemEdit.toggle(!!value);
      return this;
    };

    EntityDropdown.prototype.itemTemplate = function(value, key) {
      var html;
      if (key == null) {
        key = null;
      }
      html = "<div class=\"input-group input-group-sm ed-item " + (!this.multiple ? "ed-dropdown-toggle" : "") + "\" data-key=\"" + key + "\">\n    <input type=\"text\" class=\"form-control\" " + (!this.multiple ? "data-toggle='dropdown' data-target='#" + this.cid + "'" : "tab-index='-1'") + " value=\"" + (_.escape(value)) + "\" readonly>\n    <div class=\"input-group-btn\">";
      if (this.allowEdit) {
        html += "<button type=\"button\" class=\"btn btn-default btn-edit\" tabindex=\"-1\">\n    <span class=\"glyphicon glyphicon-pencil\"></span>\n</button>";
      }
      html += "<button type=\"button\" class=\"btn btn-default btn-remove\" tabindex=\"-1\">\n    <span class=\"glyphicon glyphicon-remove\"></span>\n</button>";
      if (!this.multiple) {
        html += "<button type=\"button\" class=\"btn btn-default btn-dropdown dropdown-toggle\" data-toggle=\"dropdown\" data-target=\"#" + this.cid + "\" tab-index=\"1\">\n    <span class=\"glyphicon glyphicon-search\"></span>\n</button>";
      }
      return html += "</div></div>";
    };

    EntityDropdown.prototype.dispose = function() {
      var _ref13, _ref14;
      if ((_ref13 = this.selector) != null) {
        _ref13.remove();
      }
      if ((_ref14 = this.innerForm) != null) {
        _ref14.remove();
      }
      return this;
    };

    EntityDropdown.prototype.remove = function() {
      this.dispose();
      return EntityDropdown.__super__.remove.apply(this, arguments);
    };

    return EntityDropdown;

  })(BaseInput);

  EntitySelector = (function(_super) {
    __extends(EntitySelector, _super);

    function EntitySelector() {
      _ref13 = EntitySelector.__super__.constructor.apply(this, arguments);
      return _ref13;
    }

    EntitySelector.prototype.className = "entity-selector";

    EntitySelector.prototype.events = {
      "click .item": "check",
      "click .more": "more",
      "click .btn-add": "add",
      "click [type=search]": function() {
        return false;
      }
    };

    EntitySelector.prototype.initialize = function(options) {
      var _ref14, _ref15, _ref16, _ref17,
        _this = this;
      EntitySelector.__super__.initialize.apply(this, arguments);
      this.filter = (_ref14 = options.filter) != null ? _ref14 : false;
      this.multiple = (_ref15 = options.multiple) != null ? _ref15 : false;
      this.allowSearch = (_ref16 = options.allowSearch) != null ? _ref16 : true;
      this.allowCreate = (_ref17 = options.allowCreate) != null ? _ref17 : true;
      this.data = [];
      this.buildSelected(this.model.get(this.key));
      this.entity = Cruddy.app.entity(options.reference);
      this.entity.done(function(entity) {
        _this.primaryKey = "id";
        _this.primaryColumn = entity.get("primary_column");
        _this.dataSource = entity.search();
        _this.listenTo(_this.dataSource, "request", _this.loading);
        _this.listenTo(_this.dataSource, "data", _this.renderItems);
        return _this.listenTo(_this.dataSource, "error", _this.displayError);
      });
      this.entity.fail($.proxy(this, "displayError"));
      return this;
    };

    EntitySelector.prototype.checkForMore = function() {
      if ((this.moreElement != null) && this.items.parent().height() + 50 > this.moreElement.position().top) {
        this.more();
      }
      return this;
    };

    EntitySelector.prototype.check = function(e) {
      var id;
      id = $(e.target).data("id").toString();
      this.select(_.find(this.dataSource.data, function(item) {
        return item.id === id;
      }));
      return false;
    };

    EntitySelector.prototype.select = function(item) {
      var value;
      if (this.multiple) {
        if (item.id in this.selected) {
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
      return this;
    };

    EntitySelector.prototype.more = function() {
      if (!this.dataSource || this.dataSource.inProgress()) {
        return;
      }
      this.dataSource.next();
      return false;
    };

    EntitySelector.prototype.add = function(e) {
      var target,
        _this = this;
      e.preventDefault();
      e.stopPropagation();
      target = $(e.currentTarget).prop("disabled", true);
      this.entity.always(function() {
        return target.prop("disabled", false);
      });
      this.entity.done(function(entity) {
        var attrs, instance, primaryColumn;
        attrs = {};
        primaryColumn = entity.get("primary_column");
        if (entity.columns.get(primaryColumn) instanceof Cruddy.columns.Field) {
          attrs[primaryColumn] = _this.dataSource.get("search");
        }
        instance = entity.createInstance(attrs);
        _this.innerForm = new EntityForm({
          model: instance,
          inner: true
        });
        _this.innerForm.render().$el.appendTo(document.body);
        after_break(function() {
          return _this.innerForm.show();
        });
        _this.listenToOnce(_this.innerForm, "remove", function() {
          return _this.innerForm = null;
        });
        return _this.listenToOnce(instance, "sync", function(instance, resp) {
          _this.select({
            id: instance.id,
            title: resp.data.title
          });
          _this.dataSource.set("search", "");
          return _this.innerForm.remove();
        });
      });
      return this;
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

    EntitySelector.prototype.displayError = function(xhr) {
      if (xhr.status !== 403) {
        return;
      }
      this.$el.html("<span class=error>Ошибка доступа</span>");
      return this;
    };

    EntitySelector.prototype.loading = function() {
      var _ref14;
      if ((_ref14 = this.moreElement) != null) {
        _ref14.addClass("loading");
      }
      return this;
    };

    EntitySelector.prototype.renderItems = function() {
      var html, item, _i, _len, _ref14;
      this.moreElement = null;
      html = "";
      if (this.dataSource.data.length || this.dataSource.more) {
        _ref14 = this.dataSource.data;
        for (_i = 0, _len = _ref14.length; _i < _len; _i++) {
          item = _ref14[_i];
          html += this.renderItem(item);
        }
        if (this.dataSource.more) {
          html += "<li class=\"more " + (this.dataSource.inProgress() ? "loading" : "") + "\">еще</li>";
        }
      } else {
        html += "<li class='empty'>нет результатов</li>";
      }
      this.items.html(html);
      if (this.dataSource.more) {
        this.moreElement = this.items.children(".more");
        this.checkForMore();
      }
      return this;
    };

    EntitySelector.prototype.renderItem = function(item) {
      var className;
      className = item.id in this.selected ? "selected" : "";
      return "<li class=\"item " + className + "\" data-id=\"" + item.id + "\">" + item.title + "</li>";
    };

    EntitySelector.prototype.render = function() {
      var _this = this;
      this.dispose();
      this.$el.html(this.template());
      this.items = this.$(".items");
      this.entity.done(function() {
        _this.renderItems();
        _this.items.parent().on("scroll", $.proxy(_this, "checkForMore"));
        if (_this.allowSearch) {
          return _this.renderSearch();
        }
      });
      return this;
    };

    EntitySelector.prototype.renderSearch = function() {
      this.searchInput = new SearchInput({
        model: this.dataSource,
        key: "search"
      });
      this.$el.prepend(this.searchInput.render().el);
      this.searchInput.$el.wrap("<div class='" + (this.allowCreate ? "input-group input-group-sm" : "") + " search-input-container'></div>");
      if (this.allowCreate) {
        this.searchInput.$el.after("<div class='input-group-btn'>\n    <button type='button' class='btn btn-default btn-add' tabindex='-1'>\n        <span class='glyphicon glyphicon-plus'></span>\n    </button>\n</div>");
      }
      return this;
    };

    EntitySelector.prototype.template = function() {
      return "<div class=\"items-container\"><ul class=\"items\"><li class=\"more loading\"></li></ul></div>";
    };

    EntitySelector.prototype.focus = function() {
      var _ref14,
        _this = this;
      ((_ref14 = this.searchInput) != null ? _ref14.focus() : void 0) || this.entity.done(function() {
        return _this.searchInput.focus();
      });
      return this;
    };

    EntitySelector.prototype.dispose = function() {
      var _ref14, _ref15;
      if ((_ref14 = this.searchInput) != null) {
        _ref14.remove();
      }
      if ((_ref15 = this.innerForm) != null) {
        _ref15.remove();
      }
      return this;
    };

    EntitySelector.prototype.remove = function() {
      this.dispose();
      return EntitySelector.__super__.remove.apply(this, arguments);
    };

    return EntitySelector;

  })(BaseInput);

  FileList = (function(_super) {
    __extends(FileList, _super);

    function FileList() {
      _ref14 = FileList.__super__.constructor.apply(this, arguments);
      return _ref14;
    }

    FileList.prototype.className = "file-list";

    FileList.prototype.events = {
      "change [type=file]": "appendFiles",
      "click .action-delete": "deleteFile"
    };

    FileList.prototype.initialize = function(options) {
      var _ref15, _ref16, _ref17;
      this.multiple = (_ref15 = options.multiple) != null ? _ref15 : false;
      this.formatter = (_ref16 = options.formatter) != null ? _ref16 : {
        format: function(value) {
          if (value instanceof File) {
            return value.name;
          } else {
            return value;
          }
        }
      };
      this.accepts = (_ref17 = options.accepts) != null ? _ref17 : "";
      return FileList.__super__.initialize.apply(this, arguments);
    };

    FileList.prototype.deleteFile = function(e) {
      var value;
      if (this.multiple) {
        value = _.clone(this.model.get(this.key));
        value.splice($(e.currentTarget).data("index"), 1);
      } else {
        value = '';
      }
      this.model.set(this.key, value);
      return false;
    };

    FileList.prototype.appendFiles = function(e) {
      var file, value, _i, _len, _ref15;
      if (e.target.files.length === 0) {
        return;
      }
      if (this.multiple) {
        value = _.clone(this.model.get(this.key));
        _ref15 = e.target.files;
        for (_i = 0, _len = _ref15.length; _i < _len; _i++) {
          file = _ref15[_i];
          value.push(file);
        }
      } else {
        value = e.target.files[0];
      }
      this.model.set(this.key, value);
      return this;
    };

    FileList.prototype.applyChanges = function() {
      return this.render();
    };

    FileList.prototype.render = function() {
      var html, i, item, value, _i, _len;
      value = this.model.get(this.key);
      html = "";
      if (this.multiple) {
        for (i = _i = 0, _len = value.length; _i < _len; i = ++_i) {
          item = value[i];
          html += this.renderItem(item, i);
        }
      } else {
        if (value) {
          html += this.renderItem(value);
        }
      }
      if (html) {
        html = this.wrapItems(html);
      }
      html += this.renderInput(this.multiple ? "<span class='glyphicon glyphicon-plus'></span> Добавить" : "Выбрать");
      this.$el.html(html);
      return this;
    };

    FileList.prototype.wrapItems = function(html) {
      return "<ul class=\"list-group\">" + html + "</ul>";
    };

    FileList.prototype.renderInput = function(label) {
      return "<div class=\"btn btn-sm btn-default file-list-input-wrap\">\n    <input type=\"file\" accept=\"" + this.accepts + " \"" + (this.multiple ? "multiple" : void 0) + ">\n    " + label + "\n</div>";
    };

    FileList.prototype.renderItem = function(item, i) {
      var label;
      if (i == null) {
        i = 0;
      }
      label = this.formatter.format(item);
      return "<li class=\"list-group-item\">\n    <a href=\"#\" class=\"action-delete pull-right\" data-index=\"" + i + "\"><span class=\"glyphicon glyphicon-remove\"></span></a>\n\n    " + label + "\n</li>";
    };

    return FileList;

  })(BaseInput);

  ImageList = (function(_super) {
    __extends(ImageList, _super);

    ImageList.prototype.className = "image-list";

    function ImageList() {
      this.readers = [];
      ImageList.__super__.constructor.apply(this, arguments);
    }

    ImageList.prototype.initialize = function(options) {
      var _ref15, _ref16;
      this.width = (_ref15 = options.width) != null ? _ref15 : 0;
      this.height = (_ref16 = options.height) != null ? _ref16 : 80;
      return ImageList.__super__.initialize.apply(this, arguments);
    };

    ImageList.prototype.render = function() {
      var reader, _i, _len, _ref15;
      ImageList.__super__.render.apply(this, arguments);
      _ref15 = this.readers;
      for (_i = 0, _len = _ref15.length; _i < _len; _i++) {
        reader = _ref15[_i];
        reader.readAsDataURL(reader.item);
      }
      this.readers = [];
      this.$(".fancybox").fancybox();
      return this;
    };

    ImageList.prototype.wrapItems = function(html) {
      return "<ul class=\"image-group\">" + html + "</ul>";
    };

    ImageList.prototype.renderItem = function(item, i) {
      if (i == null) {
        i = 0;
      }
      return "<li class=\"image-group-item\">\n    " + (this.renderImage(item, i)) + "\n    <a href=\"#\" class=\"action-delete\" data-index=\"" + i + "\"><span class=\"glyphicon glyphicon-remove\"></span></a>\n</li>";
    };

    ImageList.prototype.renderImage = function(item, i) {
      var id, image;
      if (i == null) {
        i = 0;
      }
      id = this.key + i;
      if (item instanceof File) {
        image = item.data || "";
        if (item.data == null) {
          this.readers.push(this.createPreviewLoader(item, id));
        }
      } else {
        image = thumb(item, this.width, this.height);
      }
      return "<a href=\"" + (item instanceof File ? item.data || "#" : item) + "\" class=\"fancybox\">\n    <img src=\"" + image + "\" id=\"" + id + "\">\n</a>";
    };

    ImageList.prototype.createPreviewLoader = function(item, id) {
      var reader;
      reader = new FileReader;
      reader.item = item;
      reader.onload = function(e) {
        e.target.item.data = e.target.result;
        return $("#" + id).attr("src", e.target.result).parent().attr("href", e.target.result);
      };
      return reader;
    };

    return ImageList;

  })(FileList);

  SearchInput = (function(_super) {
    __extends(SearchInput, _super);

    function SearchInput() {
      _ref15 = SearchInput.__super__.constructor.apply(this, arguments);
      return _ref15;
    }

    SearchInput.prototype.attributes = {
      type: "search",
      placeholder: "поиск"
    };

    SearchInput.prototype.scheduleChange = function() {
      var _this = this;
      if (this.timeout != null) {
        clearTimeout(this.timeout);
      }
      this.timeout = setTimeout((function() {
        return _this.change();
      }), 300);
      return this;
    };

    SearchInput.prototype.keydown = function(e) {
      if (e.keyCode === 8) {
        this.model.set(this.key, "");
        return false;
      }
      this.scheduleChange();
      return SearchInput.__super__.keydown.apply(this, arguments);
    };

    return SearchInput;

  })(TextInput);

  SlugInput = (function(_super) {
    __extends(SlugInput, _super);

    SlugInput.prototype.events = {
      "click .btn": "toggleSyncing"
    };

    function SlugInput(options) {
      var _ref16;
      this.input = new TextInput(_.clone(options));
      if (options.className == null) {
        options.className = "input-group";
      }
      options.className += " input-group-" + ((_ref16 = options.size) != null ? _ref16 : "sm");
      if (options.attributes != null) {
        delete options.attributes;
      }
      SlugInput.__super__.constructor.apply(this, arguments);
    }

    SlugInput.prototype.initialize = function(options) {
      var chars, _ref16, _ref17;
      chars = (_ref16 = options.chars) != null ? _ref16 : "a-z0-9\-_";
      this.regexp = new RegExp("[^" + chars + "]+", "g");
      this.separator = (_ref17 = options.separator) != null ? _ref17 : "-";
      this.key = options.key;
      if (options.ref != null) {
        this.ref = options.ref;
      }
      return SlugInput.__super__.initialize.apply(this, arguments);
    };

    SlugInput.prototype.toggleSyncing = function() {
      if (this.syncButton.hasClass("active")) {
        this.unlink();
      } else {
        this.link();
      }
      return this;
    };

    SlugInput.prototype.link = function() {
      if (!this.ref) {
        return;
      }
      this.listenTo(this.model, "change:" + this.ref, this.sync);
      this.syncButton.addClass("active");
      this.input.disable();
      return this.sync();
    };

    SlugInput.prototype.unlink = function() {
      if (this.ref != null) {
        this.stopListening(this.model, null, this.sync);
      }
      this.syncButton.removeClass("active");
      this.input.enable();
      return this;
    };

    SlugInput.prototype.linkable = function() {
      var refValue;
      refValue = this.convert(this.model.get(this.ref));
      return refValue === this.model.get(this.key);
    };

    SlugInput.prototype.convert = function(value) {
      if (value) {
        return value.toLocaleLowerCase().replace(/\s+/g, this.separator).replace(this.regexp, "");
      } else {
        return value;
      }
    };

    SlugInput.prototype.change = function() {
      this.unlink();
      this.$el.val(this.convert(this.$el.val()));
      return SlugInput.__super__.change.apply(this, arguments);
    };

    SlugInput.prototype.sync = function() {
      this.model.set(this.key, this.convert(this.model.get(this.ref)));
      return this;
    };

    SlugInput.prototype.render = function() {
      this.$el.html(this.template());
      this.$el.prepend(this.input.render().el);
      if (this.ref != null) {
        this.syncButton = this.$(".btn");
        if (this.linkable()) {
          this.link();
        }
      }
      return this;
    };

    SlugInput.prototype.template = function() {
      if (this.ref == null) {
        return "";
      }
      return "<div class=\"input-group-btn\">\n    <button type=\"button\" tabindex=\"-1\" class=\"btn btn-default\" title=\"Связать с полем " + (this.model.entity.fields.get(this.ref).get("label")) + "\"><span class=\"glyphicon glyphicon-link\"></span></button>\n</div>";
    };

    return SlugInput;

  })(Backbone.View);

  SelectInput = (function(_super) {
    __extends(SelectInput, _super);

    function SelectInput() {
      _ref16 = SelectInput.__super__.constructor.apply(this, arguments);
      return _ref16;
    }

    SelectInput.prototype.tagName = "select";

    SelectInput.prototype.initialize = function(options) {
      var _ref17, _ref18;
      this.items = (_ref17 = options.items) != null ? _ref17 : {};
      this.prompt = (_ref18 = options.prompt) != null ? _ref18 : null;
      return SelectInput.__super__.initialize.apply(this, arguments);
    };

    SelectInput.prototype.applyChanges = function(model, data) {
      this.$("[value='" + data + "']").prop("selected", true);
      return this;
    };

    SelectInput.prototype.render = function() {
      this.$el.html(this.template());
      return SelectInput.__super__.render.apply(this, arguments);
    };

    SelectInput.prototype.template = function() {
      var html, key, value, _ref17, _ref18;
      html = "";
      html += this.optionTemplate("", (_ref17 = this.prompt) != null ? _ref17 : "");
      _ref18 = this.items;
      for (key in _ref18) {
        value = _ref18[key];
        html += this.optionTemplate(key, value);
      }
      return html;
    };

    SelectInput.prototype.optionTemplate = function(value, title) {
      return "<option value=\"" + (_.escape(value)) + "\">" + (_.escape(title)) + "</option>";
    };

    return SelectInput;

  })(TextInput);

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
      this.dispose();
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
      return this.field.get("visible") && (this.field.get("editable") && this.field.get("updateable") || !this.model.isNew());
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

    FieldView.prototype.dispose = function() {
      var _ref17;
      if ((_ref17 = this.input) != null) {
        _ref17.remove();
      }
      return this;
    };

    FieldView.prototype.stopListening = function() {
      this.dispose();
      return FieldView.__super__.stopListening.apply(this, arguments);
    };

    return FieldView;

  })(Backbone.View);

  Field = (function(_super) {
    __extends(Field, _super);

    function Field() {
      _ref17 = Field.__super__.constructor.apply(this, arguments);
      return _ref17;
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
      return this.get("editable") && (this.get("updateable") || !model.isNew()) && model.isSaveable();
    };

    return Field;

  })(Attribute);

  Cruddy.fields.Input = (function(_super) {
    __extends(Input, _super);

    function Input() {
      _ref18 = Input.__super__.constructor.apply(this, arguments);
      return _ref18;
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

  /*
  class Cruddy.fields.DateTimeView extends Cruddy.fields.InputView
      format: (value) -> moment.unix(value).format @field.get "format"
      unformat: (value) -> moment(value, @field.get "format").unix()
  */


  Cruddy.fields.DateTime = (function(_super) {
    __extends(DateTime, _super);

    function DateTime() {
      _ref19 = DateTime.__super__.constructor.apply(this, arguments);
      return _ref19;
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

  Cruddy.fields.Boolean = (function(_super) {
    __extends(Boolean, _super);

    function Boolean() {
      _ref20 = Boolean.__super__.constructor.apply(this, arguments);
      return _ref20;
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

  Cruddy.fields.Relation = (function(_super) {
    __extends(Relation, _super);

    function Relation() {
      _ref21 = Relation.__super__.constructor.apply(this, arguments);
      return _ref21;
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
      return new EntityDropdown({
        model: model,
        key: this.id,
        reference: this.get("reference"),
        allowEdit: false
      });
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

  Cruddy.fields.File = (function(_super) {
    __extends(File, _super);

    function File() {
      _ref22 = File.__super__.constructor.apply(this, arguments);
      return _ref22;
    }

    File.prototype.createEditableInput = function(model) {
      return new FileList({
        model: model,
        key: this.id,
        multiple: this.get("multiple"),
        accepts: this.get("accepts")
      });
    };

    File.prototype.format = function(value) {
      if (value instanceof File) {
        return value.name;
      } else {
        return value;
      }
    };

    return File;

  })(Field);

  Cruddy.fields.Image = (function(_super) {
    __extends(Image, _super);

    function Image() {
      _ref23 = Image.__super__.constructor.apply(this, arguments);
      return _ref23;
    }

    Image.prototype.createEditableInput = function(model) {
      return new ImageList({
        model: model,
        key: this.id,
        width: this.get("width"),
        height: this.get("height"),
        multiple: this.get("multiple"),
        accepts: this.get("accepts")
      });
    };

    Image.prototype.format = function(value) {
      if (value instanceof File) {
        return value.name;
      } else {
        return value;
      }
    };

    return Image;

  })(Cruddy.fields.File);

  Cruddy.fields.Slug = (function(_super) {
    __extends(Slug, _super);

    function Slug() {
      _ref24 = Slug.__super__.constructor.apply(this, arguments);
      return _ref24;
    }

    Slug.prototype.createEditableInput = function(model) {
      return new SlugInput({
        model: model,
        key: this.id,
        chars: this.get("chars"),
        ref: this.get("ref"),
        separator: this.get("separator"),
        attributes: {
          placeholder: this.get("label")
        }
      });
    };

    Slug.prototype.createFilterInput = function(model, column) {
      return new TextInput({
        model: model,
        key: this.id,
        attributes: {
          placeholder: this.get("label")
        }
      });
    };

    return Slug;

  })(Field);

  Cruddy.fields.Enum = (function(_super) {
    __extends(Enum, _super);

    function Enum() {
      _ref25 = Enum.__super__.constructor.apply(this, arguments);
      return _ref25;
    }

    Enum.prototype.createEditableInput = function(model) {
      return new SelectInput({
        model: model,
        key: this.id,
        prompt: this.get("prompt"),
        items: this.get("items")
      });
    };

    Enum.prototype.createFilterInput = function(model) {
      return new SelectInput({
        model: model,
        key: this.id,
        prompt: "Любое значение",
        items: this.get("items")
      });
    };

    Enum.prototype.format = function(value) {
      var items;
      items = this.get("items");
      if (value in items) {
        return items[value];
      } else {
        return "n/a";
      }
    };

    return Enum;

  })(Field);

  Cruddy.columns = new Factory;

  Column = (function(_super) {
    __extends(Column, _super);

    function Column() {
      _ref26 = Column.__super__.constructor.apply(this, arguments);
      return _ref26;
    }

    Column.prototype.initialize = function(options) {
      if (options.formatter != null) {
        this.formatter = Cruddy.formatters.create(options.formatter, options.formatterOptions);
      }
      return Column.__super__.initialize.apply(this, arguments);
    };

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
      if (this.formatter != null) {
        return this.formatter.format(value);
      } else {
        return value;
      }
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
      _ref27 = Field.__super__.constructor.apply(this, arguments);
      return _ref27;
    }

    Field.prototype.initialize = function(attributes) {
      var field, _ref28;
      field = (_ref28 = attributes.field) != null ? _ref28 : attributes.id;
      this.field = attributes.entity.fields.get(field);
      if (attributes.title === null) {
        this.set("title", this.field.get("label"));
      }
      return Field.__super__.initialize.apply(this, arguments);
    };

    Field.prototype.renderCell = function(value) {
      if (this.formatter != null) {
        return this.formatter.format(value);
      } else {
        return this.field.format(value);
      }
    };

    Field.prototype.createFilterInput = function(model) {
      return this.field.createFilterInput(model, this);
    };

    Field.prototype.getClass = function() {
      return Field.__super__.getClass.apply(this, arguments) + " col-" + this.field.get("type");
    };

    return Field;

  })(Column);

  Cruddy.columns.Computed = (function(_super) {
    __extends(Computed, _super);

    function Computed() {
      _ref28 = Computed.__super__.constructor.apply(this, arguments);
      return _ref28;
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

  Cruddy.formatters = new Factory;

  BaseFormatter = (function() {
    BaseFormatter.prototype.defaultOptions = {};

    function BaseFormatter(options) {
      if (options == null) {
        options = {};
      }
      this.options = $.extend({}, this.defaultOptions, options);
      this;
    }

    BaseFormatter.prototype.format = function(value) {
      return value;
    };

    return BaseFormatter;

  })();

  Cruddy.formatters.Image = (function(_super) {
    __extends(Image, _super);

    function Image() {
      _ref29 = Image.__super__.constructor.apply(this, arguments);
      return _ref29;
    }

    Image.prototype.defaultOptions = {
      width: 40,
      height: 40
    };

    Image.prototype.format = function(value) {
      if (_.isEmpty(value)) {
        return "";
      }
      if (_.isArray(value)) {
        value = value[0];
      }
      return "<img src=\"" + (thumb(value, this.options.width, this.options.height)) + "\" width=\"" + (this.options.width || this.defaultOptions.width) + "\" height=\"" + (this.options.height || this.defaultOptions.height) + "\" alt=\"" + (_.escape(value)) + "\">";
    };

    return Image;

  })(BaseFormatter);

  Cruddy.formatters.Plain = (function(_super) {
    __extends(Plain, _super);

    function Plain() {
      _ref30 = Plain.__super__.constructor.apply(this, arguments);
      return _ref30;
    }

    Plain.prototype.format = function(value) {
      return value;
    };

    return Plain;

  })(BaseFormatter);

  Cruddy.related = new Factory;

  Related = (function(_super) {
    __extends(Related, _super);

    function Related() {
      _ref31 = Related.__super__.constructor.apply(this, arguments);
      return _ref31;
    }

    Related.prototype.resolve = function() {
      var _this = this;
      if (this.resolver != null) {
        return this.resolver;
      }
      this.resolver = Cruddy.app.entity(this.get("related"));
      return this.resolver.done(function(entity) {
        return _this.related = entity;
      });
    };

    return Related;

  })(Backbone.Model);

  Cruddy.related.One = (function(_super) {
    __extends(One, _super);

    function One() {
      _ref32 = One.__super__.constructor.apply(this, arguments);
      return _ref32;
    }

    One.prototype.associate = function(parent, child) {
      child.set(this.get("foreign_key"), parent.id);
      return this;
    };

    return One;

  })(Related);

  Cruddy.related.MorphOne = (function(_super) {
    __extends(MorphOne, _super);

    function MorphOne() {
      _ref33 = MorphOne.__super__.constructor.apply(this, arguments);
      return _ref33;
    }

    MorphOne.prototype.associate = function(parent, child) {
      child.set(this.get("morph_type"), this.get("morph_class"));
      return MorphOne.__super__.associate.apply(this, arguments);
    };

    return MorphOne;

  })(Cruddy.related.One);

  Entity = (function(_super) {
    __extends(Entity, _super);

    function Entity() {
      _ref34 = Entity.__super__.constructor.apply(this, arguments);
      return _ref34;
    }

    Entity.prototype.initialize = function(attributes, options) {
      this.fields = this.createCollection(Cruddy.fields, attributes.fields);
      this.columns = this.createCollection(Cruddy.columns, attributes.columns);
      this.related = this.createCollection(Cruddy.related, attributes.related);
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
        var _i, _len, _ref35, _results;
        _ref35 = columns.models;
        _results = [];
        for (_i = 0, _len = _ref35.length; _i < _len; _i++) {
          col = _ref35[_i];
          if (col.get("filterable")) {
            _results.push(col.createFilter());
          }
        }
        return _results;
      })();
      return new Backbone.Collection(filters);
    };

    Entity.prototype.createInstance = function(attributes, relatedData) {
      var item, related, _i, _len, _ref35;
      if (attributes == null) {
        attributes = {};
      }
      if (relatedData == null) {
        relatedData = {};
      }
      related = {};
      _ref35 = this.related.models;
      for (_i = 0, _len = _ref35.length; _i < _len; _i++) {
        item = _ref35[_i];
        related[item.id] = item.related.createInstance(relatedData[item.id]);
      }
      return new EntityInstance(_.extend({}, this.get("defaults"), attributes), {
        entity: this,
        related: related
      });
    };

    Entity.prototype.search = function() {
      if (this.searchDataSource != null) {
        return this.searchDataSource.reset();
      }
      this.searchDataSource = new SearchDataSource({}, {
        url: this.url("search"),
        primaryColumn: this.get("primary_column")
      });
      return this.searchDataSource.next();
    };

    Entity.prototype.load = function(id) {
      var xhr,
        _this = this;
      xhr = $.ajax({
        url: this.url(id),
        type: "GET",
        dataType: "json",
        cache: true,
        displayLoading: true
      });
      return xhr.then(function(resp) {
        resp = resp.data;
        return _this.createInstance(resp.model, resp.related);
      });
    };

    Entity.prototype.update = function(id) {
      var _this = this;
      return this.load(id).then(function(instance) {
        _this.set("instance", instance);
        return instance;
      });
    };

    Entity.prototype.getCopyableAttributes = function(attributes) {
      var data, field, _i, _len, _ref35;
      data = {};
      _ref35 = this.fields.models;
      for (_i = 0, _len = _ref35.length; _i < _len; _i++) {
        field = _ref35[_i];
        if (field.get("copyable") && field.id in attributes) {
          data[field.id] = attributes[field.id];
        }
      }
      return data;
    };

    Entity.prototype.url = function(id) {
      return entity_url(this.id, id);
    };

    Entity.prototype.link = function(id) {
      return ("" + this.id) + (id != null ? "/" + id : "");
    };

    return Entity;

  })(Backbone.Model);

  EntityInstance = (function(_super) {
    __extends(EntityInstance, _super);

    function EntityInstance() {
      _ref35 = EntityInstance.__super__.constructor.apply(this, arguments);
      return _ref35;
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
      return this.entity.link(this.isNew() ? "create" : this.id);
    };

    EntityInstance.prototype.url = function() {
      return this.entity.url(this.id);
    };

    EntityInstance.prototype.sync = function(method, model, options) {
      var _ref36;
      if (method === "update" || method === "create") {
        options.data = new AdvFormData((_ref36 = options.attrs) != null ? _ref36 : this.attributes).original;
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
        var key, model, save, _ref36;
        save = [];
        if (xhr != null) {
          save.push(xhr);
        }
        _ref36 = _this.related;
        for (key in _ref36) {
          model = _ref36[key];
          if (model.isNew()) {
            _this.entity.related.get(key).associate(_this, model);
          }
          if (model.hasChangedSinceSync()) {
            save.push(model.save());
          }
        }
        return $.when.apply($, save);
      };
      if (this.isNew()) {
        return xhr.then(function(resp) {
          return queue();
        });
      } else {
        return queue(xhr);
      }
    };

    EntityInstance.prototype.parse = function(resp) {
      return resp.data.instance;
    };

    EntityInstance.prototype.copy = function() {
      var copy, item, key, _ref36;
      copy = this.entity.createInstance();
      copy.set(this.getCopyableAttributes(), {
        silent: true
      });
      _ref36 = this.related;
      for (key in _ref36) {
        item = _ref36[key];
        copy.related[key].set(item.getCopyableAttributes(), {
          silent: true
        });
      }
      return copy;
    };

    EntityInstance.prototype.getCopyableAttributes = function() {
      return this.entity.getCopyableAttributes(this.attributes);
    };

    EntityInstance.prototype.hasChangedSinceSync = function() {
      var key, related, value, _ref36, _ref37;
      _ref36 = this.attributes;
      for (key in _ref36) {
        value = _ref36[key];
        if (!_.isEqual(value, this.original[key])) {
          return true;
        }
      }
      if (!this.isNew()) {
        _ref37 = this.related;
        for (key in _ref37) {
          related = _ref37[key];
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
      var _this = this;
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
        after_break(function() {
          return _this.form.show();
        });
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
      this.header = this.$(".entity-page-header");
      this.content = this.$(".entity-page-content");
      this.footer = this.$(".entity-page-footer");
      this.dataSource = this.model.createDataSource();
      this.dataGrid = new DataGrid({
        model: this.dataSource
      });
      this.pagination = new Pagination({
        model: this.dataSource
      });
      this.filterList = new FilterList({
        model: this.dataSource.filter,
        entity: this.dataSource.entity
      });
      this.search = new SearchInput({
        model: this.dataSource,
        key: "search"
      });
      this.dataSource.fetch();
      this.$(".col-search").append(this.search.render().el);
      this.$(".col-filters").append(this.filterList.render().el);
      this.content.append(this.dataGrid.render().el);
      this.footer.append(this.pagination.render().el);
      return this;
    };

    EntityPage.prototype.template = function() {
      var html;
      html = "<div class='entity-page-header'>";
      html += "<h1>\n    " + (this.model.get("title")) + "\n";
      if (this.model.get("can_create")) {
        html += "<button class=\"btn btn-default btn-create\" type=\"button\">\n    <span class=\"glyphicon glyphicon-plus\"</span>\n</button>";
      }
      html += "</h1>";
      html += "<div class=\"row row-search\"><div class=\"col-xs-2 col-search\"></div><div class=\"col-xs-10 col-filters\"></div></div>";
      html += "</div>";
      html += "<div class='entity-page-content-wrap'><div class='entity-page-content'></div></div>";
      return html += "<div class='entity-page-footer'></div>";
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
      if (this.pagination != null) {
        this.pagination.remove();
      }
      if (this.search != null) {
        this.search.remove();
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
      "click .btn-destroy": "destroy",
      "click .btn-copy": "copy"
    };

    function EntityForm(options) {
      this.className += " " + this.className + "-" + options.model.entity.id;
      EntityForm.__super__.constructor.apply(this, arguments);
    }

    EntityForm.prototype.initialize = function(options) {
      var key, related, _ref36, _ref37;
      this.inner = (_ref36 = options.inner) != null ? _ref36 : false;
      this.listenTo(this.model, "destroy", this.handleDestroy);
      this.signOn(this.model);
      _ref37 = this.model.related;
      for (key in _ref37) {
        related = _ref37[key];
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
      if (e.ctrlKey && e.keyCode === 90 && e.target === document.body) {
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
      if (!this.request) {
        this.submit.attr("disabled", this.model.hasChangedSinceSync() === false);
      }
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

    EntityForm.prototype.displaySuccess = function() {
      return this.displayAlert("Получилось!", "success");
    };

    EntityForm.prototype.displayInvalid = function() {
      return this.displayAlert("Не получилось...", "warning");
    };

    EntityForm.prototype.displayError = function(xhr) {
      var _ref36;
      if (((_ref36 = xhr.responseJSON) != null ? _ref36.error : void 0) !== "VALIDATION") {
        return this.displayAlert("Ошибка", "danger");
      }
    };

    EntityForm.prototype.handleDestroy = function() {
      if (this.model.entity.get("soft_deleting")) {
        this.update();
      } else {
        if (this.inner) {
          this.remove();
        } else {
          Cruddy.router.navigate(this.model.entity.link(), {
            trigger: true
          });
        }
      }
      return this;
    };

    EntityForm.prototype.show = function() {
      this.$el.toggleClass("opened", true);
      this.tabs[0].focus();
      return this;
    };

    EntityForm.prototype.save = function() {
      var _this = this;
      if ((this.request != null) || !this.model.hasChangedSinceSync()) {
        return;
      }
      this.request = this.model.save({
        displayLoading: true
      }).done($.proxy(this, "displaySuccess")).fail($.proxy(this, "displayError"));
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
        if (this.inner) {
          this.remove();
        } else {
          Cruddy.router.navigate(this.model.entity.link(), {
            trigger: true
          });
        }
      }
      return this;
    };

    EntityForm.prototype.destroy = function() {
      var confirmed, softDeleting,
        _this = this;
      if (this.request || this.model.isNew()) {
        return;
      }
      softDeleting = this.model.entity.get("soft_deleting");
      confirmed = !softDeleting ? confirm("Точно удалить? Восстановить не получится!") : true;
      if (confirmed) {
        this.request = this.softDeleting && this.model.get("deleted_at") ? this.model.restore : this.model.destroy({
          wait: true
        });
        this.request.always(function() {
          return _this.request = null;
        });
      }
      return this;
    };

    EntityForm.prototype.copy = function() {
      var copy;
      this.model.entity.set("instance", copy = this.model.copy());
      Cruddy.router.navigate(copy.link());
      return this;
    };

    EntityForm.prototype.render = function() {
      var key, related, _ref36;
      this.dispose();
      this.$el.html(this.template());
      this.nav = this.$(".nav");
      this.footer = this.$("footer");
      this.submit = this.$(".btn-save");
      this.destroy = this.$(".btn-destroy");
      this.copy = this.$(".btn-copy");
      this.tabs = [];
      this.renderTab(this.model, true);
      _ref36 = this.model.related;
      for (key in _ref36) {
        related = _ref36[key];
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
      this.submit.attr("disabled", (this.request != null) || !this.model.hasChangedSinceSync());
      this.submit.toggle(this.model.entity.get(this.model.isNew() ? "can_create" : "can_update"));
      this.destroy.attr("disabled", this.request != null);
      this.destroy.html(this.model.entity.get("soft_deleting" && this.model.get("deleted_at")) ? "Восстановить" : "<span class='glyphicon glyphicon-trash' title='Удалить'></span>");
      this.destroy.toggle(!this.model.isNew() && this.model.entity.get("can_delete"));
      this.copy.toggle(!this.model.isNew() && this.model.entity.get("can_create"));
      return this;
    };

    EntityForm.prototype.template = function() {
      return "<header>\n    <div class=\"btn-group btn-group-sm\">\n        <button type=\"button\" tabindex=\"-1\" class=\"btn btn-link btn-copy\" title=\"Копировать\">\n            <span class=\"glyphicon glyphicon-book\"></span>\n        </button>\n    </div>\n    <ul class=\"nav nav-pills\"></ul>\n</header>\n\n<footer>\n    <button type=\"button\" class=\"btn btn-default btn-close btn-sm\" type=\"button\">Закрыть</button>\n    <button type=\"button\" class=\"btn btn-default btn-destroy btn-sm\" type=\"button\"></button>\n    <button type=\"button\" class=\"btn btn-primary btn-save btn-sm\" type=\"button\" disabled></button>\n</footer>";
    };

    EntityForm.prototype.navTemplate = function(label, target, active) {
      active = active ? " class=\"active\"" : "";
      return "<li" + active + "><a href=\"#" + target + "\" data-toggle=\"tab\">" + label + "</a></li>";
    };

    EntityForm.prototype.remove = function() {
      var _this = this;
      this.trigger("remove", this);
      this.$el.one(TRANSITIONEND, function() {
        _this.dispose();
        $(document).off("." + _this.cid);
        _this.trigger("removed", _this);
        return EntityForm.__super__.remove.apply(_this, arguments);
      }).removeClass("opened");
      return this;
    };

    EntityForm.prototype.dispose = function() {
      var fieldList, _i, _len, _ref36;
      if (this.tabs != null) {
        _ref36 = this.tabs;
        for (_i = 0, _len = _ref36.length; _i < _len; _i++) {
          fieldList = _ref36[_i];
          fieldList.remove();
        }
      }
      return this;
    };

    return EntityForm;

  })(Backbone.View);

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
      _ref36 = App.__super__.constructor.apply(this, arguments);
      return _ref36;
    }

    App.prototype.entities = {};

    App.prototype.initialize = function() {
      this.container = $("body");
      this.loadingRequests = 0;
      return this.on("change:entity", this.displayEntity, this);
    };

    App.prototype.displayEntity = function(model, entity) {
      this.dispose();
      if (entity) {
        return this.container.append((this.page = new EntityPage({
          model: entity
        })).render().el);
      }
    };

    App.prototype.displayError = function(xhr) {
      var error;
      error = (xhr == null) || xhr.status === 403 ? "Ошибка доступа" : "Ошибка";
      this.dispose();
      this.container.html("<p class='alert alert-danger'>" + error + "</p>");
      return this;
    };

    App.prototype.startLoading = function() {
      var _this = this;
      if (this.loadingRequests++ === 0) {
        this.loading = setTimeout((function() {
          $(document.body).addClass("loading");
          return _this.loading = false;
        }), 1000);
      }
      return this;
    };

    App.prototype.doneLoading = function() {
      if (this.loadingRequests === 0) {
        console.error("Seems like doneLoading is called too many times.");
        return;
      }
      if (--this.loadingRequests === 0) {
        if (this.loading) {
          clearTimeout(this.loading);
          this.loading = false;
        } else {
          $(document.body).removeClass("loading");
        }
      }
      return this;
    };

    App.prototype.entity = function(id, options) {
      var _this = this;
      if (options == null) {
        options = {};
      }
      if (id in this.entities) {
        return this.entities[id];
      }
      options = $.extend({}, {
        url: entity_url(id, "schema"),
        type: "get",
        dataType: "json",
        displayLoading: true
      }, options);
      return this.entities[id] = $.ajax(options).then(function(resp) {
        var entity, related, wait;
        entity = new Entity(resp.data);
        if (_.isEmpty(entity.related.models)) {
          return entity;
        }
        wait = (function() {
          var _i, _len, _ref37, _results;
          _ref37 = entity.related.models;
          _results = [];
          for (_i = 0, _len = _ref37.length; _i < _len; _i++) {
            related = _ref37[_i];
            _results.push(related.resolve());
          }
          return _results;
        })();
        return $.when.apply($, wait).then(function() {
          return entity;
        });
      });
    };

    App.prototype.dispose = function() {
      var _ref37;
      if ((_ref37 = this.page) != null) {
        _ref37.remove();
      }
      return this;
    };

    return App;

  })(Backbone.Model);

  Cruddy.app = new App;

  Router = (function(_super) {
    __extends(Router, _super);

    function Router() {
      _ref37 = Router.__super__.constructor.apply(this, arguments);
      return _ref37;
    }

    Router.prototype.routes = {
      ":page": "page",
      ":page/create": "create",
      ":page/:id": "update"
    };

    Router.prototype.loading = function(promise) {
      Cruddy.app.startLoading();
      return promise.always(function() {
        return Cruddy.app.doneLoading();
      });
    };

    Router.prototype.entity = function(id) {
      var promise;
      promise = Cruddy.app.entity(id).done(function(entity) {
        entity.set("instance", null);
        return Cruddy.app.set("entity", entity);
      });
      return promise.fail(function() {
        return Cruddy.app.displayError.apply(Cruddy.app, arguments).set("entity", false);
      });
    };

    Router.prototype.page = function(page) {
      return this.entity(page);
    };

    Router.prototype.create = function(page) {
      return this.entity(page).done(function(entity) {
        return entity.set("instance", entity.createInstance());
      });
    };

    Router.prototype.update = function(page, id) {
      return this.entity(page).then(function(entity) {
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