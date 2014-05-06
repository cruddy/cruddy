(function() {
  var API_URL, AdvFormData, Alert, App, Attribute, BaseFormatter, Cruddy, DataGrid, DataSource, Factory, FieldList, FilterList, Pagination, Router, SearchDataSource, TRANSITIONEND, after_break, b_btn, b_icon, entity_url, humanize, thumb, _ref,
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

  humanize = (function(_this) {
    return function(id) {
      return id.replace(/_-/, " ");
    };
  })(this);

  entity_url = function(id, extra) {
    var url;
    url = Cruddy.baseUrl + "/api/" + id;
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

  b_icon = function(icon) {
    return "<span class='glyphicon glyphicon-" + icon + "'></span>";
  };

  b_btn = function(label, icon, className, type) {
    if (icon == null) {
      icon = null;
    }
    if (className == null) {
      className = "btn-default";
    }
    if (type == null) {
      type = 'button';
    }
    if (icon) {
      label = b_icon(icon) + ' ' + label;
    }
    if (_.isArray(className)) {
      className = "btn-" + className.join(" btn-");
    }
    return "<button type='" + type + "' class='btn " + className + "'>" + (label.trim()) + "</button>";
  };

  Alert = (function(_super) {
    __extends(Alert, _super);

    function Alert() {
      return Alert.__super__.constructor.apply(this, arguments);
    }

    Alert.prototype.tagName = "span";

    Alert.prototype.className = "alert";

    Alert.prototype.initialize = function(options) {
      var _ref1;
      this.$el.addClass((_ref1 = this.className + "-" + options.type) != null ? _ref1 : "info");
      this.$el.text(options.message);
      if (options.timeout != null) {
        setTimeout(((function(_this) {
          return function() {
            return _this.remove();
          };
        })(this)), options.timeout);
      }
      return this;
    };

    Alert.prototype.render = function() {
      after_break((function(_this) {
        return function() {
          return _this.$el.addClass("show");
        };
      })(this));
      return this;
    };

    Alert.prototype.remove = function() {
      this.$el.one(TRANSITIONEND, (function(_this) {
        return function() {
          return Alert.__super__.remove.apply(_this, arguments);
        };
      })(this));
      this.$el.removeClass("show");
      return this;
    };

    return Alert;

  })(Backbone.View);

  Cruddy.View = (function(_super) {
    __extends(View, _super);

    function View() {
      return View.__super__.constructor.apply(this, arguments);
    }

    View.prototype.componentId = function(component) {
      return this.cid + "-" + component;
    };

    View.prototype.$component = function(component) {
      return this.$("#" + this.componentId(component));
    };

    return View;

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
        if (_.isFunction(value.serialize)) {
          this.append(name, value.serialize());
        } else {
          for (key in value) {
            _value = value[key];
            this.append(this.key(name, key), _value);
          }
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
      return Attribute.__super__.constructor.apply(this, arguments);
    }

    Attribute.prototype.initialize = function(options) {
      this.entity = options.entity;
      return this;
    };

    Attribute.prototype.getType = function() {
      return this.attributes.type;
    };

    Attribute.prototype.getHelp = function() {
      return this.attributes.help;
    };

    Attribute.prototype.canFilter = function() {
      return this.attributes.filter_type === "complex";
    };

    Attribute.prototype.isVisible = function() {
      return this.attributes.hide === false;
    };

    return Attribute;

  })(Backbone.Model);

  DataSource = (function(_super) {
    __extends(DataSource, _super);

    function DataSource() {
      return DataSource.__super__.constructor.apply(this, arguments);
    }

    DataSource.prototype.defaults = {
      data: [],
      search: ""
    };

    DataSource.prototype.initialize = function(attributes, options) {
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
        success: (function(_this) {
          return function(resp) {
            _this._hold = true;
            _this.set(resp.data);
            _this._hold = false;
            return _this.trigger("data", _this, resp.data.data);
          };
        })(this),
        error: (function(_this) {
          return function(xhr) {
            return _this.trigger("error", _this, xhr);
          };
        })(this)
      };
      if (this.filter != null) {
        this.listenTo(this.filter, "change", ((function(_this) {
          return function() {
            _this.set({
              current_page: 1,
              silent: true
            });
            return _this.fetch();
          };
        })(this)));
      }
      this.on("change", (function(_this) {
        return function() {
          if (!_this._hold) {
            return _this.fetch();
          }
        };
      })(this));
      return this.on("change:search", (function(_this) {
        return function() {
          return _this.set({
            current_page: 1,
            silent: true
          });
        };
      })(this));
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
      if (this.request != null) {
        this.request.abort();
      }
      this.options.data = this.data();
      this.request = $.ajax(this.options);
      this.request.always((function(_this) {
        return function() {
          return _this.request = null;
        };
      })(this));
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
        keywords: this.get("search")
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
      var data, key, value, _ref1;
      if (this.filter == null) {
        return null;
      }
      data = {};
      _ref1 = this.filter.attributes;
      for (key in _ref1) {
        value = _ref1[key];
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
      return SearchDataSource.__super__.constructor.apply(this, arguments);
    }

    SearchDataSource.prototype.defaults = {
      search: ""
    };

    SearchDataSource.prototype.initialize = function(attributes, options) {
      this.filters = new Backbone.Model;
      this.options = {
        url: options.url,
        type: "get",
        dataType: "json",
        data: {
          simple: 1
        },
        success: (function(_this) {
          return function(resp) {
            var item, _i, _len, _ref1;
            resp = resp.data;
            _ref1 = resp.data;
            for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
              item = _ref1[_i];
              _this.data.push(item);
            }
            _this.page = resp.current_page;
            _this.more = resp.current_page < resp.last_page;
            _this.request = null;
            _this.trigger("data", _this, _this.data);
            return _this;
          };
        })(this),
        error: (function(_this) {
          return function(xhr) {
            _this.request = null;
            _this.trigger("error", _this, xhr);
            return _this;
          };
        })(this)
      };
      if (options.ajaxOptions != null) {
        $.extend(true, this.options, options.ajaxOptions);
      }
      this.reset();
      this.on("change:search", this.refresh, this);
      this.listenTo(this.filters, "change", this.refresh);
      return this;
    };

    SearchDataSource.prototype.refresh = function() {
      return this.reset().next();
    };

    SearchDataSource.prototype.reset = function() {
      this.data = [];
      this.page = null;
      this.more = true;
      return this;
    };

    SearchDataSource.prototype.fetch = function(q, page, filters) {
      if (this.request != null) {
        this.request.abort();
      }
      $.extend(this.options.data, {
        page: page,
        keywords: q,
        filters: filters
      });
      this.trigger("request", this, this.request = $.ajax(this.options));
      return this.request;
    };

    SearchDataSource.prototype.next = function() {
      var page;
      if (this.more) {
        page = this.page != null ? this.page + 1 : 1;
        this.fetch(this.get("search"), page, this.filters.attributes);
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
      return Pagination.__super__.constructor.apply(this, arguments);
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
      html += this.renderLink(current - 1, "&larr; " + Cruddy.lang.prev, "previous" + (current > 1 ? "" : " disabled"));
      if (this.model.get("total") != null) {
        html += this.renderStats();
      }
      html += this.renderLink(current + 1, "" + Cruddy.lang.next + " &rarr;", "next" + (current < last ? "" : " disabled"));
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

    DataGrid.prototype.className = "table table-hover data-grid";

    DataGrid.prototype.events = {
      "click .sortable": "setOrder",
      "click .item": "navigate"
    };

    function DataGrid(options) {
      this.className += " data-grid-" + options.entity.id;
      DataGrid.__super__.constructor.apply(this, arguments);
    }

    DataGrid.prototype.initialize = function(options) {
      this.entity = options.entity;
      this.columns = this.entity.columns.models.filter(function(col) {
        return col.isVisible();
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
      var prev;
      prev = entity.previous("instance");
      if (prev != null) {
        this.$("#item-" + prev.id).removeClass("active");
        prev.off(null, null, this);
      }
      if (curr != null) {
        this.$("#item-" + curr.id).addClass("active");
        curr.on("sync destroy", ((function(_this) {
          return function() {
            return _this.model.fetch();
          };
        })(this)), this);
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
      return "<th class=\"" + (col.getClass()) + "\" id=\"col-" + col.id + "\">" + (this.renderHeadCellValue(col)) + "</th>";
    };

    DataGrid.prototype.renderHeadCellValue = function(col) {
      var help, title;
      title = _.escape(col.getHeader());
      help = _.escape(col.getHelp());
      if (col.canOrder()) {
        title = "<span class=\"sortable\" data-id=\"" + col.id + "\">" + title + "</span>";
      }
      if (help) {
        return "<span class=\"glyphicon glyphicon-question-sign\" title=\"" + help + "\"></span> " + title;
      } else {
        return title;
      }
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
        html += "<tr><td class=\"no-items\" colspan=\"" + columns.length + "\">" + Cruddy.lang.no_results + "</td></tr>";
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
      return "<td class=\"" + (col.getClass()) + "\">" + (col.format(item[col.id])) + "</td>";
    };

    return DataGrid;

  })(Backbone.View);

  FieldList = (function(_super) {
    __extends(FieldList, _super);

    function FieldList() {
      return FieldList.__super__.constructor.apply(this, arguments);
    }

    FieldList.prototype.className = "field-list";

    FieldList.prototype.focus = function() {
      var _ref1;
      if ((_ref1 = this.primary) != null) {
        _ref1.focus();
      }
      return this;
    };

    FieldList.prototype.render = function() {
      var field, _i, _len, _ref1;
      this.dispose();
      this.$el.empty();
      _ref1 = this.createFields();
      for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
        field = _ref1[_i];
        this.$el.append(field.el);
      }
      return this;
    };

    FieldList.prototype.createFields = function() {
      var field, view, _i, _len, _ref1;
      this.fields = (function() {
        var _i, _len, _ref1, _results;
        _ref1 = this.model.entity.fields.models;
        _results = [];
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          field = _ref1[_i];
          if (field.isVisible()) {
            _results.push(field.createView(this.model).render());
          }
        }
        return _results;
      }).call(this);
      _ref1 = this.fields;
      for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
        view = _ref1[_i];
        if (!(view.field.isEditable(this.model))) {
          continue;
        }
        this.primary = view;
        break;
      }
      return this.fields;
    };

    FieldList.prototype.dispose = function() {
      var field, _i, _len, _ref1;
      if (this.fields != null) {
        _ref1 = this.fields;
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          field = _ref1[_i];
          field.remove();
        }
      }
      this.fields = null;
      this.primary = null;
      return this;
    };

    FieldList.prototype.remove = function() {
      this.dispose();
      return FieldList.__super__.remove.apply(this, arguments);
    };

    return FieldList;

  })(Backbone.View);

  FilterList = (function(_super) {
    __extends(FilterList, _super);

    function FilterList() {
      return FilterList.__super__.constructor.apply(this, arguments);
    }

    FilterList.prototype.className = "filter-list";

    FilterList.prototype.tagName = "fieldset";

    FilterList.prototype.initialize = function(options) {
      this.entity = options.entity;
      this.availableFilters = options.filters;
      return this;
    };

    FilterList.prototype.render = function() {
      var field, filter, input, _i, _len, _ref1;
      this.dispose();
      this.$el.html(this.template());
      this.items = this.$(".filter-list-container");
      _ref1 = this.availableFilters;
      for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
        filter = _ref1[_i];
        if (!((field = this.entity.fields.get(filter)) && field.canFilter() && (input = field.createFilterInput(this.model)))) {
          continue;
        }
        this.filters.push(input);
        this.items.append(input.render().el);
        input.$el.wrap("<div class=\"form-group filter filter-" + field.id + "\"></div>").parent().before("<label>" + (field.getFilterLabel()) + "</label>");
      }
      return this;
    };

    FilterList.prototype.template = function() {
      return "<div class=\"filter-list-container\"></div>";
    };

    FilterList.prototype.dispose = function() {
      var filter, _i, _len, _ref1;
      if (this.filters != null) {
        _ref1 = this.filters;
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          filter = _ref1[_i];
          filter.remove();
        }
      }
      this.filters = [];
      return this;
    };

    FilterList.prototype.remove = function() {
      this.dispose();
      return FilterList.__super__.remove.apply(this, arguments);
    };

    return FilterList;

  })(Backbone.View);

  Cruddy.Inputs = {};

  Cruddy.Inputs.Base = (function(_super) {
    __extends(Base, _super);

    function Base(options) {
      this.key = options.key;
      Base.__super__.constructor.apply(this, arguments);
    }

    Base.prototype.initialize = function() {
      this.listenTo(this.model, "change:" + this.key, function(model, value, options) {
        return this.applyChanges(value, !options.input || options.input !== this);
      });
      return this;
    };

    Base.prototype.applyChanges = function(data, external) {
      return this;
    };

    Base.prototype.render = function() {
      return this.applyChanges(this.getValue(), true);
    };

    Base.prototype.focus = function() {
      return this;
    };

    Base.prototype.getValue = function() {
      return this.model.get(this.key);
    };

    Base.prototype.setValue = function(value, options) {
      if (options == null) {
        options = {};
      }
      options.input = this;
      this.model.set(this.key, value, options);
      return this;
    };

    return Base;

  })(Cruddy.View);

  Cruddy.Inputs.Static = (function(_super) {
    __extends(Static, _super);

    function Static() {
      return Static.__super__.constructor.apply(this, arguments);
    }

    Static.prototype.tagName = "p";

    Static.prototype.className = "form-control-static";

    Static.prototype.initialize = function(options) {
      if (options.formatter != null) {
        this.formatter = options.formatter;
      }
      return Static.__super__.initialize.apply(this, arguments);
    };

    Static.prototype.applyChanges = function(data) {
      return this.render();
    };

    Static.prototype.render = function() {
      var value;
      value = this.getValue();
      if (this.formatter != null) {
        value = this.formatter.format(value);
      }
      this.$el.html(value);
      return this;
    };

    return Static;

  })(Cruddy.Inputs.Base);

  Cruddy.Inputs.BaseText = (function(_super) {
    __extends(BaseText, _super);

    function BaseText() {
      return BaseText.__super__.constructor.apply(this, arguments);
    }

    BaseText.prototype.className = "form-control";

    BaseText.prototype.events = {
      "change": "change",
      "keydown": "keydown"
    };

    BaseText.prototype.keydown = function(e) {
      if (e.ctrlKey && e.keyCode === 13) {
        return this.change();
      }
      return this;
    };

    BaseText.prototype.disable = function() {
      this.$el.prop("disabled", true);
      return this;
    };

    BaseText.prototype.enable = function() {
      this.$el.prop("disabled", false);
      return this;
    };

    BaseText.prototype.change = function() {
      this.model.set(this.key, this.el.value);
      return this;
    };

    BaseText.prototype.applyChanges = function(data, external) {
      if (external) {
        this.$el.val(data);
      }
      return this;
    };

    BaseText.prototype.focus = function() {
      this.el.focus();
      return this;
    };

    return BaseText;

  })(Cruddy.Inputs.Base);

  Cruddy.Inputs.Text = (function(_super) {
    __extends(Text, _super);

    function Text() {
      return Text.__super__.constructor.apply(this, arguments);
    }

    Text.prototype.tagName = "input";

    Text.prototype.initialize = function(options) {
      options.mask && this.$el.mask(options.mask);
      return Text.__super__.initialize.apply(this, arguments);
    };

    return Text;

  })(Cruddy.Inputs.BaseText);

  Cruddy.Inputs.Textarea = (function(_super) {
    __extends(Textarea, _super);

    function Textarea() {
      return Textarea.__super__.constructor.apply(this, arguments);
    }

    Textarea.prototype.tagName = "textarea";

    return Textarea;

  })(Cruddy.Inputs.BaseText);

  Cruddy.Inputs.Checkbox = (function(_super) {
    __extends(Checkbox, _super);

    function Checkbox() {
      return Checkbox.__super__.constructor.apply(this, arguments);
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
      return this.setValue(this.input.prop("checked"));
    };

    Checkbox.prototype.applyChanges = function(value, external) {
      if (external) {
        this.input.prop("checked", value);
      }
      return this;
    };

    Checkbox.prototype.render = function() {
      this.input = $("<input>", {
        type: "checkbox",
        checked: this.getValue()
      });
      this.$el.append(this.input);
      if (this.label != null) {
        this.$el.append(this.label);
      }
      return this;
    };

    return Checkbox;

  })(Cruddy.Inputs.Base);

  Cruddy.Inputs.Boolean = (function(_super) {
    __extends(Boolean, _super);

    function Boolean() {
      return Boolean.__super__.constructor.apply(this, arguments);
    }

    Boolean.prototype.events = {
      "click .btn": "check"
    };

    Boolean.prototype.initialize = function(options) {
      var _ref1;
      this.tripleState = (_ref1 = options.tripleState) != null ? _ref1 : false;
      return Boolean.__super__.initialize.apply(this, arguments);
    };

    Boolean.prototype.check = function(e) {
      var currentValue, value;
      value = !!$(e.target).data("value");
      currentValue = this.model.get(this.key);
      if (value === currentValue && this.tripleState) {
        value = null;
      }
      return this.setValue(value);
    };

    Boolean.prototype.applyChanges = function(value) {
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

    Boolean.prototype.render = function() {
      this.$el.html(this.template());
      this.values = this.$(".btn");
      return Boolean.__super__.render.apply(this, arguments);
    };

    Boolean.prototype.template = function() {
      return "<div class=\"btn-group\">\n    <button type=\"button\" class=\"btn btn-default\" data-value=\"1\">" + Cruddy.lang.yes + "</button>\n    <button type=\"button\" class=\"btn btn-default\" data-value=\"0\">" + Cruddy.lang.no + "</button>\n</div>";
    };

    return Boolean;

  })(Cruddy.Inputs.Base);

  Cruddy.Inputs.EntityDropdown = (function(_super) {
    __extends(EntityDropdown, _super);

    function EntityDropdown() {
      return EntityDropdown.__super__.constructor.apply(this, arguments);
    }

    EntityDropdown.prototype.className = "entity-dropdown";

    EntityDropdown.prototype.events = {
      "click .btn-remove": "removeItem",
      "click .btn-edit": "editItem",
      "keydown [type=search]": "searchKeydown",
      "show.bs.dropdown": "renderDropdown",
      "shown.bs.dropdown": function() {
        after_break((function(_this) {
          return function() {
            return _this.selector.focus();
          };
        })(this));
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
      var _ref1, _ref2;
      if (options.multiple != null) {
        this.multiple = options.multiple;
      }
      if (options.reference != null) {
        this.reference = options.reference;
      }
      if (options.owner != null) {
        this.owner = options.owner;
      }
      this.allowEdit = ((_ref1 = options.allowEdit) != null ? _ref1 : true) && this.reference.updatePermitted();
      this.active = false;
      this.placeholder = (_ref2 = options.placeholder) != null ? _ref2 : Cruddy.lang.not_selected;
      this.disableDropdown = false;
      if (options.constraint) {
        this.constraint = options.constraint;
        this.listenTo(this.model, "change:" + this.constraint.field, function() {
          return this.checkToDisable().applyConstraint(true);
        });
      }
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
      return this.setValue(value);
    };

    EntityDropdown.prototype.editItem = function(e) {
      var item, target, xhr;
      item = this.model.get(this.key);
      if (this.multiple) {
        item = item[this.getKey(e)];
      }
      if (!item) {
        return;
      }
      target = $(e.currentTarget).prop("disabled", true);
      xhr = this.reference.load(item.id).done((function(_this) {
        return function(instance) {
          _this.innerForm = new Cruddy.Entity.Form({
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
        };
      })(this));
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

    EntityDropdown.prototype.applyConstraint = function(reset) {
      var value, _ref1;
      if (reset == null) {
        reset = false;
      }
      if (this.selector) {
        value = this.model.get(this.constraint.field);
        if ((_ref1 = this.selector.dataSource) != null) {
          _ref1.filters.set(this.constraint.otherField, value);
        }
        this.selector.createAttributes[this.constraint.otherField] = value;
      }
      if (reset) {
        this.model.set(this.key, this.multiple ? [] : null);
      }
      return this;
    };

    EntityDropdown.prototype.checkToDisable = function() {
      if (this.constraint) {
        if (_.isEmpty(this.model.get(this.constraint.field))) {
          this.disable();
        } else {
          this.enable();
        }
      }
      return this;
    };

    EntityDropdown.prototype.disable = function() {
      if (this.disableDropdown) {
        return this;
      }
      this.disableDropdown = true;
      return this.toggleDisableControls();
    };

    EntityDropdown.prototype.enable = function() {
      if (!this.disableDropdown) {
        return this;
      }
      this.disableDropdown = false;
      return this.toggleDisableControls();
    };

    EntityDropdown.prototype.toggleDisableControls = function() {
      this.dropdownBtn.prop("disabled", this.disableDropdown);
      if (!this.multiple) {
        this.itemTitle.prop("disabled", this.disableDropdown);
      }
      return this;
    };

    EntityDropdown.prototype.renderDropdown = function(e) {
      var dataSource;
      if (this.disableDropdown) {
        e.preventDefault();
        return;
      }
      this.opened = true;
      if (!this.selector) {
        this.selector = new Cruddy.Inputs.EntitySelector({
          model: this.model,
          key: this.key,
          multiple: this.multiple,
          reference: this.reference,
          allowCreate: this.allowEdit,
          owner: this.owner
        });
        if (this.constraint) {
          this.applyConstraint();
        }
        this.$el.append(this.selector.render().el);
      }
      dataSource = this.selector.dataSource;
      if (!dataSource.inProgress()) {
        dataSource.refresh();
      }
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

    EntityDropdown.prototype.applyChanges = function(value) {
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
      this.dropdownBtn = this.$("#" + this.cid + "-dropdown");
      this.$el.attr("id", this.cid);
      this.checkToDisable();
      return this;
    };

    EntityDropdown.prototype.renderMultiple = function() {
      this.$el.append(this.items = $("<div>", {
        "class": "items"
      }));
      this.$el.append("<button type=\"button\" class=\"btn btn-default btn-block dropdown-toggle ed-dropdown-toggle\" data-toggle=\"dropdown\" id=\"" + this.cid + "-dropdown\" data-target=\"#" + this.cid + "\">\n    " + Cruddy.lang.choose + "\n    <span class=\"caret\"></span>\n</button>");
      return this.renderItems();
    };

    EntityDropdown.prototype.renderItems = function() {
      var html, key, value, _i, _len, _ref1;
      html = "";
      _ref1 = this.getValue();
      for (key = _i = 0, _len = _ref1.length; _i < _len; key = ++_i) {
        value = _ref1[key];
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
      value = this.getValue();
      this.itemTitle.val(value ? value.title : "");
      this.itemDelete.toggle(!!value);
      this.itemEdit.toggle(!!value);
      return this;
    };

    EntityDropdown.prototype.itemTemplate = function(value, key) {
      var html;
      if (key == null) {
        key = null;
      }
      html = "<div class=\"input-group input-group ed-item " + (!this.multiple ? "ed-dropdown-toggle" : "") + "\" data-key=\"" + key + "\">\n    <input type=\"text\" class=\"form-control\" " + (!this.multiple ? "data-toggle='dropdown' data-target='#" + this.cid + "' placeholder='" + this.placeholder + "'" : "tab-index='-1'") + " value=\"" + (_.escape(value)) + "\" readonly>\n    <div class=\"input-group-btn\">";
      if (this.allowEdit) {
        html += "<button type=\"button\" class=\"btn btn-default btn-edit\" tabindex=\"-1\">\n    <span class=\"glyphicon glyphicon-pencil\"></span>\n</button>";
      }
      html += "<button type=\"button\" class=\"btn btn-default btn-remove\" tabindex=\"-1\">\n    <span class=\"glyphicon glyphicon-remove\"></span>\n</button>";
      if (!this.multiple) {
        html += "<button type=\"button\" class=\"btn btn-default btn-dropdown dropdown-toggle\" data-toggle=\"dropdown\" id=\"" + this.cid + "-dropdown\" data-target=\"#" + this.cid + "\" tab-index=\"1\">\n    <span class=\"glyphicon glyphicon-search\"></span>\n</button>";
      }
      return html += "</div></div>";
    };

    EntityDropdown.prototype.dispose = function() {
      var _ref1, _ref2;
      if ((_ref1 = this.selector) != null) {
        _ref1.remove();
      }
      if ((_ref2 = this.innerForm) != null) {
        _ref2.remove();
      }
      return this;
    };

    EntityDropdown.prototype.remove = function() {
      this.dispose();
      return EntityDropdown.__super__.remove.apply(this, arguments);
    };

    return EntityDropdown;

  })(Cruddy.Inputs.Base);

  Cruddy.Inputs.EntitySelector = (function(_super) {
    __extends(EntitySelector, _super);

    function EntitySelector() {
      return EntitySelector.__super__.constructor.apply(this, arguments);
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
      var _ref1, _ref2, _ref3, _ref4;
      EntitySelector.__super__.initialize.apply(this, arguments);
      this.filter = (_ref1 = options.filter) != null ? _ref1 : false;
      this.multiple = (_ref2 = options.multiple) != null ? _ref2 : false;
      this.reference = options.reference;
      this.allowSearch = (_ref3 = options.allowSearch) != null ? _ref3 : true;
      this.allowCreate = ((_ref4 = options.allowCreate) != null ? _ref4 : true) && this.reference.createPermitted();
      this.createAttributes = {};
      this.data = [];
      this.buildSelected(this.model.get(this.key));
      if (this.reference.viewPermitted()) {
        this.primaryKey = "id";
        this.dataSource = this.reference.search({
          ajaxOptions: {
            data: {
              owner: options.owner
            }
          }
        });
        this.listenTo(this.dataSource, "request", this.loading);
        this.listenTo(this.dataSource, "data", this.renderItems);
        this.listenTo(this.dataSource, "error", this.displayError);
      }
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
        return item.id.toString() === id;
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
      return this.setValue(value);
    };

    EntitySelector.prototype.more = function() {
      if (!this.dataSource || this.dataSource.inProgress()) {
        return;
      }
      this.dataSource.next();
      return false;
    };

    EntitySelector.prototype.add = function(e) {
      var instance;
      e.preventDefault();
      e.stopPropagation();
      instance = this.reference.createInstance({
        attributes: this.createAttributes
      });
      console.log(instance);
      this.innerForm = new Cruddy.Entity.Form({
        model: instance,
        inner: true
      });
      this.innerForm.render().$el.appendTo(document.body);
      after_break((function(_this) {
        return function() {
          return _this.innerForm.show();
        };
      })(this));
      this.listenToOnce(this.innerForm, "remove", (function(_this) {
        return function() {
          return _this.innerForm = null;
        };
      })(this));
      this.listenToOnce(instance, "sync", (function(_this) {
        return function(instance, resp) {
          _this.select({
            id: instance.id,
            title: resp.data.title
          });
          _this.dataSource.set("search", "");
          return _this.innerForm.remove();
        };
      })(this));
      return this;
    };

    EntitySelector.prototype.applyChanges = function(data) {
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
      var _ref1;
      if ((_ref1 = this.moreElement) != null) {
        _ref1.addClass("loading");
      }
      return this;
    };

    EntitySelector.prototype.renderItems = function() {
      var html, item, _i, _len, _ref1;
      this.moreElement = null;
      html = "";
      if (this.dataSource.data.length || this.dataSource.more) {
        _ref1 = this.dataSource.data;
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          item = _ref1[_i];
          html += this.renderItem(item);
        }
        if (this.dataSource.more) {
          html += "<li class=\"more " + (this.dataSource.inProgress() ? "loading" : "") + "\">" + Cruddy.lang.more + "</li>";
        }
      } else {
        html += "<li class='empty'>" + Cruddy.lang.no_results + "</li>";
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
      if (this.reference.viewPermitted()) {
        this.dispose();
        this.$el.html(this.template());
        this.items = this.$(".items");
        this.renderItems();
        this.items.parent().on("scroll", $.proxy(this, "checkForMore"));
        if (this.allowSearch) {
          this.renderSearch();
        }
      } else {
        this.$el.html("<span class=error>" + Cruddy.lang.forbidden + "</span>");
      }
      return this;
    };

    EntitySelector.prototype.renderSearch = function() {
      this.searchInput = new Cruddy.Inputs.Search({
        model: this.dataSource,
        key: "search"
      });
      this.$el.prepend(this.searchInput.render().el);
      this.searchInput.$el.wrap("<div class='" + (this.allowCreate ? "input-group" : "") + " search-input-container'></div>");
      if (this.allowCreate) {
        this.searchInput.$el.after("<div class='input-group-btn'>\n    <button type='button' class='btn btn-default btn-add' tabindex='-1'>\n        <span class='glyphicon glyphicon-plus'></span>\n    </button>\n</div>");
      }
      return this;
    };

    EntitySelector.prototype.template = function() {
      return "<div class=\"items-container\"><ul class=\"items\"><li class=\"more loading\"></li></ul></div>";
    };

    EntitySelector.prototype.focus = function() {
      var _ref1;
      ((_ref1 = this.searchInput) != null ? _ref1.focus() : void 0) || this.entity.done((function(_this) {
        return function() {
          return _this.searchInput.focus();
        };
      })(this));
      return this;
    };

    EntitySelector.prototype.dispose = function() {
      var _ref1, _ref2;
      if ((_ref1 = this.searchInput) != null) {
        _ref1.remove();
      }
      if ((_ref2 = this.innerForm) != null) {
        _ref2.remove();
      }
      return this;
    };

    EntitySelector.prototype.remove = function() {
      this.dispose();
      return EntitySelector.__super__.remove.apply(this, arguments);
    };

    return EntitySelector;

  })(Cruddy.Inputs.Base);

  Cruddy.Inputs.FileList = (function(_super) {
    __extends(FileList, _super);

    function FileList() {
      return FileList.__super__.constructor.apply(this, arguments);
    }

    FileList.prototype.className = "file-list";

    FileList.prototype.events = {
      "change [type=file]": "appendFiles",
      "click .action-delete": "deleteFile"
    };

    FileList.prototype.initialize = function(options) {
      var _ref1, _ref2, _ref3;
      this.multiple = (_ref1 = options.multiple) != null ? _ref1 : false;
      this.formatter = (_ref2 = options.formatter) != null ? _ref2 : {
        format: function(value) {
          if (value instanceof File) {
            return value.name;
          } else {
            return value;
          }
        }
      };
      this.accepts = (_ref3 = options.accepts) != null ? _ref3 : "";
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
      var file, value, _i, _len, _ref1;
      if (e.target.files.length === 0) {
        return;
      }
      if (this.multiple) {
        value = _.clone(this.model.get(this.key));
        _ref1 = e.target.files;
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          file = _ref1[_i];
          value.push(file);
        }
      } else {
        value = e.target.files[0];
      }
      return this.setValue(value);
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
      html += this.renderInput(this.multiple ? "<span class='glyphicon glyphicon-plus'></span> " + Cruddy.lang.add : Cruddy.lang.choose);
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

  })(Cruddy.Inputs.Base);

  Cruddy.Inputs.ImageList = (function(_super) {
    __extends(ImageList, _super);

    ImageList.prototype.className = "image-list";

    function ImageList() {
      this.readers = [];
      ImageList.__super__.constructor.apply(this, arguments);
    }

    ImageList.prototype.initialize = function(options) {
      var _ref1, _ref2;
      this.width = (_ref1 = options.width) != null ? _ref1 : 0;
      this.height = (_ref2 = options.height) != null ? _ref2 : 80;
      return ImageList.__super__.initialize.apply(this, arguments);
    };

    ImageList.prototype.render = function() {
      var reader, _i, _len, _ref1;
      ImageList.__super__.render.apply(this, arguments);
      _ref1 = this.readers;
      for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
        reader = _ref1[_i];
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
      return "<a href=\"" + (item instanceof File ? item.data || "#" : Cruddy.root + '/' + item) + "\" class=\"fancybox\">\n    <img src=\"" + image + "\" id=\"" + id + "\">\n</a>";
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

  })(Cruddy.Inputs.FileList);

  Cruddy.Inputs.Search = (function(_super) {
    __extends(Search, _super);

    function Search() {
      return Search.__super__.constructor.apply(this, arguments);
    }

    Search.prototype.attributes = {
      type: "search",
      placeholder: Cruddy.lang.search
    };

    Search.prototype.scheduleChange = function() {
      if (this.timeout != null) {
        clearTimeout(this.timeout);
      }
      this.timeout = setTimeout(((function(_this) {
        return function() {
          return _this.change();
        };
      })(this)), 300);
      return this;
    };

    Search.prototype.keydown = function(e) {
      if (e.keyCode === 8) {
        this.model.set(this.key, "");
        return false;
      }
      this.scheduleChange();
      return Search.__super__.keydown.apply(this, arguments);
    };

    return Search;

  })(Cruddy.Inputs.Text);

  Cruddy.Inputs.Slug = (function(_super) {
    __extends(Slug, _super);

    Slug.prototype.events = {
      "click .btn": "toggleSyncing"
    };

    function Slug(options) {
      this.input = new Cruddy.Inputs.Text(_.clone(options));
      if (options.className == null) {
        options.className = "input-group";
      }
      if (options.attributes != null) {
        delete options.attributes;
      }
      Slug.__super__.constructor.apply(this, arguments);
    }

    Slug.prototype.initialize = function(options) {
      var chars, _ref1, _ref2;
      chars = (_ref1 = options.chars) != null ? _ref1 : "a-z0-9\-_";
      this.regexp = new RegExp("[^" + chars + "]+", "g");
      this.separator = (_ref2 = options.separator) != null ? _ref2 : "-";
      this.key = options.key;
      this.ref = _.isArray(options.ref) ? options.ref : options.ref ? [options.ref] : void 0;
      return Slug.__super__.initialize.apply(this, arguments);
    };

    Slug.prototype.toggleSyncing = function() {
      if (this.syncButton.hasClass("active")) {
        this.unlink();
      } else {
        this.link();
      }
      return this;
    };

    Slug.prototype.link = function() {
      if (!this.ref) {
        return;
      }
      this.listenTo(this.model, "change:" + this.ref.join(" change:"), this.sync);
      this.syncButton.addClass("active");
      this.input.disable();
      return this.sync();
    };

    Slug.prototype.unlink = function() {
      if (this.ref != null) {
        this.stopListening(this.model, null, this.sync);
      }
      this.syncButton.removeClass("active");
      this.input.enable();
      return this;
    };

    Slug.prototype.linkable = function() {
      var modelValue, value;
      modelValue = this.model.get(this.key);
      value = this.getValue();
      return value === modelValue || modelValue === null && value === "";
    };

    Slug.prototype.convert = function(value) {
      if (value) {
        return value.toLocaleLowerCase().replace(/\s+/g, this.separator).replace(this.regexp, "");
      } else {
        return value;
      }
    };

    Slug.prototype.sync = function() {
      this.model.set(this.key, this.getValue());
      return this;
    };

    Slug.prototype.getValue = function() {
      var components, key, refValue, _i, _len, _ref1;
      components = [];
      _ref1 = this.ref;
      for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
        key = _ref1[_i];
        refValue = this.model.get(key);
        if (refValue) {
          components.push(refValue);
        }
      }
      if (components.length) {
        return this.convert(components.join(this.separator));
      } else {
        return "";
      }
    };

    Slug.prototype.render = function() {
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

    Slug.prototype.template = function() {
      if (this.ref == null) {
        return "";
      }
      return "<div class=\"input-group-btn\">\n    <button type=\"button\" tabindex=\"-1\" class=\"btn btn-default\" title=\"" + Cruddy.lang.slug_sync + "\"><span class=\"glyphicon glyphicon-link\"></span></button>\n</div>";
    };

    return Slug;

  })(Backbone.View);

  Cruddy.Inputs.Select = (function(_super) {
    __extends(Select, _super);

    function Select() {
      return Select.__super__.constructor.apply(this, arguments);
    }

    Select.prototype.tagName = "select";

    Select.prototype.initialize = function(options) {
      var _ref1, _ref2;
      this.items = (_ref1 = options.items) != null ? _ref1 : {};
      this.prompt = (_ref2 = options.prompt) != null ? _ref2 : null;
      return Select.__super__.initialize.apply(this, arguments);
    };

    Select.prototype.applyChanges = function(data, external) {
      if (external) {
        this.$("[value='" + data + "']").prop("selected", true);
      }
      return this;
    };

    Select.prototype.render = function() {
      this.$el.html(this.template());
      return Select.__super__.render.apply(this, arguments);
    };

    Select.prototype.template = function() {
      var html, key, value, _ref1, _ref2;
      html = "";
      html += this.optionTemplate("", (_ref1 = this.prompt) != null ? _ref1 : "");
      _ref2 = this.items;
      for (key in _ref2) {
        value = _ref2[key];
        html += this.optionTemplate(key, value);
      }
      return html;
    };

    Select.prototype.optionTemplate = function(value, title) {
      return "<option value=\"" + (_.escape(value)) + "\">" + (_.escape(title)) + "</option>";
    };

    return Select;

  })(Cruddy.Inputs.Text);

  Cruddy.Inputs.Code = (function(_super) {
    __extends(Code, _super);

    function Code() {
      return Code.__super__.constructor.apply(this, arguments);
    }

    Code.prototype.initialize = function(options) {
      var session, _ref1, _ref2;
      this.$el.height(((_ref1 = options.height) != null ? _ref1 : 100) + "px");
      this.editor = ace.edit(this.el);
      this.editor.setTheme("ace/theme/" + ((_ref2 = options.theme) != null ? _ref2 : Cruddy.ace_theme));
      session = this.editor.getSession();
      if (options.mode) {
        session.setMode("ace/mode/" + options.mode);
      }
      session.setUseWrapMode(true);
      session.setWrapLimitRange(null, null);
      return Code.__super__.initialize.apply(this, arguments);
    };

    Code.prototype.applyChanges = function(value, external) {
      if (external) {
        this.editor.setValue(value);
        this.editor.getSession().getSelection().clearSelection();
      }
      return this;
    };

    Code.prototype.render = function() {
      this.editor.on("blur", (function(_this) {
        return function() {
          return _this.model.set(_this.key, _this.editor.getValue(), {
            input: _this
          });
        };
      })(this));
      return Code.__super__.render.apply(this, arguments);
    };

    Code.prototype.remove = function() {
      var _ref1;
      if ((_ref1 = this.editor) != null) {
        _ref1.destroy();
      }
      this.editor = null;
      return Code.__super__.remove.apply(this, arguments);
    };

    Code.prototype.focus = function() {
      var _ref1;
      if ((_ref1 = this.editor) != null) {
        _ref1.focus();
      }
      return this;
    };

    return Code;

  })(Cruddy.Inputs.Base);

  Cruddy.Inputs.Markdown = (function(_super) {
    __extends(Markdown, _super);

    function Markdown() {
      return Markdown.__super__.constructor.apply(this, arguments);
    }

    Markdown.prototype.events = {
      "show.bs.tab [data-toggle=tab]": "showTab",
      "shown.bs.tab [data-toggle=tab]": "shownTab"
    };

    Markdown.prototype.initialize = function(options) {
      var _ref1;
      this.height = (_ref1 = options.height) != null ? _ref1 : 200;
      this.editorInput = new Cruddy.Inputs.Code({
        model: this.model,
        key: this.key,
        theme: options.theme,
        mode: "markdown",
        height: this.height
      });
      return Markdown.__super__.initialize.apply(this, arguments);
    };

    Markdown.prototype.showTab = function(e) {
      if ($(e.target).data("tab") === "preview") {
        this.renderPreview();
      }
      return this;
    };

    Markdown.prototype.shownTab = function(e) {
      if ($(e.traget).data("tab") === "editor") {
        return this.editorInput.focus();
      }
    };

    Markdown.prototype.render = function() {
      this.$el.html(this.template());
      this.$(".tab-pane-editor").append(this.editorInput.render().el);
      this.preview = this.$(".tab-pane-preview");
      return this;
    };

    Markdown.prototype.renderPreview = function() {
      this.preview.html(marked(this.getValue()));
      return this;
    };

    Markdown.prototype.template = function() {
      return "<div class=\"markdown-editor\">\n    <a href=\"https://help.github.com/articles/github-flavored-markdown\" target=\"_blank\" class=\"hint\">GitHub flavored markdown</a>\n\n    <ul class=\"nav nav-tabs\">\n        <li class=\"active\"><a href=\"#" + this.cid + "-editor\" data-toggle=\"tab\" data-tab=\"editor\" tab-index=\"-1\">" + Cruddy.lang.markdown_source + "</a></li>\n        <li><a href=\"#" + this.cid + "-preview\" data-toggle=\"tab\" data-tab=\"preview\" tab-index=\"-1\">" + Cruddy.lang.markdown_parsed + "</a></li>\n    </ul>\n\n    <div class=\"tab-content\">\n        <div class=\"tab-pane-editor tab-pane active\" id=\"" + this.cid + "-editor\"></div>\n        <div class=\"tab-pane-preview tab-pane\" id=\"" + this.cid + "-preview\" style=\"height:" + this.height + "px\"></div>\n    </div>\n</div>";
    };

    Markdown.prototype.focus = function() {
      var tab;
      tab = this.$("[data-tab=editor]");
      if (tab.hasClass("active")) {
        this.editorInput.focus();
      } else {
        tab.tab("show");
      }
      return this;
    };

    return Markdown;

  })(Cruddy.Inputs.Base);

  Cruddy.Inputs.NumberFilter = (function(_super) {
    __extends(NumberFilter, _super);

    function NumberFilter() {
      return NumberFilter.__super__.constructor.apply(this, arguments);
    }

    NumberFilter.prototype.className = "input-group number-filter";

    NumberFilter.prototype.events = {
      "click .dropdown-menu a": "changeOperator",
      "change": "changeValue"
    };

    NumberFilter.prototype.initialize = function() {
      this.defaultOp = "=";
      if (!this.getValue()) {
        this.setValue(this.makeValue(this.defaultOp, ""), {
          silent: true
        });
      }
      return NumberFilter.__super__.initialize.apply(this, arguments);
    };

    NumberFilter.prototype.changeOperator = function(e) {
      var op, value;
      e.preventDefault();
      op = $(e.currentTarget).data("op");
      value = this.getValue();
      if (value.op !== op) {
        this.setValue(this.makeValue(op, value.val));
      }
      return this;
    };

    NumberFilter.prototype.changeValue = function(e) {
      var value;
      value = this.getValue();
      this.setValue(this.makeValue(value.op, e.target.value));
      return this;
    };

    NumberFilter.prototype.applyChanges = function(value, external) {
      this.$(".dropdown-menu li").removeClass("active");
      this.$(".dropdown-menu a[data-op='" + value.op + "']").parent().addClass("active");
      this.op.text(value.op);
      if (external) {
        this.input.val(value.val);
      }
      return this;
    };

    NumberFilter.prototype.render = function() {
      this.$el.html(this.template());
      this.op = this.$component("op");
      this.input = this.$component("input");
      this.reset = this.$component("reset");
      return NumberFilter.__super__.render.apply(this, arguments);
    };

    NumberFilter.prototype.template = function() {
      return "<div class=\"input-group-btn\">\n    <button type=\"button\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\">\n        <span id=\"" + (this.componentId("op")) + "\" class=\"value\">=</span>\n        <span class=\"caret\"></span>\n    </button>\n\n    <ul class=\"dropdown-menu\">\n        <li><a href=\"#\" data-op=\"=\">=</a></li>\n        <li><a href=\"#\" data-op=\"&gt;\">&gt;</a></li>\n        <li><a href=\"#\" data-op=\"&lt;\">&lt;</a></li>\n    </ul>\n</div>\n\n<input type=\"text\" class=\"form-control\" id=\"" + (this.componentId("input")) + "\">";
    };

    NumberFilter.prototype.makeValue = function(op, val) {
      return {
        op: op,
        val: val
      };
    };

    return NumberFilter;

  })(Cruddy.Inputs.Base);

  Cruddy.Fields = new Factory;

  Cruddy.Fields.BaseView = (function(_super) {
    __extends(BaseView, _super);

    function BaseView(options) {
      var base, className, classes, field;
      this.field = field = options.field;
      this.inputId = options.model.entity.id + "_" + field.id;
      base = " field-";
      classes = [field.getType(), field.id, this.inputId];
      className = "field" + base + classes.join(base);
      if (field.isRequired()) {
        className += " required";
      }
      className += " form-group";
      this.className = this.className ? className + " " + this.className : className;
      BaseView.__super__.constructor.apply(this, arguments);
    }

    BaseView.prototype.initialize = function(options) {
      this.listenTo(this.model, "sync", this.handleSync);
      this.listenTo(this.model, "request", this.handleRequest);
      this.listenTo(this.model, "invalid", this.handleInvalid);
      return this;
    };

    BaseView.prototype.handleSync = function() {
      return this.toggleVisibility();
    };

    BaseView.prototype.handleRequest = function() {
      return this.hideError();
    };

    BaseView.prototype.handleInvalid = function(model, errors) {
      var error;
      if (this.field.id in errors) {
        error = errors[this.field.id];
        this.showError(_.isArray(error) ? _.first(error) : error);
      }
      return this;
    };

    BaseView.prototype.toggleVisibility = function() {
      this.$el.toggle(this.isVisible());
      return this;
    };

    BaseView.prototype.hideError = function() {
      this.error.hide();
      return this;
    };

    BaseView.prototype.showError = function(message) {
      this.error.text(message).show();
      return this;
    };

    BaseView.prototype.focus = function() {
      return this;
    };

    BaseView.prototype.render = function() {
      this.$(".field-help").tooltip({
        container: "body",
        placement: "left"
      });
      this.error = this.$("#" + this.cid + "-error");
      return this;
    };

    BaseView.prototype.helpTemplate = function() {
      var help;
      help = this.field.getHelp();
      if (help) {
        return "<span class=\"glyphicon glyphicon-question-sign field-help\" title=\"" + (_.escape(help)) + "\"></span>";
      } else {
        return "";
      }
    };

    BaseView.prototype.errorTemplate = function() {
      return "<span class=\"help-block error\" id=\"" + this.cid + "-error\"></span>";
    };

    BaseView.prototype.isVisible = function() {
      return this.field.isEditable(this.model.action()) || !this.model.isNew();
    };

    BaseView.prototype.dispose = function() {
      return this;
    };

    BaseView.prototype.remove = function() {
      this.dispose();
      return BaseView.__super__.remove.apply(this, arguments);
    };

    return BaseView;

  })(Backbone.View);

  Cruddy.Fields.InputView = (function(_super) {
    __extends(InputView, _super);

    function InputView() {
      return InputView.__super__.constructor.apply(this, arguments);
    }

    InputView.prototype.handleRequest = function(model) {
      this.isEditable = this.field.isEditable(model.action());
      return InputView.__super__.handleRequest.apply(this, arguments);
    };

    InputView.prototype.handleSync = function(model) {
      if (this.field.isEditable(model.action()) !== this.isEditable) {
        this.render();
      }
      return InputView.__super__.handleSync.apply(this, arguments);
    };

    InputView.prototype.hideError = function() {
      this.$el.removeClass("has-error");
      return InputView.__super__.hideError.apply(this, arguments);
    };

    InputView.prototype.showError = function() {
      this.$el.addClass("has-error");
      return InputView.__super__.showError.apply(this, arguments);
    };

    InputView.prototype.render = function() {
      this.dispose();
      this.$el.html(this.template());
      this.input = this.field.createInput(this.model);
      this.$el.append(this.input.render().el);
      this.$el.append(this.errorTemplate());
      this.toggleVisibility();
      this.isEditable = this.field.isEditable(this.model.action());
      return InputView.__super__.render.apply(this, arguments);
    };

    InputView.prototype.label = function(label) {
      if (label == null) {
        label = this.field.getLabel();
      }
      return "<label for=\"" + this.inputId + "\" class=\"field-label\">\n    " + (this.helpTemplate()) + (_.escape(label)) + "\n</label>";
    };

    InputView.prototype.template = function() {
      return this.label();
    };

    InputView.prototype.focus = function() {
      this.input.focus();
      return this;
    };

    InputView.prototype.dispose = function() {
      var _ref1;
      if ((_ref1 = this.input) != null) {
        _ref1.remove();
      }
      return this;
    };

    return InputView;

  })(Cruddy.Fields.BaseView);

  Cruddy.Fields.Base = (function(_super) {
    __extends(Base, _super);

    function Base() {
      return Base.__super__.constructor.apply(this, arguments);
    }

    Base.prototype.viewConstructor = Cruddy.Fields.InputView;

    Base.prototype.createView = function(model) {
      return new this.viewConstructor({
        model: model,
        field: this
      });
    };

    Base.prototype.createInput = function(model) {
      var input;
      if (this.isEditable(model.action()) && model.isSaveable()) {
        input = this.createEditableInput(model);
      }
      return input || new Cruddy.Inputs.Static({
        model: model,
        key: this.id,
        formatter: this
      });
    };

    Base.prototype.createEditableInput = function(model) {
      return null;
    };

    Base.prototype.createFilterInput = function(model) {
      return null;
    };

    Base.prototype.getFilterLabel = function() {
      return this.attributes.label;
    };

    Base.prototype.format = function(value) {
      return value || "n/a";
    };

    Base.prototype.getLabel = function() {
      return this.attributes.label;
    };

    Base.prototype.isEditable = function(action) {
      return this.attributes.fillable && this.attributes.disabled !== true && this.attributes.disabled !== action;
    };

    Base.prototype.isRequired = function() {
      return this.attributes.required;
    };

    Base.prototype.isUnique = function() {
      return this.attributes.unique;
    };

    return Base;

  })(Attribute);

  Cruddy.Fields.Input = (function(_super) {
    __extends(Input, _super);

    function Input() {
      return Input.__super__.constructor.apply(this, arguments);
    }

    Input.prototype.createEditableInput = function(model) {
      var attributes, type;
      attributes = {
        placeholder: this.attributes.placeholder
      };
      type = this.attributes.input_type;
      if (type === "textarea") {
        attributes.rows = this.attributes.rows;
        return new Cruddy.Inputs.Textarea({
          model: model,
          key: this.id,
          attributes: attributes
        });
      } else {
        attributes.type = type;
        return new Cruddy.Inputs.Text({
          model: model,
          key: this.id,
          mask: this.attributes.mask,
          attributes: attributes
        });
      }
    };

    Input.prototype.format = function(value) {
      if (this.attributes.input_type === "textarea") {
        return "<pre>" + Input.__super__.format.apply(this, arguments) + "</pre>";
      } else {
        return Input.__super__.format.apply(this, arguments);
      }
    };

    return Input;

  })(Cruddy.Fields.Base);

  Cruddy.Fields.DateTime = (function(_super) {
    __extends(DateTime, _super);

    function DateTime() {
      return DateTime.__super__.constructor.apply(this, arguments);
    }

    DateTime.prototype.format = function(value) {
      if (value === null) {
        return Cruddy.lang.never;
      } else {
        return moment.unix(value).calendar();
      }
    };

    return DateTime;

  })(Cruddy.Fields.Input);

  Cruddy.Fields.Boolean = (function(_super) {
    __extends(Boolean, _super);

    function Boolean() {
      return Boolean.__super__.constructor.apply(this, arguments);
    }

    Boolean.prototype.createEditableInput = function(model) {
      return new Cruddy.Inputs.Boolean({
        model: model,
        key: this.id
      });
    };

    Boolean.prototype.createFilterInput = function(model) {
      return new Cruddy.Inputs.Boolean({
        model: model,
        key: this.id,
        tripleState: true
      });
    };

    Boolean.prototype.format = function(value) {
      if (value) {
        return Cruddy.lang.yes;
      } else {
        return Cruddy.lang.no;
      }
    };

    return Boolean;

  })(Cruddy.Fields.Base);

  Cruddy.Fields.BaseRelation = (function(_super) {
    __extends(BaseRelation, _super);

    function BaseRelation() {
      return BaseRelation.__super__.constructor.apply(this, arguments);
    }

    BaseRelation.prototype.isVisible = function() {
      return this.getReference().viewPermitted() && BaseRelation.__super__.isVisible.apply(this, arguments);
    };

    BaseRelation.prototype.getReference = function() {
      if (!this.reference) {
        this.reference = Cruddy.app.entity(this.attributes.reference);
      }
      return this.reference;
    };

    BaseRelation.prototype.getFilterLabel = function() {
      return this.getReference().getSingularTitle();
    };

    return BaseRelation;

  })(Cruddy.Fields.Base);

  Cruddy.Fields.Relation = (function(_super) {
    __extends(Relation, _super);

    function Relation() {
      return Relation.__super__.constructor.apply(this, arguments);
    }

    Relation.prototype.createEditableInput = function(model) {
      return new Cruddy.Inputs.EntityDropdown({
        model: model,
        key: this.id,
        multiple: this.attributes.multiple,
        reference: this.getReference(),
        owner: this.entity.id + "." + this.id,
        constraint: this.attributes.constraint
      });
    };

    Relation.prototype.createFilterInput = function(model) {
      return new Cruddy.Inputs.EntityDropdown({
        model: model,
        key: this.id,
        reference: this.getReference(),
        allowEdit: false,
        placeholder: Cruddy.lang.any_value,
        owner: this.entity.id + "." + this.id,
        constraint: this.attributes.constraint
      });
    };

    Relation.prototype.format = function(value) {
      if (_.isEmpty(value)) {
        return Cruddy.lang.not_selected;
      }
      if (this.attributes.multiple) {
        return _.pluck(value, "title").join(", ");
      } else {
        return value.title;
      }
    };

    Relation.prototype.isEditable = function() {
      return Relation.__super__.isEditable.apply(this, arguments) && this.getReference().viewPermitted();
    };

    Relation.prototype.canFilter = function() {
      return Relation.__super__.canFilter.apply(this, arguments) && this.getReference().viewPermitted();
    };

    return Relation;

  })(Cruddy.Fields.BaseRelation);

  Cruddy.Fields.File = (function(_super) {
    __extends(File, _super);

    function File() {
      return File.__super__.constructor.apply(this, arguments);
    }

    File.prototype.createEditableInput = function(model) {
      return new Cruddy.Inputs.FileList({
        model: model,
        key: this.id,
        multiple: this.attributes.multiple,
        accepts: this.attributes.accepts
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

  })(Cruddy.Fields.Base);

  Cruddy.Fields.Image = (function(_super) {
    __extends(Image, _super);

    function Image() {
      return Image.__super__.constructor.apply(this, arguments);
    }

    Image.prototype.createEditableInput = function(model) {
      return new Cruddy.Inputs.ImageList({
        model: model,
        key: this.id,
        width: this.attributes.width,
        height: this.attributes.height,
        multiple: this.attributes.multiple,
        accepts: this.attributes.accepts
      });
    };

    return Image;

  })(Cruddy.Fields.File);

  Cruddy.Fields.Slug = (function(_super) {
    __extends(Slug, _super);

    function Slug() {
      return Slug.__super__.constructor.apply(this, arguments);
    }

    Slug.prototype.createEditableInput = function(model) {
      return new Cruddy.Inputs.Slug({
        model: model,
        key: this.id,
        chars: this.attributes.chars,
        ref: this.attributes.ref,
        separator: this.attributes.separator,
        attributes: {
          placeholder: this.attributes.placeholder
        }
      });
    };

    return Slug;

  })(Cruddy.Fields.Base);

  Cruddy.Fields.Enum = (function(_super) {
    __extends(Enum, _super);

    function Enum() {
      return Enum.__super__.constructor.apply(this, arguments);
    }

    Enum.prototype.createEditableInput = function(model) {
      return new Cruddy.Inputs.Select({
        model: model,
        key: this.id,
        prompt: this.attributes.prompt,
        items: this.attributes.items
      });
    };

    Enum.prototype.createFilterInput = function(model) {
      return new Cruddy.Inputs.Select({
        model: model,
        key: this.id,
        prompt: Cruddy.lang.any_value,
        items: this.attributes.items
      });
    };

    Enum.prototype.format = function(value) {
      var items;
      items = this.attributes.items;
      if (value in items) {
        return items[value];
      } else {
        return "n/a";
      }
    };

    return Enum;

  })(Cruddy.Fields.Base);

  Cruddy.Fields.Markdown = (function(_super) {
    __extends(Markdown, _super);

    function Markdown() {
      return Markdown.__super__.constructor.apply(this, arguments);
    }

    Markdown.prototype.createEditableInput = function(model) {
      return new Cruddy.Inputs.Markdown({
        model: model,
        key: this.id,
        height: this.attributes.height,
        theme: this.attributes.theme
      });
    };

    return Markdown;

  })(Cruddy.Fields.Base);

  Cruddy.Fields.Code = (function(_super) {
    __extends(Code, _super);

    function Code() {
      return Code.__super__.constructor.apply(this, arguments);
    }

    Code.prototype.createEditableInput = function(model) {
      return new Cruddy.Inputs.Code({
        model: model,
        key: this.id,
        height: this.attributes.height,
        mode: this.attributes.mode,
        theme: this.attributes.theme
      });
    };

    return Code;

  })(Cruddy.Fields.Base);

  Cruddy.Fields.EmbeddedView = (function(_super) {
    __extends(EmbeddedView, _super);

    function EmbeddedView() {
      return EmbeddedView.__super__.constructor.apply(this, arguments);
    }

    EmbeddedView.prototype.className = "has-many-view";

    EmbeddedView.prototype.events = {
      "click .btn-create": "create"
    };

    EmbeddedView.prototype.initialize = function(options) {
      this.views = {};
      this.collection = this.model.get(this.field.id);
      this.listenTo(this.collection, "add", this.add);
      this.listenTo(this.collection, "remove", this.removeItem);
      return EmbeddedView.__super__.initialize.apply(this, arguments);
    };

    EmbeddedView.prototype.handleInvalid = function(model, errors) {
      if (this.field.id in errors && errors[this.field.id].length) {
        EmbeddedView.__super__.handleInvalid.apply(this, arguments);
      }
      return this;
    };

    EmbeddedView.prototype.create = function(e) {
      e.preventDefault();
      e.stopPropagation();
      this.collection.add(this.field.getReference().createInstance(), {
        focus: true
      });
      return this;
    };

    EmbeddedView.prototype.add = function(model, collection, options) {
      var view;
      this.views[model.cid] = view = new Cruddy.Fields.EmbeddedItemView({
        model: model,
        collection: this.collection,
        disabled: this.field.isEditable()
      });
      this.body.append(view.render().el);
      if (options != null ? options.focus : void 0) {
        after_break(function() {
          return view.focus();
        });
      }
      if (!this.focusable) {
        this.focusable = view;
      }
      this.update();
      return this;
    };

    EmbeddedView.prototype.removeItem = function(model) {
      var view;
      if (view = this.views[model.cid]) {
        view.remove();
        delete this.views[model.cid];
      }
      this.update();
      return this;
    };

    EmbeddedView.prototype.render = function() {
      var model, _i, _len, _ref1;
      this.dispose();
      this.$el.html(this.template());
      this.body = this.$("#" + this.cid + "-body");
      this.createButton = this.$(".btn-create");
      _ref1 = this.collection.models;
      for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
        model = _ref1[_i];
        this.add(model);
      }
      this.update();
      return EmbeddedView.__super__.render.apply(this, arguments);
    };

    EmbeddedView.prototype.update = function() {
      this.createButton.toggle(this.field.isMultiple() || this.collection.isEmpty());
      return this;
    };

    EmbeddedView.prototype.template = function() {
      var buttons, ref;
      ref = this.field.getReference();
      buttons = ref.createPermitted() ? b_btn("", "plus", ["default", "create"]) : "";
      return "<div class='header field-label'>\n    " + (this.helpTemplate()) + (_.escape(this.field.getLabel())) + " " + buttons + "\n</div>\n<div class=\"error-container has-error\">" + (this.errorTemplate()) + "</div>\n<div class='body' id='" + this.cid + "-body'></div>";
    };

    EmbeddedView.prototype.dispose = function() {
      var cid, view, _ref1;
      _ref1 = this.views;
      for (cid in _ref1) {
        view = _ref1[cid];
        view.remove();
      }
      this.views = {};
      this.focusable = null;
      return this;
    };

    EmbeddedView.prototype.remove = function() {
      this.dispose();
      return EmbeddedView.__super__.remove.apply(this, arguments);
    };

    EmbeddedView.prototype.focus = function() {
      var _ref1;
      if ((_ref1 = this.focusable) != null) {
        _ref1.focus();
      }
      return this;
    };

    return EmbeddedView;

  })(Cruddy.Fields.BaseView);

  Cruddy.Fields.EmbeddedItemView = (function(_super) {
    __extends(EmbeddedItemView, _super);

    function EmbeddedItemView() {
      return EmbeddedItemView.__super__.constructor.apply(this, arguments);
    }

    EmbeddedItemView.prototype.className = "has-many-item-view";

    EmbeddedItemView.prototype.events = {
      "click .btn-delete": "deleteItem"
    };

    EmbeddedItemView.prototype.initialize = function(options) {
      var _ref1;
      this.collection = options.collection;
      this.disabled = (_ref1 = options.disabled) != null ? _ref1 : true;
      return EmbeddedItemView.__super__.initialize.apply(this, arguments);
    };

    EmbeddedItemView.prototype.deleteItem = function(e) {
      e.preventDefault();
      e.stopPropagation();
      this.collection.remove(this.model);
      return this;
    };

    EmbeddedItemView.prototype.render = function() {
      this.dispose();
      this.$el.html(this.template());
      this.fieldList = new FieldList({
        model: this.model,
        disabled: this.disabled || !this.model.isSaveable()
      });
      this.$el.prepend(this.fieldList.render().el);
      return this;
    };

    EmbeddedItemView.prototype.template = function() {
      if (this.model.entity.deletePermitted() || this.model.isNew()) {
        return b_btn(Cruddy.lang["delete"], "trash", ["default", "sm", "delete"]);
      } else {
        return "";
      }
    };

    EmbeddedItemView.prototype.dispose = function() {
      var _ref1;
      if ((_ref1 = this.fieldList) != null) {
        _ref1.remove();
      }
      this.fieldList = null;
      return this;
    };

    EmbeddedItemView.prototype.remove = function() {
      this.dispose();
      return EmbeddedItemView.__super__.remove.apply(this, arguments);
    };

    EmbeddedItemView.prototype.focus = function() {
      var _ref1;
      if ((_ref1 = this.fieldList) != null) {
        _ref1.focus();
      }
      return this;
    };

    return EmbeddedItemView;

  })(Backbone.View);

  Cruddy.Fields.RelatedCollection = (function(_super) {
    __extends(RelatedCollection, _super);

    function RelatedCollection() {
      return RelatedCollection.__super__.constructor.apply(this, arguments);
    }

    RelatedCollection.prototype.initialize = function(items, options) {
      this.owner = options.owner;
      this.field = options.field;
      this.deleted = false;
      this.listenTo(this.owner, "sync", (function(_this) {
        return function() {
          return _this.deleted = false;
        };
      })(this));
      return RelatedCollection.__super__.initialize.apply(this, arguments);
    };

    RelatedCollection.prototype.remove = function() {
      this.deleted = true;
      return RelatedCollection.__super__.remove.apply(this, arguments);
    };

    RelatedCollection.prototype.hasChangedSinceSync = function() {
      var item, _i, _len, _ref1;
      if (this.deleted) {
        return true;
      }
      _ref1 = this.models;
      for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
        item = _ref1[_i];
        if (item.hasChangedSinceSync()) {
          return true;
        }
      }
      return false;
    };

    RelatedCollection.prototype.copy = function(copy) {
      var item, items;
      items = this.field.isUnique() ? [] : (function() {
        var _i, _len, _ref1, _results;
        _ref1 = this.models;
        _results = [];
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          item = _ref1[_i];
          _results.push(item.copy());
        }
        return _results;
      }).call(this);
      return new Cruddy.Fields.RelatedCollection(items, {
        owner: copy,
        field: this.field
      });
    };

    RelatedCollection.prototype.serialize = function() {
      var data, item, _i, _len, _ref1;
      if (this.field.isMultiple()) {
        data = {};
        _ref1 = this.models;
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          item = _ref1[_i];
          data[item.cid] = item;
        }
        return data;
      } else {
        return this.first();
      }
    };

    return RelatedCollection;

  })(Backbone.Collection);

  Cruddy.Fields.Embedded = (function(_super) {
    __extends(Embedded, _super);

    function Embedded() {
      return Embedded.__super__.constructor.apply(this, arguments);
    }

    Embedded.prototype.viewConstructor = Cruddy.Fields.EmbeddedView;

    Embedded.prototype.createInstance = function(model, items) {
      var item, ref;
      if (items instanceof Backbone.Collection) {
        return items;
      }
      if (!this.attributes.multiple) {
        items = (items || this.isRequired() ? [items] : []);
      }
      ref = this.getReference();
      items = (function() {
        var _i, _len, _results;
        _results = [];
        for (_i = 0, _len = items.length; _i < _len; _i++) {
          item = items[_i];
          _results.push(ref.createInstance(item));
        }
        return _results;
      })();
      return new Cruddy.Fields.RelatedCollection(items, {
        owner: model,
        field: this
      });
    };

    Embedded.prototype.applyValues = function(collection, items) {
      var item, ref;
      if (!this.attributes.multiple) {
        items = [items];
      }
      collection.set(_.pluck(items, "attributes"), {
        add: false
      });
      ref = this.getReference();
      collection.add((function() {
        var _i, _len, _results;
        _results = [];
        for (_i = 0, _len = items.length; _i < _len; _i++) {
          item = items[_i];
          if (!collection.get(item.id)) {
            _results.push(ref.createInstance(item));
          }
        }
        return _results;
      })());
      return this;
    };

    Embedded.prototype.hasChangedSinceSync = function(items) {
      return items.hasChangedSinceSync();
    };

    Embedded.prototype.copy = function(copy, items) {
      return items.copy(copy);
    };

    Embedded.prototype.processErrors = function(collection, errorsCollection) {
      var cid, errors, model;
      if (!_.isObject(errorsCollection)) {
        return;
      }
      if (!this.attributes.multiple) {
        model = collection.first();
        if (model) {
          model.trigger("invalid", model, errorsCollection);
        }
        return this;
      }
      for (cid in errorsCollection) {
        errors = errorsCollection[cid];
        model = collection.get(cid);
        if (model) {
          model.trigger("invalid", model, errors);
        }
      }
      return this;
    };

    Embedded.prototype.triggerRelated = function(event, collection, args) {
      var model, _i, _len, _ref1;
      _ref1 = collection.models;
      for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
        model = _ref1[_i];
        model.trigger.apply(model, [event, model].concat(args));
      }
      return this;
    };

    Embedded.prototype.isMultiple = function() {
      return this.attributes.multiple;
    };

    return Embedded;

  })(Cruddy.Fields.BaseRelation);

  Cruddy.Fields.Number = (function(_super) {
    __extends(Number, _super);

    function Number() {
      return Number.__super__.constructor.apply(this, arguments);
    }

    Number.prototype.createEditableInput = function(model) {
      return new Cruddy.Inputs.Text({
        model: model,
        key: this.id,
        attributes: {
          type: "text"
        }
      });
    };

    Number.prototype.createFilterInput = function(model) {
      return new Cruddy.Inputs.NumberFilter({
        model: model,
        key: this.id
      });
    };

    return Number;

  })(Cruddy.Fields.Base);

  Cruddy.Fields.Computed = (function(_super) {
    __extends(Computed, _super);

    function Computed() {
      return Computed.__super__.constructor.apply(this, arguments);
    }

    Computed.prototype.createInput = function(model) {
      return new Cruddy.Inputs.Static({
        model: model,
        key: this.id,
        formatter: this
      });
    };

    Computed.prototype.isEditable = function() {
      return false;
    };

    return Computed;

  })(Cruddy.Fields.Base);

  Cruddy.Columns = new Factory;

  Cruddy.Columns.Base = (function(_super) {
    __extends(Base, _super);

    function Base() {
      return Base.__super__.constructor.apply(this, arguments);
    }

    Base.prototype.initialize = function(attributes) {
      if (attributes.formatter != null) {
        this.formatter = Cruddy.formatters.create(attributes.formatter, attributes.formatterOptions);
      }
      return Base.__super__.initialize.apply(this, arguments);
    };

    Base.prototype.format = function(value) {
      if (this.formatter != null) {
        return this.formatter.format(value);
      } else {
        return _.escape(value);
      }
    };

    Base.prototype.getHeader = function() {
      return this.attributes.header;
    };

    Base.prototype.getClass = function() {
      return "col-" + this.id;
    };

    Base.prototype.canOrder = function() {
      return this.attributes.can_order;
    };

    return Base;

  })(Attribute);

  Cruddy.Columns.Proxy = (function(_super) {
    __extends(Proxy, _super);

    function Proxy() {
      return Proxy.__super__.constructor.apply(this, arguments);
    }

    Proxy.prototype.initialize = function(attributes) {
      var field, _ref1;
      field = (_ref1 = attributes.field) != null ? _ref1 : attributes.id;
      this.field = attributes.entity.fields.get(field);
      if (attributes.header === null) {
        this.set("header", this.field.get("label"));
      }
      return Proxy.__super__.initialize.apply(this, arguments);
    };

    Proxy.prototype.format = function(value) {
      if (this.formatter != null) {
        return this.formatter.format(value);
      } else {
        return this.field.format(value);
      }
    };

    Proxy.prototype.getClass = function() {
      return Proxy.__super__.getClass.apply(this, arguments) + " col-" + this.field.get("type");
    };

    return Proxy;

  })(Cruddy.Columns.Base);

  Cruddy.Columns.Computed = (function(_super) {
    __extends(Computed, _super);

    function Computed() {
      return Computed.__super__.constructor.apply(this, arguments);
    }

    Computed.prototype.getClass = function() {
      return Computed.__super__.getClass.apply(this, arguments) + " col-computed";
    };

    return Computed;

  })(Cruddy.Columns.Base);

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
      return Image.__super__.constructor.apply(this, arguments);
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
      return Plain.__super__.constructor.apply(this, arguments);
    }

    Plain.prototype.format = function(value) {
      return _.escape(value);
    };

    return Plain;

  })(BaseFormatter);

  Cruddy.Entity = {};

  Cruddy.Entity.Entity = (function(_super) {
    __extends(Entity, _super);

    function Entity() {
      return Entity.__super__.constructor.apply(this, arguments);
    }

    Entity.prototype.initialize = function(attributes, options) {
      this.fields = this.createCollection(Cruddy.Fields, attributes.fields);
      this.columns = this.createCollection(Cruddy.Columns, attributes.columns);
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
        order_by: this.get("order_by")
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
        var _i, _len, _ref1, _results;
        _ref1 = columns.models;
        _results = [];
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          col = _ref1[_i];
          if (col.get("filter_type") === "complex") {
            _results.push(col.createFilter());
          }
        }
        return _results;
      })();
      return new Backbone.Collection(filters);
    };

    Entity.prototype.createInstance = function(attributes, options) {
      if (attributes == null) {
        attributes = {};
      }
      if (options == null) {
        options = {};
      }
      attributes = _.extend({}, this.get("defaults"), attributes.attributes);
      options.entity = this;
      return new Cruddy.Entity.Instance(attributes, options);
    };

    Entity.prototype.getRelation = function(id) {
      var field;
      field = this.fields.get(id);
      if (!field) {
        console.error("The field " + id + " is not found.");
        return;
      }
      if (!field instanceof Cruddy.Fields.BaseRelation) {
        console.error("The field " + id + " is not a relation.");
        return;
      }
      return field;
    };

    Entity.prototype.search = function(options) {
      if (options == null) {
        options = {};
      }
      return new SearchDataSource({}, $.extend({
        url: this.url()
      }, options));
    };

    Entity.prototype.load = function(id) {
      var xhr;
      xhr = $.ajax({
        url: this.url(id),
        type: "GET",
        dataType: "json",
        cache: true,
        displayLoading: true
      });
      return xhr.then((function(_this) {
        return function(resp) {
          resp = resp.data;
          return _this.createInstance(resp);
        };
      })(this));
    };

    Entity.prototype.actionUpdate = function(id) {
      return this.load(id).then((function(_this) {
        return function(instance) {
          _this.set("instance", instance);
          return instance;
        };
      })(this));
    };

    Entity.prototype.actionCreate = function() {
      return this.set("instance", this.createInstance());
    };

    Entity.prototype.getCopyableAttributes = function(model, attributes) {
      var data, field, ref, _i, _j, _len, _len1, _ref1, _ref2;
      data = {};
      _ref1 = this.fields.models;
      for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
        field = _ref1[_i];
        if (!field.isUnique() && field.id in attributes && !_.contains(this.attributes.related, field.id)) {
          data[field.id] = attributes[field.id];
        }
      }
      _ref2 = this.attributes.related;
      for (_j = 0, _len1 = _ref2.length; _j < _len1; _j++) {
        ref = _ref2[_j];
        if (ref in attributes) {
          data[ref] = this.getRelation(ref).copy(model, attributes[ref]);
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

    Entity.prototype.getPluralTitle = function() {
      return this.attributes.title.plural;
    };

    Entity.prototype.getSingularTitle = function() {
      return this.attributes.title.singular;
    };

    Entity.prototype.getPermissions = function() {
      return this.attributes.permissions;
    };

    Entity.prototype.updatePermitted = function() {
      return this.attributes.permissions.update;
    };

    Entity.prototype.createPermitted = function() {
      return this.attributes.permissions.create;
    };

    Entity.prototype.deletePermitted = function() {
      return this.attributes.permissions["delete"];
    };

    Entity.prototype.viewPermitted = function() {
      return this.attributes.permissions.view;
    };

    Entity.prototype.isSoftDeleting = function() {
      return this.attributes.soft_deleting;
    };

    return Entity;

  })(Backbone.Model);

  Cruddy.Entity.Instance = (function(_super) {
    __extends(Instance, _super);

    function Instance(attributes, options) {
      this.entity = options.entity;
      this.related = {};
      Instance.__super__.constructor.apply(this, arguments);
    }

    Instance.prototype.initialize = function(attributes, options) {
      var event, _i, _len, _ref1;
      this.original = _.clone(attributes);
      this.on("error", this.processError, this);
      this.on("sync", this.handleSync, this);
      this.on("destroy", (function(_this) {
        return function() {
          if (_this.entity.get("soft_deleting")) {
            return _this.set("deleted_at", moment().unix());
          }
        };
      })(this));
      _ref1 = ["sync", "request"];
      for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
        event = _ref1[_i];
        this.on(event, this.triggerRelated(event), this);
      }
      return this;
    };

    Instance.prototype.handleSync = function() {
      this.original = _.clone(this.attributes);
      return this;
    };

    Instance.prototype.triggerRelated = function(event) {
      var slice;
      slice = Array.prototype.slice;
      return function(model) {
        var id, related, relation, _ref1;
        _ref1 = this.related;
        for (id in _ref1) {
          related = _ref1[id];
          relation = this.entity.getRelation(id);
          relation.triggerRelated.call(relation, event, related, slice.call(arguments, 1));
        }
        return this;
      };
    };

    Instance.prototype.processError = function(model, xhr) {
      var errors, id, _ref1, _ref2, _results;
      if (((_ref1 = xhr.responseJSON) != null ? _ref1.error : void 0) === "VALIDATION") {
        errors = xhr.responseJSON.data;
        this.trigger("invalid", this, errors);
        _ref2 = this.related;
        _results = [];
        for (id in _ref2) {
          model = _ref2[id];
          if (id in errors) {
            _results.push(this.entity.getRelation(id).processErrors(model, errors[id]));
          }
        }
        return _results;
      }
    };

    Instance.prototype.validate = function() {
      this.set("errors", {});
      return null;
    };

    Instance.prototype.link = function() {
      return this.entity.link(this.isNew() ? "create" : this.id);
    };

    Instance.prototype.url = function() {
      return this.entity.url(this.id);
    };

    Instance.prototype.set = function(key, val, options) {
      var attrs, id, is_copy, related, relation, relationAttrs, _i, _len, _ref1;
      if (typeof key === "object") {
        attrs = key;
        options = val;
        is_copy = options != null ? options.is_copy : void 0;
        _ref1 = this.entity.get("related");
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          id = _ref1[_i];
          if (!(id in attrs)) {
            continue;
          }
          relation = this.entity.getRelation(id);
          relationAttrs = attrs[id];
          if (is_copy) {
            related = this.related[id] = relationAttrs;
          } else if (id in this.related) {
            related = this.related[id];
            if (relationAttrs) {
              relation.applyValues(related, relationAttrs);
            }
          } else {
            related = this.related[id] = relation.createInstance(this, relationAttrs);
            related.parent = this;
          }
          attrs[id] = related;
        }
      }
      return Instance.__super__.set.apply(this, arguments);
    };

    Instance.prototype.sync = function(method, model, options) {
      var _ref1;
      if (method === "update" || method === "create") {
        options.data = new AdvFormData((_ref1 = options.attrs) != null ? _ref1 : this.attributes).original;
        options.contentType = false;
        options.processData = false;
      }
      return Instance.__super__.sync.apply(this, arguments);
    };

    Instance.prototype.parse = function(resp) {
      return resp.data.attributes;
    };

    Instance.prototype.copy = function() {
      var copy;
      copy = this.entity.createInstance();
      copy.set(this.getCopyableAttributes(copy), {
        silent: true,
        is_copy: true
      });
      return copy;
    };

    Instance.prototype.getCopyableAttributes = function(copy) {
      return this.entity.getCopyableAttributes(copy, this.attributes);
    };

    Instance.prototype.hasChangedSinceSync = function() {
      var key, value, _ref1;
      _ref1 = this.attributes;
      for (key in _ref1) {
        value = _ref1[key];
        if (key in this.related ? this.entity.getRelation(key).hasChangedSinceSync(value) : !_.isEqual(value, this.original[key])) {
          return true;
        }
      }
      return false;
    };

    Instance.prototype.isSaveable = function() {
      return (this.isNew() && this.entity.createPermitted()) || (!this.isNew() && this.entity.updatePermitted());
    };

    Instance.prototype.serialize = function() {
      return {
        attributes: this.attributes,
        id: this.id
      };
    };

    Instance.prototype.action = function() {
      if (this.isNew()) {
        return "create";
      } else {
        return "update";
      }
    };

    return Instance;

  })(Backbone.Model);

  Cruddy.Entity.Page = (function(_super) {
    __extends(Page, _super);

    Page.prototype.className = "page entity-page";

    Page.prototype.events = {
      "click .btn-create": "create",
      "click .btn-refresh": "refresh"
    };

    function Page(options) {
      this.className += " entity-page-" + options.model.id;
      Page.__super__.constructor.apply(this, arguments);
    }

    Page.prototype.initialize = function(options) {
      this.listenTo(this.model, "change:instance", this.toggleForm);
      return Page.__super__.initialize.apply(this, arguments);
    };

    Page.prototype.toggleForm = function(entity, instance) {
      if (this.form != null) {
        this.stopListening(this.form.model);
        this.form.remove();
      }
      if (instance != null) {
        this.listenTo(instance, "sync", function() {
          return Cruddy.router.navigate(instance.link());
        });
        this.form = new Cruddy.Entity.Form({
          model: instance
        });
        this.$el.append(this.form.render().$el);
        after_break((function(_this) {
          return function() {
            return _this.form.show();
          };
        })(this));
      }
      return this;
    };

    Page.prototype.create = function() {
      Cruddy.router.navigate(this.model.link("create"), {
        trigger: true
      });
      return this;
    };

    Page.prototype.refresh = function(e) {
      var btn;
      btn = $(e.currentTarget);
      btn.prop("disabled", true);
      this.dataSource.fetch().always(function() {
        return btn.prop("disabled", false);
      });
      return this;
    };

    Page.prototype.render = function() {
      var filters;
      this.dispose();
      this.$el.html(this.template());
      this.dataSource = this.model.createDataSource();
      this.dataSource.fetch();
      this.search = this.createSearchInput(this.dataSource);
      this.$component("search").append(this.search.render().el);
      if (!_.isEmpty(filters = this.dataSource.entity.get("filters"))) {
        this.filterList = this.createFilterList(this.dataSource.filter, filters);
        this.$component("filters").append(this.filterList.render().el);
      }
      this.dataGrid = this.createDataGrid(this.dataSource);
      this.pagination = this.createPagination(this.dataSource);
      this.$component("body").append(this.dataGrid.render().el).append(this.pagination.render().el);
      return this;
    };

    Page.prototype.createDataGrid = function(dataSource) {
      return new DataGrid({
        model: dataSource,
        entity: this.model
      });
    };

    Page.prototype.createPagination = function(dataSource) {
      return new Pagination({
        model: dataSource
      });
    };

    Page.prototype.createFilterList = function(model, filters) {
      return new FilterList({
        model: model,
        entity: this.model,
        filters: filters
      });
    };

    Page.prototype.createSearchInput = function(dataSource) {
      return new Cruddy.Inputs.Search({
        model: dataSource,
        key: "search"
      });
    };

    Page.prototype.template = function() {
      var html;
      return html = "<div class=\"content-header\">\n    <div class=\"column column-main\">\n        <h1 class=\"entity-title\">" + (this.model.getPluralTitle()) + "</h1>\n\n        <div class=\"entity-title-buttons\">\n            " + (this.buttonsTemplate()) + "\n        </div>\n    </div>\n\n    <div class=\"column column-extra\">\n        <div class=\"entity-search-box\" id=\"" + (this.componentId("search")) + "\"></div>\n    </div>\n</div>\n\n<div class=\"content-body\">\n    <div class=\"column column-main\" id=\"" + (this.componentId("body")) + "\"></div>\n    <div class=\"column column-extra\" id=\"" + (this.componentId("filters")) + "\"></div>\n</div>";
    };

    Page.prototype.buttonsTemplate = function() {
      var html;
      html = "<button type=\"button\" class=\"btn btn-default btn-refresh\" title=\"" + Cruddy.lang.refresh + "\">" + (b_icon("refresh")) + "</button>";
      if (this.model.createPermitted()) {
        html += " <button type=\"button\" class=\"btn btn-primary btn-create\" title=\"" + Cruddy.lang.add + "\">" + (b_icon("plus")) + "</button>";
      }
      return html;
    };

    Page.prototype.dispose = function() {
      var _ref1, _ref2, _ref3, _ref4, _ref5, _ref6;
      if ((_ref1 = this.form) != null) {
        _ref1.remove();
      }
      if ((_ref2 = this.filterList) != null) {
        _ref2.remove();
      }
      if ((_ref3 = this.dataGrid) != null) {
        _ref3.remove();
      }
      if ((_ref4 = this.pagination) != null) {
        _ref4.remove();
      }
      if ((_ref5 = this.search) != null) {
        _ref5.remove();
      }
      if ((_ref6 = this.dataSource) != null) {
        _ref6.stopListening();
      }
      return this;
    };

    Page.prototype.remove = function() {
      this.dispose();
      return Page.__super__.remove.apply(this, arguments);
    };

    return Page;

  })(Cruddy.View);

  Cruddy.Entity.Form = (function(_super) {
    __extends(Form, _super);

    Form.prototype.className = "entity-form";

    Form.prototype.events = {
      "click .btn-save": "save",
      "click .btn-close": "close",
      "click .btn-destroy": "destroy",
      "click .btn-copy": "copy"
    };

    function Form(options) {
      this.className += " " + this.className + "-" + options.model.entity.id;
      Form.__super__.constructor.apply(this, arguments);
    }

    Form.prototype.initialize = function(options) {
      var key, model, _ref1, _ref2;
      this.inner = (_ref1 = options.inner) != null ? _ref1 : false;
      this.listenTo(this.model, "destroy", this.handleDestroy);
      this.listenTo(this.model, "invalid", this.displayInvalid);
      this.listenTo(this.model, "change", this.handleChange);
      _ref2 = this.model.related;
      for (key in _ref2) {
        model = _ref2[key];
        this.listenTo(model, "change", this.handleChange);
      }
      this.hotkeys = $(document).on("keydown." + this.cid, "body", $.proxy(this, "hotkeys"));
      return this;
    };

    Form.prototype.hotkeys = function(e) {
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

    Form.prototype.handleChange = function() {
      return this;
    };

    Form.prototype.displayAlert = function(message, type, timeout) {
      if (this.alert != null) {
        this.alert.remove();
      }
      this.alert = new Alert({
        message: message,
        className: "flash",
        type: type,
        timeout: timeout
      });
      this.footer.prepend(this.alert.render().el);
      return this;
    };

    Form.prototype.displaySuccess = function() {
      return this.displayAlert(Cruddy.lang.success, "success", 3000);
    };

    Form.prototype.displayInvalid = function() {
      return this.displayAlert(Cruddy.lang.invalid, "warning", 5000);
    };

    Form.prototype.displayError = function(xhr) {
      var _ref1;
      if (((_ref1 = xhr.responseJSON) != null ? _ref1.error : void 0) !== "VALIDATION") {
        return this.displayAlert(Cruddy.lang.failure, "danger", 5000);
      }
    };

    Form.prototype.handleDestroy = function() {
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

    Form.prototype.show = function() {
      this.$el.toggleClass("opened", true);
      this.tabs[0].focus();
      return this;
    };

    Form.prototype.save = function() {
      if (this.request != null) {
        return;
      }
      this.request = this.model.save(null, {
        displayLoading: true,
        xhr: (function(_this) {
          return function() {
            var xhr;
            xhr = $.ajaxSettings.xhr();
            if (xhr.upload) {
              xhr.upload.addEventListener('progress', $.proxy(_this, "progressCallback"));
            }
            return xhr;
          };
        })(this)
      });
      this.request.done($.proxy(this, "displaySuccess")).fail($.proxy(this, "displayError"));
      this.request.always((function(_this) {
        return function() {
          _this.request = null;
          _this.progressBar.parent().hide();
          return _this.update();
        };
      })(this));
      this.update();
      return this;
    };

    Form.prototype.progressCallback = function(e) {
      var width;
      if (e.lengthComputable) {
        width = (e.loaded * 100) / e.total;
        this.progressBar.width(width + '%').parent().show();
      }
      return this;
    };

    Form.prototype.close = function() {
      var confirmed;
      if (this.request) {
        confirmed = confirm(Cruddy.lang.confirm_abort);
      } else {
        confirmed = this.model.hasChangedSinceSync() ? confirm(Cruddy.lang.confirm_discard) : true;
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

    Form.prototype.destroy = function() {
      var confirmed, softDeleting;
      if (this.request || this.model.isNew()) {
        return;
      }
      softDeleting = this.model.entity.get("soft_deleting");
      confirmed = !softDeleting ? confirm(Cruddy.lang.confirm_delete) : true;
      if (confirmed) {
        this.request = this.softDeleting && this.model.get("deleted_at") ? this.model.restore : this.model.destroy({
          wait: true
        });
        this.request.always((function(_this) {
          return function() {
            return _this.request = null;
          };
        })(this));
      }
      return this;
    };

    Form.prototype.copy = function() {
      var copy;
      this.model.entity.set("instance", copy = this.model.copy());
      Cruddy.router.navigate(copy.link());
      return this;
    };

    Form.prototype.render = function() {
      this.dispose();
      this.$el.html(this.template());
      this.nav = this.$(".navbar-nav");
      this.footer = this.$("footer");
      this.submit = this.$(".btn-save");
      this.destroy = this.$(".btn-destroy");
      this.copy = this.$(".btn-copy");
      this.progressBar = this.$(".form-save-progress");
      this.tabs = [];
      this.renderTab(this.model, true);
      return this.update();
    };

    Form.prototype.renderTab = function(model, active) {
      var fieldList, id;
      this.tabs.push(fieldList = new FieldList({
        model: model
      }));
      id = "tab-" + model.entity.id;
      fieldList.render().$el.insertBefore(this.footer).wrap($("<div></div>", {
        id: id,
        "class": "wrap" + (active ? " active" : "")
      }));
      this.nav.append(this.navTemplate(model.entity.get("title").singular, id, active));
      return this;
    };

    Form.prototype.update = function() {
      var permit;
      permit = this.model.entity.getPermissions();
      this.$el.toggleClass("loading", this.request != null);
      this.submit.text(this.model.isNew() ? Cruddy.lang.create : Cruddy.lang.save);
      this.submit.attr("disabled", this.request != null);
      this.submit.toggle(this.model.isNew() ? permit.create : permit.update);
      this.destroy.attr("disabled", this.request != null);
      this.destroy.html(this.model.entity.isSoftDeleting() && this.model.get("deleted_at") ? "Восстановить" : "<span class='glyphicon glyphicon-trash' title='" + Cruddy.lang["delete"] + "'></span>");
      this.destroy.toggle(!this.model.isNew() && permit["delete"]);
      this.copy.toggle(!this.model.isNew() && permit.create);
      return this;
    };

    Form.prototype.template = function() {
      return "<div class=\"navbar navbar-default navbar-static-top\" role=\"navigation\">\n    <div class=\"container-fluid\">\n        <button type=\"button\" class=\"btn btn-link btn-destroy navbar-btn pull-right\" type=\"button\"></button>\n        \n        <button type=\"button\" tabindex=\"-1\" class=\"btn btn-link btn-copy navbar-btn pull-right\" title=\"" + Cruddy.lang.copy + "\">\n            <span class=\"glyphicon glyphicon-book\"></span>\n        </button>\n        \n        <ul class=\"nav navbar-nav\"></ul>\n    </div>\n</div>\n\n<footer>\n    <button type=\"button\" class=\"btn btn-default btn-close\" type=\"button\">" + Cruddy.lang.close + "</button>\n    <button type=\"button\" class=\"btn btn-primary btn-save\" type=\"button\" disabled></button>\n\n    <div class=\"progress\"><div class=\"progress-bar form-save-progress\"></div></div>\n</footer>";
    };

    Form.prototype.navTemplate = function(label, target, active) {
      active = active ? " class=\"active\"" : "";
      return "<li" + active + "><a href=\"#" + target + "\" data-toggle=\"tab\">" + label + "</a></li>";
    };

    Form.prototype.remove = function() {
      this.trigger("remove", this);
      this.$el.one(TRANSITIONEND, (function(_this) {
        return function() {
          _this.dispose();
          $(document).off("." + _this.cid);
          _this.trigger("removed", _this);
          return Form.__super__.remove.apply(_this, arguments);
        };
      })(this)).removeClass("opened");
      return this;
    };

    Form.prototype.dispose = function() {
      var fieldList, _i, _len, _ref1;
      if (this.tabs != null) {
        _ref1 = this.tabs;
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          fieldList = _ref1[_i];
          fieldList.remove();
        }
      }
      return this;
    };

    return Form;

  })(Backbone.View);

  App = (function(_super) {
    __extends(App, _super);

    function App() {
      return App.__super__.constructor.apply(this, arguments);
    }

    App.prototype.initialize = function() {
      var entity, _i, _len, _ref1;
      this.container = $("body");
      this.mainContent = $("#content");
      this.loadingRequests = 0;
      this.entities = {};
      this.entitiesDfd = {};
      _ref1 = Cruddy.entities;
      for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
        entity = _ref1[_i];
        this.entities[entity.id] = new Cruddy.Entity.Entity(entity);
      }
      this.on("change:entity", this.displayEntity, this);
      return this;
    };

    App.prototype.displayEntity = function(model, entity) {
      this.dispose();
      this.mainContent.hide();
      if (entity) {
        return this.container.append((this.page = new Cruddy.Entity.Page({
          model: entity
        })).render().el);
      }
    };

    App.prototype.displayError = function(error) {
      this.dispose();
      this.mainContent.html("<p class='alert alert-danger'>" + error + "</p>").show();
      return this;
    };

    App.prototype.startLoading = function() {
      if (this.loadingRequests++ === 0) {
        this.loading = setTimeout(((function(_this) {
          return function() {
            $(document.body).addClass("loading");
            return _this.loading = false;
          };
        })(this)), 1000);
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

    App.prototype.entity = function(id) {
      if (!id in this.entities) {
        console.error("Unknown entity " + id);
      }
      return this.entities[id];
    };

    App.prototype.dispose = function() {
      var _ref1;
      if ((_ref1 = this.page) != null) {
        _ref1.remove();
      }
      return this;
    };

    return App;

  })(Backbone.Model);

  Cruddy.app = new App;

  Router = (function(_super) {
    __extends(Router, _super);

    function Router() {
      return Router.__super__.constructor.apply(this, arguments);
    }

    Router.prototype.initialize = function() {
      var entities, hashStripper, history, root;
      entities = (_.map(Cruddy.entities, function(entity) {
        return entity.id;
      })).join("|");
      this.addRoute("index", entities);
      this.addRoute("update", entities, "([^/]+)");
      this.addRoute("create", entities, "create");
      root = Cruddy.root + "/" + Cruddy.uri + "/";
      history = Backbone.history;
      hashStripper = /#.*$/;
      $(document.body).on("click", "a", function(e) {
        var fragment, loaded, oldFragment;
        fragment = e.currentTarget.href.replace(hashStripper, "");
        oldFragment = history.fragment;
        if (fragment.indexOf(root) === 0 && (fragment = fragment.slice(root.length)) && fragment !== oldFragment) {
          loaded = history.loadUrl(fragment);
          history.fragment = oldFragment;
          if (loaded) {
            e.preventDefault();
            history.navigate(fragment);
          }
        }
        return e;
      });
      return this;
    };

    Router.prototype.addRoute = function(name, entities, appendage) {
      var route;
      if (appendage == null) {
        appendage = null;
      }
      route = "^(" + entities + ")";
      if (appendage) {
        route += "/" + appendage;
      }
      route += "$";
      this.route(new RegExp(route), name);
      return this;
    };

    Router.prototype.resolveEntity = function(id) {
      var entity;
      entity = Cruddy.app.entity(id);
      if (entity.viewPermitted()) {
        entity.set("instance", null);
        Cruddy.app.set("entity", entity);
        return entity;
      } else {
        Cruddy.app.displayError(Cruddy.lang.entity_forbidden);
        return null;
      }
    };

    Router.prototype.index = function(entity) {
      return this.resolveEntity(entity);
    };

    Router.prototype.create = function(entity) {
      console.log('create');
      entity = this.resolveEntity(entity);
      if (entity) {
        entity.actionCreate();
      }
      return entity;
    };

    Router.prototype.update = function(entity, id) {
      entity = this.resolveEntity(entity);
      if (entity) {
        entity.actionUpdate(id);
      }
      return entity;
    };

    return Router;

  })(Backbone.Router);

  Cruddy.router = new Router;

  Backbone.history.start({
    root: Cruddy.uri,
    pushState: true,
    hashChange: false
  });

}).call(this);

//# sourceMappingURL=app.js.map
