(function() {
  var AdvFormData, Alert, App, Cruddy, DataGrid, DataSource, Factory, FieldList, FilterList, NOT_AVAILABLE, Pagination, Router, SearchDataSource, TITLE_SEPARATOR, TRANSITIONEND, VALIDATION_FAILED_CODE, add_query_to_url, after_break, b_btn, b_icon, entity_url, get, humanize, render_divider, render_presentation_action, render_presentation_actions, thumb, value_if,
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  Cruddy = window.Cruddy || {};

  TRANSITIONEND = "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd";

  NOT_AVAILABLE = "&mdash;";

  TITLE_SEPARATOR = " / ";

  VALIDATION_FAILED_CODE = 422;

  moment.lang([Cruddy.locale, "en"]);

  Backbone.emulateHTTP = true;

  Backbone.emulateJSON = true;

  $(document).ajaxSend(function(e, xhr, options) {
    if (!Cruddy.app) {
      options.displayLoading = false;
    }
    if (options.displayLoading) {
      Cruddy.app.startLoading();
    }
    xhr.setRequestHeader("X-CSRF-TOKEN", Cruddy.token);
  }).ajaxComplete(function(e, xhr, options) {
    if (options.displayLoading) {
      Cruddy.app.doneLoading();
    }
  });

  $(document.body).on("click", "[data-trigger=fancybox]", function(e) {
    if ($.fancybox.open(e.currentTarget) !== false) {
      return false;
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
    url = Cruddy.baseUrl + "/" + id;
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
    url = "" + Cruddy.thumbUrl + "?src=" + (encodeURIComponent(src));
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

  render_divider = function() {
    return "<li class=\"divider\"></li>";
  };

  render_presentation_action = function(url, title) {
    return "<li><a href=\"" + url + "\" target=\"_blank\">" + title + "</a></li>";
  };

  render_presentation_actions = function(items) {
    var href, html, title;
    html = "";
    for (title in items) {
      href = items[title];
      html += render_presentation_action(href, title);
    }
    return html;
  };

  value_if = function(bool, value) {
    if (bool) {
      return value;
    } else {
      return "";
    }
  };

  get = function(path, obj) {
    var key, _i, _len, _ref;
    if (obj == null) {
      obj = window;
    }
    if (_.isEmpty(path)) {
      return obj;
    }
    _ref = path.split(".");
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      key = _ref[_i];
      if (!(key in obj)) {
        return;
      }
      obj = obj[key];
    }
    return obj;
  };

  add_query_to_url = function(url, query) {
    var painConverter;
    if (_.isObject(query)) {
      painConverter = function(value, param) {
        return "" + (encodeURIComponent(param)) + "=" + (encodeURIComponent(value));
      };
      query = _.map(query, painConverter).join("&");
    }
    if (query && query.length) {
      return "" + url + "?" + query;
    } else {
      return url;
    }
  };

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

  $.extend(Cruddy, {
    Fields: {},
    Columns: {},
    Filters: {},
    formatters: new Factory,
    getHistoryRoot: function() {
      return this.baseUrl.substr(this.root.length);
    },
    getApp: function() {
      if (!this.app) {
        this.app = (new App).init();
      }
      return this.app;
    },
    ready: function(callback) {
      return this.getApp().ready(callback);
    }
  });

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

    AdvFormData.prototype.append = function(value, name) {
      if (value === void 0) {
        return;
      }
      if (value instanceof File || value instanceof Blob) {
        return this.original.append(name, value);
      }
      if (_.isObject(value) || _.isArray(value)) {
        if (_.isEmpty(value)) {
          return this.original.append(name, "");
        }
        _.each(value, (function(_this) {
          return function(value, key) {
            return _this.append(value, _this.key(name, key));
          };
        })(this));
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

  Cruddy.Attribute = (function(_super) {
    __extends(Attribute, _super);

    function Attribute() {
      return Attribute.__super__.constructor.apply(this, arguments);
    }

    Attribute.prototype.initialize = function(options) {
      this.entity = options.entity;
      return this;
    };

    Attribute.prototype.getType = function() {
      return "attribute";
    };

    Attribute.prototype.getHelp = function() {
      return this.attributes.help;
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
      page: 1,
      per_page: null,
      keywords: "",
      order_by: null,
      order_dir: "asc"
    };

    DataSource.prototype.initialize = function(attributes, options) {
      var entity;
      this.entity = entity = options.entity;
      this.defaults = _.clone(this.attributes);
      this.options = {
        url: entity.url(),
        dataType: "json",
        type: "get",
        displayLoading: true,
        success: (function(_this) {
          return function(resp) {
            _this.resp = resp;
            return _this.trigger("data", _this, resp.items);
          };
        })(this),
        error: (function(_this) {
          return function(xhr) {
            return _this.trigger("error", _this, xhr);
          };
        })(this)
      };
      this.on("change:keywords", (function(_this) {
        return function() {
          return _this.set("page", 1);
        };
      })(this));
      return this.on("change", (function(_this) {
        return function(model, options) {
          if (!options.noFetch) {
            return _this.fetch();
          }
        };
      })(this));
    };

    DataSource.prototype.hasData = function() {
      return this.resp != null;
    };

    DataSource.prototype.isEmpty = function() {
      return !this.hasData() || _.isEmpty(this.resp.items);
    };

    DataSource.prototype.hasMore = function() {
      return this.hasData() && this.resp.page < this.resp.lastPage;
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
      this.options.data = this._getRequestData();
      this.request = $.ajax(this.options);
      this.request.always((function(_this) {
        return function() {
          return _this.request = null;
        };
      })(this));
      this.trigger("request", this, this.request);
      return this.request;
    };

    DataSource.prototype.next = function() {
      if (this.inProgress() || this.isFull()) {
        return;
      }
      this.set({
        page: this.get("page") + 1
      });
      return this;
    };

    DataSource.prototype.prev = function() {
      var page;
      if (this.inProgress() || (page = this.get("page")) <= 1) {
        return;
      }
      this.set({
        page: page - 1
      });
      return this;
    };

    DataSource.prototype._getRequestData = function() {
      var data, key, value, _ref;
      data = {};
      _ref = this.attributes;
      for (key in _ref) {
        value = _ref[key];
        if (_.isNumber(value) || !_.isEmpty(value)) {
          data[key] = value;
        }
      }
      return data;
    };

    DataSource.prototype.getData = function() {
      var _ref;
      return (_ref = this.resp) != null ? _ref.items : void 0;
    };

    DataSource.prototype.getTotal = function() {
      var _ref;
      return (_ref = this.resp) != null ? _ref.total : void 0;
    };

    DataSource.prototype.getFrom = function() {
      var _ref;
      return (_ref = this.resp) != null ? _ref.from : void 0;
    };

    DataSource.prototype.getTo = function() {
      var _ref;
      return (_ref = this.resp) != null ? _ref.to : void 0;
    };

    DataSource.prototype.getLastPage = function() {
      var _ref;
      return (_ref = this.resp) != null ? _ref.lastPage : void 0;
    };

    return DataSource;

  })(Backbone.Model);

  SearchDataSource = (function(_super) {
    __extends(SearchDataSource, _super);

    function SearchDataSource() {
      return SearchDataSource.__super__.constructor.apply(this, arguments);
    }

    SearchDataSource.prototype.defaults = {
      keywords: null,
      constraint: null
    };

    SearchDataSource.prototype.initialize = function(attributes, options) {
      this.resetData = false;
      this.data = [];
      this.page = null;
      this.more = true;
      this.options = {
        url: options.url,
        type: "get",
        dataType: "json",
        data: {
          simple: 1
        },
        success: (function(_this) {
          return function(resp) {
            var item, _i, _len, _ref;
            if (_this.resetData) {
              _this.data = [];
              _this.resetData = false;
            }
            _ref = resp.items;
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              item = _ref[_i];
              _this.data.push(item);
            }
            _this.page = resp.page;
            _this.more = resp.page < resp.lastPage;
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
      this.on("change", this.refresh, this);
      return this;
    };

    SearchDataSource.prototype.refresh = function() {
      this.resetData = true;
      return this.fetchPage(1);
    };

    SearchDataSource.prototype.fetchPage = function(page) {
      if (this.request != null) {
        this.request.abort();
      }
      $.extend(this.options.data, this.attributes, {
        page: page
      });
      this.trigger("request", this, this.request = $.ajax(this.options));
      return this.request;
    };

    SearchDataSource.prototype.next = function() {
      this.fetchPage(this.page != null ? this.page + 1 : this.more ? 1 : void 0);
      return this;
    };

    SearchDataSource.prototype.inProgress = function() {
      return this.request != null;
    };

    SearchDataSource.prototype.isEmpty = function() {
      return this.page === null && !this.request;
    };

    SearchDataSource.prototype.getById = function(id) {
      if (!id.length) {
        id = id.toString();
      }
      return _.find(this.data, function(item) {
        return item.id.toString() === id;
      });
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
      var router;
      router = Cruddy.router;
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
      if (n > 0 && n <= this.model.getLastPage()) {
        this.model.set("page", n);
      }
      return this;
    };

    Pagination.prototype.previous = function() {
      return this.page(this.model.get("page") - 1);
    };

    Pagination.prototype.next = function() {
      return this.page(this.model.get("page") + 1);
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
      if (this.model.hasData()) {
        last = this.model.getLastPage();
        this.$el.toggle((last != null) && last > 1);
        if (last > 1) {
          this.$el.html(this.template(this.model.get("page"), last));
        }
      }
      return this;
    };

    Pagination.prototype.template = function(current, last) {
      var html;
      html = "";
      html += this.renderLink(current - 1, "&larr; " + Cruddy.lang.prev, "previous" + (current > 1 ? "" : " disabled"));
      if (this.model.getTotal() != null) {
        html += this.renderStats();
      }
      html += this.renderLink(current + 1, "" + Cruddy.lang.next + " &rarr;", "next" + (current < last ? "" : " disabled"));
      return html;
    };

    Pagination.prototype.renderStats = function() {
      return "<li class=\"stats\"><span>" + (this.model.getFrom()) + " - " + (this.model.getTo()) + " / " + (this.model.getTotal()) + "</span></li>";
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

    DataGrid.prototype.className = "table table-hover dg";

    DataGrid.prototype.events = {
      "click .col__sortable": "setOrder",
      "click [data-action]": "executeAction"
    };

    function DataGrid(options) {
      this.className += " dg-" + options.entity.id;
      DataGrid.__super__.constructor.apply(this, arguments);
    }

    DataGrid.prototype.initialize = function(options) {
      this.entity = options.entity;
      this.columns = this.entity.columns.models.filter(function(col) {
        return col.isVisible();
      });
      this.addActionColumns(this.columns);
      this.listenTo(this.model, "data", (function(_this) {
        return function() {
          if (_this.$items != null) {
            return _this.renderBody();
          }
        };
      })(this));
      this.listenTo(this.model, "change:order_by change:order_dir", this.markOrderColumn);
      return this.listenTo(this.entity, "change:instance", this.markActiveItem);
    };

    DataGrid.prototype.addActionColumns = function(columns) {
      this.columns.unshift(new Cruddy.Columns.ViewButton({
        entity: this.entity
      }));
      if (this.entity.deletePermitted()) {
        this.columns.push(new Cruddy.Columns.DeleteButton({
          entity: this.entity
        }));
      }
      return this;
    };

    DataGrid.prototype.markOrderColumn = function() {
      var orderBy, orderDir;
      orderBy = this.model.get("order_by");
      orderDir = this.model.get("order_dir") || "asc";
      if ((this.orderBy != null) && orderBy !== this.orderBy) {
        this.$colCell(this.orderBy).removeClass("asc desc");
      }
      this.$colCell(this.orderBy = orderBy).removeClass("asc desc").addClass(orderDir);
      return this;
    };

    DataGrid.prototype.markActiveItem = function() {
      var model;
      if (model = this.entity.previous("instance")) {
        this.$itemRow(model).removeClass("active");
      }
      if (model = this.entity.get("instance")) {
        this.$itemRow(model).addClass("active");
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

    DataGrid.prototype.render = function() {
      this.$el.html(this.template());
      this.$header = this.$component("header");
      this.$items = this.$component("items");
      this.renderHead();
      this.renderBody();
      return this;
    };

    DataGrid.prototype.renderHead = function() {
      var column, html, _i, _len, _ref;
      html = "";
      _ref = this.columns;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        column = _ref[_i];
        html += this.renderHeadCell(column);
      }
      this.$header.html(html);
      return this.markOrderColumn();
    };

    DataGrid.prototype.renderHeadCell = function(column) {
      return "<th class=\"" + (column.getClass()) + "\" id=\"" + (this.colCellId(column)) + "\" data-id=\"" + column.id + "\">\n    " + (this.renderHeadCellValue(column)) + "\n</th>";
    };

    DataGrid.prototype.renderHeadCellValue = function(col) {
      var help, title;
      title = _.escape(col.getHeader());
      if (help = _.escape(col.getHelp())) {
        title = "<span class=\"glyphicon glyphicon-question-sign\" title=\"" + help + "\"></span> " + title;
      }
      return title;
    };

    DataGrid.prototype.renderBody = function() {
      var html, item, _i, _len, _ref;
      if (this.model.isEmpty()) {
        this.$items.html(this.emptyTemplate());
        return this;
      }
      html = "";
      _ref = this.model.getData();
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        item = _ref[_i];
        html += this.renderRow(item);
      }
      this.$items.html(html);
      return this.markActiveItem();
    };

    DataGrid.prototype.renderRow = function(item) {
      var columns, html, _i, _len, _ref;
      html = "<tr class=\"item " + (this.itemStates(item)) + "\" id=\"" + (this.itemRowId(item)) + "\" data-id=\"" + item.meta.id + "\">";
      _ref = this.columns;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        columns = _ref[_i];
        html += this.renderCell(columns, item);
      }
      return html += "</tr>";
    };

    DataGrid.prototype.itemStates = function(item) {
      var instance, states;
      states = item.attributes._states ? item.attributes._states : "";
      if (((instance = this.entity.get("instance")) != null) && item.meta.id === instance.id) {
        states += " active";
      }
      return states;
    };

    DataGrid.prototype.renderCell = function(column, item) {
      return "<td class=\"" + (column.getClass()) + "\">\n    " + (column.render(item)) + "\n</td>";
    };

    DataGrid.prototype.executeAction = function(e) {
      var $el, action;
      $el = $(e.currentTarget);
      action = $el.data("action");
      if (action && (action = this[action])) {
        e.preventDefault();
        action.call(this, $el.closest(".item").data("id"), $el);
      }
    };

    DataGrid.prototype.deleteItem = function(id, $el) {
      var $row;
      if (!confirm(Cruddy.lang.confirm_delete)) {
        return;
      }
      $row = $el.closest(".item");
      $el.attr("disabled", true);
      this.entity.destroy(id, {
        displayLoading: true,
        success: (function(_this) {
          return function() {
            $row.fadeOut();
            return _this.model.fetch();
          };
        })(this),
        fail: function() {
          return $el.attr("disabled", false);
        }
      });
    };

    DataGrid.prototype.template = function() {
      return "<thead><tr id=\"" + (this.componentId("header")) + "\"></tr></thead>\n<tbody class=\"items\" id=\"" + (this.componentId("items")) + "\"></tbody>";
    };

    DataGrid.prototype.emptyTemplate = function() {
      return "<tr class=\"empty\">\n    <td colspan=\"" + this.columns.length + "\">\n        " + Cruddy.lang.no_results + "\n    </td>\n</tr>";
    };

    DataGrid.prototype.colCellId = function(col) {
      return this.componentId("col-" + col.id);
    };

    DataGrid.prototype.$colCell = function(id) {
      return this.$component("col-" + id);
    };

    DataGrid.prototype.itemRowId = function(item) {
      return this.componentId("item-" + item.meta.id);
    };

    DataGrid.prototype.$itemRow = function(item) {
      return this.$component("item-" + item.meta.id);
    };

    return DataGrid;

  })(Cruddy.View);

  FilterList = (function(_super) {
    __extends(FilterList, _super);

    function FilterList() {
      return FilterList.__super__.constructor.apply(this, arguments);
    }

    FilterList.prototype.className = "filter-list";

    FilterList.prototype.tagName = "fieldset";

    FilterList.prototype.events = {
      "click .btn-apply": "apply",
      "click .btn-reset": "reset"
    };

    FilterList.prototype.initialize = function(options) {
      this.entity = options.entity;
      this.availableFilters = options.filters;
      this.filterModel = new Backbone.Model;
      this.filterModel.entity = this.entity;
      this.listenTo(this.model, "request", (function(_this) {
        return function() {
          return _this._toggleButtons(true);
        };
      })(this));
      this.listenTo(this.model, "data", (function(_this) {
        return function() {
          return _this._toggleButtons(false);
        };
      })(this));
      this.listenTo(this.model, "change", (function(_this) {
        return function() {
          if (!_this._applying) {
            return _this._setDataFromDataSource();
          }
        };
      })(this));
      this._setDataFromDataSource();
      return this;
    };

    FilterList.prototype._toggleButtons = function(disabled) {
      this.$buttons.prop("disabled", disabled);
    };

    FilterList.prototype.apply = function() {
      this._applying = true;
      this.model.set($.extend(this._prepareData(), {
        page: 1
      }));
      this._applying = false;
      return this;
    };

    FilterList.prototype._prepareData = function() {
      var data, filter, key, value, _ref;
      data = {};
      _ref = this.filterModel.attributes;
      for (key in _ref) {
        value = _ref[key];
        if (filter = this.availableFilters.get(key)) {
          data[filter.getDataKey()] = filter.prepareData(value);
        }
      }
      return data;
    };

    FilterList.prototype._setDataFromDataSource = function() {
      var data, filter, _i, _len, _ref;
      data = {};
      _ref = this.availableFilters.models;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        filter = _ref[_i];
        data[filter.id] = filter.parseData(this.model.get(filter.getDataKey()));
      }
      this.filterModel.set(data);
      return this;
    };

    FilterList.prototype.reset = function() {
      var input, _i, _len, _ref;
      _ref = this.filters;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        input = _ref[_i];
        input.empty();
      }
      return this.apply();
    };

    FilterList.prototype.render = function() {
      var filter, input, _i, _len, _ref;
      this.dispose();
      this.$el.html(this.template());
      this.items = this.$(".filter-list-container");
      _ref = this.availableFilters.models;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        filter = _ref[_i];
        if (!(input = filter.createFilterInput(this.filterModel))) {
          continue;
        }
        this.filters.push(input);
        this.items.append(input.render().el);
        input.$el.wrap("<div class=\"form-group " + (filter.getClass()) + "\"></div>").parent().before("<label>" + (filter.getLabel()) + "</label>");
      }
      this.$buttons = this.$el.find(".fl-btn");
      return this;
    };

    FilterList.prototype.template = function() {
      return "<div class=\"filter-list-container\"></div>\n<button type=\"button\" class=\"btn fl-btn btn-primary btn-apply\">" + Cruddy.lang.filter_apply + "</button>\n<button type=\"button\" class=\"btn fl-btn btn-default btn-reset\">" + Cruddy.lang.filter_reset + "</button>";
    };

    FilterList.prototype.dispose = function() {
      var filter, _i, _len, _ref;
      if (this.filters != null) {
        _ref = this.filters;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          filter = _ref[_i];
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

  Alert = (function(_super) {
    __extends(Alert, _super);

    function Alert() {
      return Alert.__super__.constructor.apply(this, arguments);
    }

    Alert.prototype.tagName = "span";

    Alert.prototype.className = "alert";

    Alert.prototype.initialize = function(options) {
      var _ref;
      this.$el.addClass((_ref = this.className + "-" + options.type) != null ? _ref : "info");
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

  Cruddy.UploadQueue = (function() {
    function UploadQueue(storage) {
      this._storage = storage;
      this._q = [];
      this._xhr = null;
      this._file = null;
      this._working = false;
      this._uploaded = 0;
      this._total = 0;
    }

    UploadQueue.prototype.push = function(files, path) {
      var file, _i, _len;
      if (files instanceof File) {
        files = [files];
      }
      for (_i = 0, _len = files.length; _i < _len; _i++) {
        file = files[_i];
        this._q.push({
          file: file,
          path: path,
          uploaded: 0,
          total: file.size
        });
        if (this._working) {
          this._uploadStats.total += file.size;
          this._notifyProgress();
        }
      }
      return this;
    };

    UploadQueue.prototype.start = function() {
      if (this._working || !this._q.length) {
        return this;
      }
      this._working = true;
      this._uploaded = 0;
      this._total = _.reduce(this._q, ((function(_this) {
        return function(sum, file) {
          return sum + file.total;
        };
      })(this)), 0);
      this._notifyProgress();
      this.trigger("started");
      return this._next();
    };

    UploadQueue.prototype.stop = function() {
      if (!this._working) {
        return this;
      }
      this._working = false;
      this._xhr && this._xhr.abort();
      this.trigger("stopped");
      return this;
    };

    UploadQueue.prototype.isEmpty = function() {
      return !this._q.length;
    };

    UploadQueue.prototype.isWorking = function() {
      return this._working;
    };

    UploadQueue.prototype.isCompleted = function() {
      return !this._q.length;
    };

    UploadQueue.prototype._next = function() {
      var file, uploaded;
      this._xhr = null;
      this._file = null;
      if (this.isEmpty()) {
        return this.stop();
      }
      uploaded = this._uploaded;
      this._file = file = this._q.shift();
      this.trigger("filestarted", file);
      this._xhr = this._storage.upload(file.file, file.path, (function(_this) {
        return function(loaded, total) {
          file.uploaded = loaded;
          if (file.total !== total) {
            _this._total += total - file.total;
            file.total = total;
          }
          _this.trigger("fileprogress", file);
          _this._uploaded = uploaded + loaded;
          return _this._notifyProgress();
        };
      })(this)).always((function(_this) {
        return function() {
          uploaded += file.total;
          if (_this._uploaded !== uploaded) {
            _this._uploaded = uploaded;
            _this._notifyProgress();
          }
          return _this._next();
        };
      })(this)).done((function(_this) {
        return function(resp) {
          return _this.trigger("filecompleted", resp, file);
        };
      })(this)).fail((function(_this) {
        return function(xhr, error) {
          file.uploaded = file.total;
          if (xhr.status === 422) {
            _this.trigger("fileinvalid", xhr.responseJSON.message, xhr.responseJSON.code, file);
            return;
          }
          if (error === "abort") {
            _this.trigger("fileaborted", file);
            return;
          }
          _this.trigger("fileerror", file, xhr, error);
        };
      })(this));
      return this;
    };

    UploadQueue.prototype._notifyProgress = function() {
      var stats;
      stats = {
        uploaded: this._uploaded,
        total: this._total
      };
      if (stats.total > 0) {
        this.trigger("progress", stats);
      }
      return this;
    };

    return UploadQueue;

  })();

  _.extend(Cruddy.UploadQueue.prototype, Backbone.Events);

  Cruddy.FileStorage = (function() {
    function FileStorage(id) {
      this.id = id;
      this._queue = new Cruddy.UploadQueue(this);
    }

    FileStorage.prototype.url = function(path, query) {
      var url;
      url = "" + Cruddy.baseUrl + "/_files/" + this.id + "/" + (path || "");
      return add_query_to_url(url, query);
    };

    FileStorage.prototype.upload = function(file, path, progressCallback) {
      var data;
      if (_.isFunction(path)) {
        progressCallback = path;
        path = "";
      }
      data = new FormData;
      data.append("file", file);
      return $.ajax({
        data: data,
        url: this.url(path),
        type: "POST",
        dataType: "json",
        processData: false,
        contentType: false,
        xhr: function() {
          var xhr;
          xhr = $.ajaxSettings.xhr();
          xhr.upload && xhr.upload.addEventListener("progress", function(e) {
            if (!(e.lengthComputable || !progressCallback)) {
              return;
            }
            return progressCallback(e.loaded, e.total);
          });
          return xhr;
        }
      });
    };

    FileStorage.prototype.getUploadQueue = function() {
      return this._queue;
    };

    FileStorage.instance = function(id) {
      return new this(id);
    };

    return FileStorage;

  })();

  Cruddy.Layout = {};

  Cruddy.Layout.Element = (function(_super) {
    __extends(Element, _super);

    function Element(options, parent) {
      var _ref;
      this.parent = parent;
      this.disable = (_ref = options.disable) != null ? _ref : false;
      Element.__super__.constructor.apply(this, arguments);
    }

    Element.prototype.initialize = function() {
      if (!this.model && this.parent) {
        this.model = this.parent.model;
      }
      if (this.model) {
        this.entity = this.model.entity;
      }
      return Element.__super__.initialize.apply(this, arguments);
    };

    Element.prototype.handleValidationError = function(error) {
      if (this.parent) {
        this.parent.handleValidationError(error);
      }
      return this;
    };

    Element.prototype.isDisabled = function() {
      if (this.disable) {
        return true;
      }
      if (this.parent) {
        return this.parent.isDisabled();
      }
      return false;
    };

    Element.prototype.isFocusable = function() {
      return false;
    };

    Element.prototype.focus = function() {
      return this;
    };

    return Element;

  })(Cruddy.View);

  Cruddy.Layout.Container = (function(_super) {
    __extends(Container, _super);

    function Container() {
      return Container.__super__.constructor.apply(this, arguments);
    }

    Container.prototype.initialize = function(options) {
      Container.__super__.initialize.apply(this, arguments);
      this.$container = this.$el;
      this.items = [];
      if (options.items) {
        this.createItems(options.items);
      }
      return this;
    };

    Container.prototype.create = function(options) {
      var constructor;
      constructor = get(options["class"]);
      if (!constructor || !_.isFunction(constructor)) {
        console.error("Couldn't resolve element of type ", options["class"]);
        return;
      }
      return this.append(new constructor(options, this));
    };

    Container.prototype.createItems = function(items) {
      var item, _i, _len;
      for (_i = 0, _len = items.length; _i < _len; _i++) {
        item = items[_i];
        this.create(item);
      }
      return this;
    };

    Container.prototype.append = function(element) {
      if (element) {
        this.items.push(element);
      }
      return element;
    };

    Container.prototype.renderElement = function(element) {
      this.$container.append(element.render().$el);
      return this;
    };

    Container.prototype.render = function() {
      var element, _i, _len, _ref;
      if (this.items) {
        _ref = this.items;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          element = _ref[_i];
          this.renderElement(element);
        }
      }
      return Container.__super__.render.apply(this, arguments);
    };

    Container.prototype.remove = function() {
      var item, _i, _len, _ref;
      _ref = this.items;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        item = _ref[_i];
        item.remove();
      }
      return Container.__super__.remove.apply(this, arguments);
    };

    Container.prototype.getFocusable = function() {
      return _.find(this.items, function(item) {
        return item.isFocusable();
      });
    };

    Container.prototype.isFocusable = function() {
      return this.getFocusable() != null;
    };

    Container.prototype.focus = function() {
      var el;
      if (el = this.getFocusable()) {
        el.focus();
      }
      return this;
    };

    return Container;

  })(Cruddy.Layout.Element);

  Cruddy.Layout.BaseFieldContainer = (function(_super) {
    __extends(BaseFieldContainer, _super);

    function BaseFieldContainer(options) {
      var _ref;
      this.title = (_ref = options.title) != null ? _ref : null;
      BaseFieldContainer.__super__.constructor.apply(this, arguments);
    }

    return BaseFieldContainer;

  })(Cruddy.Layout.Container);

  Cruddy.Layout.FieldSet = (function(_super) {
    __extends(FieldSet, _super);

    function FieldSet() {
      return FieldSet.__super__.constructor.apply(this, arguments);
    }

    FieldSet.prototype.tagName = "fieldset";

    FieldSet.prototype.render = function() {
      this.$el.html(this.template());
      this.$container = this.$component("body");
      return FieldSet.__super__.render.apply(this, arguments);
    };

    FieldSet.prototype.template = function() {
      var html;
      html = this.title ? "<legend>" + _.escape(this.title) + "</legend>" : "";
      return html + "<div id='" + this.componentId("body") + "'></div>";
    };

    return FieldSet;

  })(Cruddy.Layout.BaseFieldContainer);

  Cruddy.Layout.TabPane = (function(_super) {
    __extends(TabPane, _super);

    function TabPane() {
      return TabPane.__super__.constructor.apply(this, arguments);
    }

    TabPane.prototype.className = "tab-pane";

    TabPane.prototype.initialize = function(options) {
      TabPane.__super__.initialize.apply(this, arguments);
      if (!options.title) {
        this.title = this.entity.get("title").singular;
      }
      this.$el.attr("id", this.cid);
      this.listenTo(this.model, "request", function() {
        if (this.header) {
          return this.header.resetErrors();
        }
      });
      return this;
    };

    TabPane.prototype.activate = function() {
      var _ref;
      if ((_ref = this.header) != null) {
        _ref.activate();
      }
      after_break((function(_this) {
        return function() {
          return _this.focus();
        };
      })(this));
      return this;
    };

    TabPane.prototype.getHeader = function() {
      if (!this.header) {
        this.header = new Cruddy.Layout.TabPane.Header({
          model: this
        });
      }
      return this.header;
    };

    TabPane.prototype.handleValidationError = function() {
      var _ref;
      if ((_ref = this.header) != null) {
        _ref.incrementErrors();
      }
      return TabPane.__super__.handleValidationError.apply(this, arguments);
    };

    return TabPane;

  })(Cruddy.Layout.BaseFieldContainer);

  Cruddy.Layout.TabPane.Header = (function(_super) {
    __extends(Header, _super);

    function Header() {
      return Header.__super__.constructor.apply(this, arguments);
    }

    Header.prototype.tagName = "li";

    Header.prototype.events = {
      "shown.bs.tab": function() {
        after_break((function(_this) {
          return function() {
            return _this.model.focus();
          };
        })(this));
      }
    };

    Header.prototype.initialize = function() {
      this.errors = 0;
      return Header.__super__.initialize.apply(this, arguments);
    };

    Header.prototype.incrementErrors = function() {
      this.$badge.text(++this.errors);
      return this;
    };

    Header.prototype.resetErrors = function() {
      this.errors = 0;
      this.$badge.text("");
      return this;
    };

    Header.prototype.render = function() {
      this.$el.html(this.template());
      this.$badge = this.$component("badge");
      return Header.__super__.render.apply(this, arguments);
    };

    Header.prototype.template = function() {
      return "<a href=\"#" + this.model.cid + "\" role=\"tab\" data-toggle=\"tab\">\n    " + this.model.title + "\n    <span class=\"badge\" id=\"" + (this.componentId("badge")) + "\"></span>\n</a>";
    };

    Header.prototype.activate = function() {
      this.$("a").tab("show");
      return this;
    };

    return Header;

  })(Cruddy.View);

  Cruddy.Layout.Row = (function(_super) {
    __extends(Row, _super);

    function Row() {
      return Row.__super__.constructor.apply(this, arguments);
    }

    Row.prototype.className = "row";

    return Row;

  })(Cruddy.Layout.Container);

  Cruddy.Layout.Col = (function(_super) {
    __extends(Col, _super);

    function Col() {
      return Col.__super__.constructor.apply(this, arguments);
    }

    Col.prototype.initialize = function(options) {
      this.$el.addClass("col-xs-" + options.span);
      return Col.__super__.initialize.apply(this, arguments);
    };

    return Col;

  })(Cruddy.Layout.BaseFieldContainer);

  Cruddy.Layout.Field = (function(_super) {
    __extends(Field, _super);

    function Field() {
      return Field.__super__.constructor.apply(this, arguments);
    }

    Field.prototype.initialize = function(options) {
      Field.__super__.initialize.apply(this, arguments);
      this.fieldView = null;
      if (!(this.field = this.entity.field(options.field))) {
        console.error("The field " + options.field + " is not found in " + this.entity.id + ".");
      }
      return this;
    };

    Field.prototype.render = function() {
      if (this.field && this.field.isVisible()) {
        this.fieldView = this.field.createView(this.model, this.isDisabled(), this);
      }
      if (this.fieldView) {
        this.$el.html(this.fieldView.render().$el);
      }
      return this;
    };

    Field.prototype.remove = function() {
      if (this.fieldView) {
        this.fieldView.remove();
      }
      return Field.__super__.remove.apply(this, arguments);
    };

    Field.prototype.isFocusable = function() {
      return this.fieldView && this.fieldView.isFocusable();
    };

    Field.prototype.focus = function() {
      if (this.fieldView) {
        this.fieldView.focus();
      }
      return this;
    };

    return Field;

  })(Cruddy.Layout.Element);

  Cruddy.Layout.Text = (function(_super) {
    __extends(Text, _super);

    function Text() {
      return Text.__super__.constructor.apply(this, arguments);
    }

    Text.prototype.tagName = "p";

    Text.prototype.className = "text-node";

    Text.prototype.initialize = function(options) {
      if (options.contents) {
        this.$el.html(options.contents);
      }
      return Text.__super__.initialize.apply(this, arguments);
    };

    return Text;

  })(Cruddy.Layout.Element);

  FieldList = (function(_super) {
    __extends(FieldList, _super);

    function FieldList() {
      return FieldList.__super__.constructor.apply(this, arguments);
    }

    FieldList.prototype.className = "field-list";

    FieldList.prototype.initialize = function() {
      var field, _i, _len, _ref;
      FieldList.__super__.initialize.apply(this, arguments);
      _ref = this.entity.fields.models;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        field = _ref[_i];
        this.append(new Cruddy.Layout.Field({
          field: field.id
        }, this));
      }
      return this;
    };

    return FieldList;

  })(Cruddy.Layout.BaseFieldContainer);

  Cruddy.Layout.Layout = (function(_super) {
    __extends(Layout, _super);

    function Layout() {
      return Layout.__super__.constructor.apply(this, arguments);
    }

    Layout.prototype.initialize = function() {
      Layout.__super__.initialize.apply(this, arguments);
      return this.setupLayout();
    };

    Layout.prototype.setupLayout = function() {
      if (this.entity.attributes.layout) {
        this.createItems(this.entity.attributes.layout);
      } else {
        this.setupDefaultLayout();
      }
      return this;
    };

    Layout.prototype.setupDefaultLayout = function() {
      return this;
    };

    return Layout;

  })(Cruddy.Layout.Container);

  Cruddy.Entity = {};

  Cruddy.Entity.Entity = (function(_super) {
    __extends(Entity, _super);

    function Entity() {
      return Entity.__super__.constructor.apply(this, arguments);
    }

    Entity.prototype.initialize = function(attributes, options) {
      this.fields = this.createObjects(attributes.fields);
      this.columns = this.createObjects(attributes.columns);
      this.filters = this.createObjects(attributes.filters);
      this.permissions = Cruddy.permissions[this.id];
      this.cache = {};
      return this;
    };

    Entity.prototype.createObjects = function(items) {
      var constructor, data, options, _i, _len;
      data = [];
      for (_i = 0, _len = items.length; _i < _len; _i++) {
        options = items[_i];
        options.entity = this;
        constructor = get(options["class"]);
        if (!constructor) {
          throw "The class " + options["class"] + " is not found";
        }
        data.push(new constructor(options));
      }
      return new Backbone.Collection(data);
    };

    Entity.prototype.createDataSource = function(data) {
      var col, defaults;
      defaults = {
        order_by: this.get("order_by")
      };
      if (col = this.columns.get(defaults.order_by)) {
        defaults.order_dir = col.get("order_dir");
      }
      data = $.extend({}, defaults, data);
      return new DataSource(data, {
        entity: this
      });
    };

    Entity.prototype.getDataSource = function() {
      if (!this.dataSource) {
        this.dataSource = this.createDataSource();
      }
      return this.dataSource;
    };

    Entity.prototype.createFilters = function(columns) {
      var col, filters;
      if (columns == null) {
        columns = this.columns;
      }
      filters = (function() {
        var _i, _len, _ref, _results;
        _ref = columns.models;
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          col = _ref[_i];
          if (col.get("filter_type") === "complex") {
            _results.push(col.createFilter());
          }
        }
        return _results;
      })();
      return new Backbone.Collection(filters);
    };

    Entity.prototype.createInstance = function(data, options) {
      var attributes, instance;
      if (data == null) {
        data = {};
      }
      if (options == null) {
        options = {};
      }
      options.entity = this;
      attributes = _.extend({}, this.get("defaults"), data.attributes);
      instance = new Cruddy.Entity.Instance(attributes, options);
      return instance.setMetaFromResponse(data);
    };

    Entity.prototype.field = function(id) {
      var field;
      if (!(field = this.fields.get(id))) {
        console.error("The field " + id + " is not found.");
        return;
      }
      return field;
    };

    Entity.prototype.getField = function(id) {
      return this.fields.get(id);
    };

    Entity.prototype.search = function(options) {
      if (options == null) {
        options = {};
      }
      return new SearchDataSource({}, $.extend({
        url: this.url()
      }, options));
    };

    Entity.prototype.load = function(id, options) {
      var defaults, xhr;
      defaults = {
        cached: true
      };
      options = $.extend(defaults, options);
      if (options.cached && id in this.cache) {
        return $.Deferred().resolve(this.cache[id]).promise();
      }
      xhr = $.ajax({
        url: this.url(id),
        type: "GET",
        dataType: "json",
        displayLoading: true
      });
      xhr = xhr.then((function(_this) {
        return function(resp) {
          var instance;
          instance = _this.createInstance(resp);
          _this.cache[instance.id] = instance;
          return instance;
        };
      })(this));
      return xhr;
    };

    Entity.prototype.destroy = function(id, options) {
      if (options == null) {
        options = {};
      }
      options.url = this.url(id);
      options.type = "POST";
      options.dataType = "json";
      options.data = {
        _method: "DELETE"
      };
      options.displayLoading = true;
      return $.ajax(options);
    };

    Entity.prototype.executeAction = function(id, action, options) {
      if (options == null) {
        options = {};
      }
      options.url = this.url(id + "/" + action);
      options.type = "POST";
      options.dataType = "json";
      options.displayLoading = true;
      return $.ajax(options);
    };

    Entity.prototype.getCopyableAttributes = function(model, copy) {
      var data, field, _i, _len, _ref;
      data = {};
      _ref = this.fields.models;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        field = _ref[_i];
        if (field.isCopyable()) {
          data[field.id] = field.copyAttribute(model, copy);
        }
      }
      return data;
    };

    Entity.prototype.hasChangedSinceSync = function(model) {
      var field, _i, _len, _ref;
      _ref = this.fields.models;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        field = _ref[_i];
        if (!(field.hasChangedSinceSync(model))) {
          continue;
        }
        console.log(field);
        return true;
      }
      return false;
    };

    Entity.prototype.prepareAttributes = function(attributes, model) {
      var field, key, result, value;
      result = {};
      for (key in attributes) {
        value = attributes[key];
        if (field = this.getField(key)) {
          result[key] = field.prepareAttribute(value);
        }
      }
      return result;
    };

    Entity.prototype.url = function(id) {
      return entity_url(this.id, id);
    };

    Entity.prototype.link = function(id) {
      var link;
      link = this.url();
      if (id instanceof Cruddy.Entity.Instance) {
        id = id.id;
      }
      if (id) {
        return link + "?id=" + id;
      } else {
        return link;
      }
    };

    Entity.prototype.createController = function() {
      var controllerClass;
      controllerClass = get(this.attributes.controller_class) || Cruddy.Entity.Page;
      if (!controllerClass) {
        throw "Failed to resolve page class " + this.attributes.view;
      }
      return new controllerClass({
        model: this
      });
    };

    Entity.prototype.getPluralTitle = function() {
      return this.attributes.title.plural;
    };

    Entity.prototype.getSingularTitle = function() {
      return this.attributes.title.singular;
    };

    Entity.prototype.getPermissions = function() {
      return this.permissions;
    };

    Entity.prototype.readPermitted = function() {
      return this.permissions.read;
    };

    Entity.prototype.updatePermitted = function() {
      return this.permissions.update;
    };

    Entity.prototype.createPermitted = function() {
      return this.permissions.create;
    };

    Entity.prototype.deletePermitted = function() {
      return this.permissions["delete"];
    };

    Entity.prototype.isSoftDeleting = function() {
      return this.attributes.soft_deleting;
    };

    Entity.prototype.getPrimaryKey = function() {
      return this.attributes.primary_key || "id";
    };

    return Entity;

  })(Backbone.Model);

  Cruddy.Entity.Instance = (function(_super) {
    __extends(Instance, _super);

    Instance.prototype.cidPrefix = "0";

    Instance.prototype.idAttribute = "__id";

    function Instance(attributes, options) {
      this.entity = options.entity;
      this.meta = {};
      Instance.__super__.constructor.apply(this, arguments);
    }

    Instance.prototype.initialize = function(attributes, options) {
      this.syncOriginalAttributes();
      this.on("error", this.handleErrorEvent, this);
      this.on("sync", this.handleSyncEvent, this);
      this.on("destroy", this.handleDestroyEvent, this);
      return this;
    };

    Instance.prototype.syncOriginalAttributes = function() {
      this.original = _.clone(this.attributes);
      return this;
    };

    Instance.prototype.handleSyncEvent = function(model, resp) {
      this.syncOriginalAttributes();
      if ((resp != null ? resp.model : void 0) != null) {
        this.setMetaFromResponse(resp.model);
      }
      return this;
    };

    Instance.prototype.setMetaFromResponse = function(resp) {
      if (resp.meta != null) {
        this.meta = _.clone(resp.meta);
      }
      return this;
    };

    Instance.prototype.handleErrorEvent = function(model, xhr) {
      if (xhr.status === VALIDATION_FAILED_CODE) {
        this.trigger("invalid", this, xhr.responseJSON);
      }
    };

    Instance.prototype.handleDestroyEvent = function(model) {
      this.isDeleted = true;
    };

    Instance.prototype.validate = function() {
      this.set("errors", {});
      return null;
    };

    Instance.prototype.link = function() {
      return this.entity.link(this.id || "create");
    };

    Instance.prototype.url = function() {
      return this.entity.url(this.id);
    };

    Instance.prototype.set = function(key, val, options) {
      var attributeId, field, value;
      if (_.isObject(key)) {
        for (attributeId in key) {
          value = key[attributeId];
          if (field = this.entity.getField(attributeId)) {
            key[attributeId] = field.parse(this, value);
          }
        }
      }
      return Instance.__super__.set.apply(this, arguments);
    };

    Instance.prototype.sync = function(method, model, options) {
      var _ref;
      if (method === "update" || method === "create") {
        options.data = new AdvFormData(this.entity.prepareAttributes((_ref = options.attrs) != null ? _ref : this.attributes, this)).original;
        options.contentType = false;
        options.processData = false;
      }
      return Instance.__super__.sync.apply(this, arguments);
    };

    Instance.prototype.parse = function(resp) {
      return resp.model.attributes;
    };

    Instance.prototype.copy = function() {
      var copy;
      copy = this.entity.createInstance();
      copy.set(this.entity.getCopyableAttributes(this, copy), {
        silent: true
      });
      return copy;
    };

    Instance.prototype.hasChangedSinceSync = function() {
      return this.entity.hasChangedSinceSync(this);
    };

    Instance.prototype.canBeSaved = function() {
      var isNew;
      isNew = this.isNew();
      return ((isNew && this.entity.createPermitted()) || (!isNew && this.entity.updatePermitted())) && (!this.isDeleted || !isNew);
    };

    Instance.prototype.serialize = function() {
      var data;
      data = this.isDeleted ? {} : this.entity.prepareAttributes(this.attributes, this);
      return $.extend(data, {
        __id: this.id,
        __d: this.isDeleted
      });
    };

    Instance.prototype.action = function() {
      if (this.isNew()) {
        return "create";
      } else {
        return "update";
      }
    };

    Instance.prototype.getTitle = function() {
      if (this.isNew()) {
        return Cruddy.lang.model_new_record;
      } else {
        return this.meta.title;
      }
    };

    Instance.prototype.getOriginal = function(key) {
      return this.original[key];
    };

    return Instance;

  })(Backbone.Model);

  Cruddy.Entity.Page = (function(_super) {
    __extends(Page, _super);

    Page.prototype.className = "page entity-page";

    Page.prototype.events = {
      "click .ep-btn-create": "create",
      "click .ep-btn-refresh": "refreshData"
    };

    function Page(options) {
      this.className += " entity-page-" + options.model.id;
      Page.__super__.constructor.apply(this, arguments);
    }

    Page.prototype.initialize = function(options) {
      this.dataSource = this._setupDataSource();
      after_break((function(_this) {
        return function() {
          return _this.listenTo(Cruddy.router, "route:index", _this._updateFromQuery);
        };
      })(this));
      return Page.__super__.initialize.apply(this, arguments);
    };

    Page.prototype._updateFromQuery = function() {
      this._updateDataSourceFromQuery();
      this._displayForm().fail((function(_this) {
        return function() {
          return _this._updateModelIdInQuery({
            replace: true
          });
        };
      })(this));
      return this;
    };

    Page.prototype._setupDataSource = function() {
      var dataSource;
      this.dataSource = dataSource = this.model.getDataSource();
      this._updateFromQuery();
      if (!(dataSource.inProgress() || dataSource.hasData())) {
        dataSource.fetch();
      }
      this.listenTo(dataSource, "change", this._refreshQuery);
      return dataSource;
    };

    Page.prototype._refreshQuery = function() {
      var dataSource;
      dataSource = this.dataSource;
      Cruddy.router.refreshQuery(dataSource.attributes, dataSource.defaults, {
        trigger: false
      });
      return this;
    };

    Page.prototype._updateDataSourceFromQuery = function(options) {
      var data, key;
      data = $.extend({}, this.dataSource.defaults, _.omit(Cruddy.router.query.keys, ["id"]));
      for (key in this.dataSource.attributes) {
        if (!(key in data)) {
          data[key] = null;
        }
      }
      this.dataSource.set(data, options);
    };

    Page.prototype._updateModelIdInQuery = function(options) {
      var router;
      router = Cruddy.router;
      options = $.extend({
        trigger: false,
        replace: false
      }, options);
      if (this.form) {
        router.setQuery("id", this.form.model.id || "new", options);
      } else {
        router.removeQuery("id", options);
      }
      return this;
    };

    Page.prototype._displayForm = function(instanceId) {
      var compareId, dfd, instance, resolve, _ref;
      if (this.loadingForm) {
        return this.loadingForm;
      }
      instanceId = instanceId != null ? instanceId : Cruddy.router.getQuery("id");
      if (instanceId instanceof Cruddy.Entity.Instance) {
        instance = instanceId;
        instanceId = instance.id || "new";
      }
      this.loadingForm = dfd = $.Deferred();
      this.loadingForm.always((function(_this) {
        return function() {
          return _this.loadingForm = null;
        };
      })(this));
      if (this.form) {
        compareId = this.form.model.isNew() ? "new" : this.form.model.id;
        if (instanceId === compareId || !this.form.confirmClose()) {
          dfd.reject();
          return dfd.promise();
        }
      }
      resolve = (function(_this) {
        return function(instance) {
          _this._createAndRenderForm(instance);
          return dfd.resolve(instance);
        };
      })(this);
      if (instanceId === "new" && !instance) {
        instance = this.model.createInstance();
      }
      if (instance) {
        resolve(instance);
        return dfd.promise();
      }
      if (instanceId) {
        this.model.load(instanceId).done(resolve).fail(function() {
          return dfd.reject();
        });
      } else {
        if ((_ref = this.form) != null) {
          _ref.remove();
        }
        dfd.resolve();
      }
      return dfd.promise();
    };

    Page.prototype._createAndRenderForm = function(instance) {
      var form, _ref;
      if ((_ref = this.form) != null) {
        _ref.remove();
      }
      this.form = form = Cruddy.Entity.Form.display(instance);
      form.on("close", (function(_this) {
        return function() {
          return Cruddy.router.removeQuery("id", {
            trigger: false
          });
        };
      })(this));
      form.on("created", function(model) {
        return Cruddy.router.setQuery("id", model.id);
      });
      form.on("remove", (function(_this) {
        return function() {
          _this.form = null;
          _this.model.set("instance", null);
          return _this.stopListening(instance);
        };
      })(this));
      form.on("saved", (function(_this) {
        return function() {
          _this.dataSource.fetch();
          return _this._updateModelIdInQuery({
            replace: true
          });
        };
      })(this));
      form.on("saved remove", function() {
        return Cruddy.app.updateTitle();
      });
      this.model.set("instance", instance);
      Cruddy.app.updateTitle();
      return this;
    };

    Page.prototype.displayForm = function(id) {
      return this._displayForm(id).done((function(_this) {
        return function() {
          return _this._updateModelIdInQuery();
        };
      })(this));
    };

    Page.prototype.create = function() {
      this.displayForm("new");
      return this;
    };

    Page.prototype.refreshData = function(e) {
      var btn;
      btn = $(e.currentTarget);
      btn.prop("disabled", true);
      this.dataSource.fetch().always(function() {
        return btn.prop("disabled", false);
      });
      return this;
    };

    Page.prototype.render = function() {
      this.$el.html(this.template());
      this.searchInputView = this.createSearchInputView();
      this.dataView = this.createDataView();
      this.paginationView = this.createPaginationView();
      this.filterListView = this.createFilterListView();
      if (this.searchInputView) {
        this.$component("search_input_view").append(this.searchInputView.render().$el);
      }
      if (this.filterListView) {
        this.$component("filter_list_view").append(this.filterListView.render().el);
      }
      if (this.dataView) {
        this.$component("data_view").append(this.dataView.render().el);
      }
      if (this.paginationView) {
        this.$component("pagination_view").append(this.paginationView.render().el);
      }
      return this;
    };

    Page.prototype.createDataView = function() {
      return new DataGrid({
        model: this.dataSource,
        entity: this.model
      });
    };

    Page.prototype.createPaginationView = function() {
      return new Pagination({
        model: this.dataSource
      });
    };

    Page.prototype.createFilterListView = function() {
      var filters;
      if ((filters = this.dataSource.entity.filters).isEmpty()) {
        return;
      }
      return new FilterList({
        model: this.dataSource,
        entity: this.model,
        filters: filters
      });
    };

    Page.prototype.createSearchInputView = function() {
      return new Cruddy.Inputs.Search({
        model: this.dataSource,
        key: "keywords"
      });
    };

    Page.prototype.template = function() {
      return "<div class=\"content-header\">\n    <div class=\"column column-main\">\n        <h1 class=\"entity-title\">" + (this.model.getPluralTitle()) + "</h1>\n\n        <div class=\"entity-title-buttons\">\n            " + (this.buttonsTemplate()) + "\n        </div>\n    </div>\n\n    <div class=\"column column-extra\">\n        <div class=\"entity-search-box\" id=\"" + (this.componentId("search_input_view")) + "\"></div>\n    </div>\n</div>\n\n<div class=\"content-body\">\n    <div class=\"column column-main\">\n        <div id=\"" + (this.componentId("data_view")) + "\"></div>\n        <div id=\"" + (this.componentId("pagination_view")) + "\"></div>\n    </div>\n\n    <div class=\"column column-extra\" id=\"" + (this.componentId("filter_list_view")) + "\"></div>\n</div>";
    };

    Page.prototype.buttonsTemplate = function() {
      var html;
      html = "<button type=\"button\" class=\"btn btn-default ep-btn-refresh\" title=\"" + Cruddy.lang.refresh + "\">\n    " + (b_icon("refresh")) + "\n</button>";
      html += " ";
      if (this.model.createPermitted()) {
        html += "<button type=\"button\" class=\"btn btn-primary ep-btn-create\" title=\"" + Cruddy.lang.add + "\">\n    " + (b_icon("plus")) + "\n</button>";
      }
      return html;
    };

    Page.prototype.remove = function() {
      var _ref, _ref1, _ref2, _ref3, _ref4;
      if ((_ref = this.form) != null) {
        _ref.remove();
      }
      if ((_ref1 = this.filterListView) != null) {
        _ref1.remove();
      }
      if ((_ref2 = this.dataView) != null) {
        _ref2.remove();
      }
      if ((_ref3 = this.paginationView) != null) {
        _ref3.remove();
      }
      if ((_ref4 = this.searchInputView) != null) {
        _ref4.remove();
      }
      return Page.__super__.remove.apply(this, arguments);
    };

    Page.prototype.getPageTitle = function() {
      var title;
      title = this.model.getPluralTitle();
      if (this.form != null) {
        title = this.form.model.getTitle() + TITLE_SEPARATOR + title;
      }
      return title;
    };

    Page.prototype.executeCustomAction = function(actionId, modelId, el) {
      if (el && !$(el).parent().is("disabled")) {
        this.model.executeAction(modelId, actionId, {
          success: (function(_this) {
            return function(resp) {
              if (resp.successful) {
                _this.dataSource.fetch();
              }
              return Cruddy.getApp().displayActionResult(resp);
            };
          })(this)
        });
      }
      return this;
    };

    Page.prototype.pageUnloadConfirmationMessage = function() {
      var _ref;
      return (_ref = this.form) != null ? _ref.pageUnloadConfirmationMessage() : void 0;
    };

    return Page;

  })(Cruddy.View);

  Cruddy.Entity.Form = (function(_super) {
    __extends(Form, _super);

    Form.prototype.className = "entity-form";

    Form.prototype.events = {
      "click [data-action]": "executeAction"
    };

    function Form(options) {
      this.className += " " + this.className + "-" + options.model.entity.id;
      Form.__super__.constructor.apply(this, arguments);
    }

    Form.prototype.initialize = function(options) {
      Form.__super__.initialize.apply(this, arguments);
      this.saveOptions = {
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
      };
      this.listenTo(this.model, "destroy", this.handleModelDestroyEvent);
      this.listenTo(this.model, "invalid", this.handleModelInvalidEvent);
      this.hotkeys = $(document).on("keydown." + this.cid, "body", $.proxy(this, "hotkeys"));
      return this;
    };

    Form.prototype.setupDefaultLayout = function() {
      var field, tab, _i, _len, _ref;
      tab = this.append(new Cruddy.Layout.TabPane({
        title: this.model.entity.get("title").singular
      }, this));
      _ref = this.entity.fields.models;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        field = _ref[_i];
        tab.append(new Cruddy.Layout.Field({
          field: field.id
        }, tab));
      }
      return this;
    };

    Form.prototype.hotkeys = function(e) {
      if (e.ctrlKey && e.keyCode === 90 && e.target === document.body) {
        this.model.set(this.model.previousAttributes());
        return false;
      }
      if (e.ctrlKey && e.keyCode === 13) {
        this.saveModel();
        return false;
      }
      if (e.keyCode === 27) {
        this.closeForm();
        e.preventDefault();
        return false;
      }
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
      this.$footer.prepend(this.alert.render().el);
      return this;
    };

    Form.prototype.displaySuccess = function(resp) {
      this.displayAlert(Cruddy.lang.form_saved, "success", 3000);
      if (resp.actionResult) {
        Cruddy.getApp().displayActionResult(resp.actionResult);
      }
      return this;
    };

    Form.prototype.displayError = function(xhr) {
      if (xhr.status !== VALIDATION_FAILED_CODE) {
        this.displayAlert(Cruddy.lang.form_failed, "danger", 5000);
      }
      return this;
    };

    Form.prototype.handleModelInvalidEvent = function() {
      return this.displayAlert(Cruddy.lang.form_invalid, "warning", 5000);
    };

    Form.prototype.handleModelDestroyEvent = function() {
      this.updateModelState();
      this.trigger("destroyed", this.model);
      return this;
    };

    Form.prototype.show = function() {
      this.$el.toggleClass("opened", true);
      this.items[0].activate();
      this.focus();
      return this;
    };

    Form.prototype.save = function(options) {
      var isNew;
      if (this.request != null) {
        return;
      }
      isNew = this.model.isNew();
      this.setupRequest(this.model.save(null, $.extend({}, this.saveOptions, options)));
      this.request.done((function(_this) {
        return function(resp) {
          _this.trigger((isNew ? "created" : "updated"), _this.model, resp);
          _this.trigger("saved", _this.model, resp);
          return _this.updateModelState();
        };
      })(this));
      return this;
    };

    Form.prototype.saveModel = function() {
      return this.save();
    };

    Form.prototype.saveWithAction = function($el) {
      return this.save({
        url: this.model.entity.url(this.model.id + "/" + $el.data("actionId"))
      });
    };

    Form.prototype.destroyModel = function() {
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

    Form.prototype.copyModel = function() {
      Cruddy.app.entityView.displayForm(this.model.copy());
      return this;
    };

    Form.prototype.refreshModel = function() {
      if (this.request != null) {
        return;
      }
      if (this.confirmClose()) {
        this.setupRequest(this.model.fetch());
      }
      this.request.done((function(_this) {
        return function() {
          return _this.updateModelMetaState();
        };
      })(this));
      return this;
    };

    Form.prototype.setupRequest = function(request) {
      request.done($.proxy(this, "displaySuccess")).fail($.proxy(this, "displayError"));
      request.always((function(_this) {
        return function() {
          _this.request = null;
          return _this.updateRequestState();
        };
      })(this));
      this.request = request;
      return this.updateRequestState();
    };

    Form.prototype.progressCallback = function(e) {
      var width;
      if (e.lengthComputable) {
        width = (e.loaded * 100) / e.total;
        this.$progressBar.width(width + '%').parent().show();
        if (width === 100) {
          this.$progressBar.parent().hide();
        }
      }
      return this;
    };

    Form.prototype.closeForm = function() {
      if (this.confirmClose()) {
        this.remove();
        this.trigger("close");
      }
      return this;
    };

    Form.prototype.pageUnloadConfirmationMessage = function() {
      if (this.model.isDeleted) {
        return;
      }
      if (this.request) {
        return Cruddy.lang.onclose_abort;
      }
      if (this.model.hasChangedSinceSync()) {
        return Cruddy.lang.onclose_discard;
      }
    };

    Form.prototype.confirmClose = function() {
      if (!this.model.isDeleted) {
        if (this.request) {
          return confirm(Cruddy.lang.confirm_abort);
        }
        if (this.model.hasChangedSinceSync()) {
          return confirm(Cruddy.lang.confirm_discard);
        }
      }
      return true;
    };

    Form.prototype.render = function() {
      this.$el.html(this.template());
      this.$container = this.$component("body");
      this.$nav = this.$component("nav");
      this.$footer = this.$component("footer");
      this.$btnSave = this.$component("save");
      this.$deletedMsg = this.$component("deleted-message");
      this.$progressBar = this.$component("progress");
      this.$serviceMenu = this.$component("service-menu");
      this.$serviceMenuItems = this.$component("service-menu-items");
      this.updateModelState();
      return Form.__super__.render.apply(this, arguments);
    };

    Form.prototype.renderElement = function(el) {
      this.$nav.append(el.getHeader().render().$el);
      return Form.__super__.renderElement.apply(this, arguments);
    };

    Form.prototype.updateRequestState = function() {
      var isLoading;
      isLoading = this.request != null;
      this.$el.toggleClass("loading", isLoading);
      this.$btnSave.attr("disabled", isLoading);
      if (this.$btnExtraActions) {
        this.$btnExtraActions.attr("disabled", isLoading);
        this.$btnExtraActions.children(".btn").attr("disabled", isLoading);
      }
      return this;
    };

    Form.prototype.updateModelState = function() {
      var entity, isDeleted, isNew;
      entity = this.model.entity;
      isNew = this.model.isNew();
      isDeleted = this.model.isDeleted || false;
      this.$el.toggleClass("destroyed", isDeleted);
      this.$btnSave.text(isNew ? Cruddy.lang.create : Cruddy.lang.save);
      this.$btnSave.toggle(!isDeleted && (isNew ? entity.createPermitted() : entity.updatePermitted()));
      return this.updateModelMetaState();
    };

    Form.prototype.updateModelMetaState = function() {
      var html, isDeleted, isNew, _ref;
      isNew = this.model.isNew();
      isDeleted = this.model.isDeleted || false;
      this.$serviceMenu.toggle(!isNew);
      if (!isNew) {
        this.$serviceMenuItems.html(this.renderServiceMenuItems());
      }
      if ((_ref = this.$btnExtraActions) != null) {
        _ref.remove();
      }
      this.$btnExtraActions = null;
      if (this.model.entity.updatePermitted()) {
        if (!isNew && !isDeleted && (html = this.renderExtraActionsButton())) {
          this.$btnSave.before(this.$btnExtraActions = $(html));
        }
      }
      return this;
    };

    Form.prototype.template = function() {
      return "<div class=\"navbar navbar-default navbar-static-top\" role=\"navigation\">\n    <div class=\"container-fluid\">\n        <ul id=\"" + (this.componentId("nav")) + "\" class=\"nav navbar-nav\"></ul>\n\n        <ul id=\"" + (this.componentId("service-menu")) + "\" class=\"nav navbar-nav navbar-right\">\n            <li class=\"dropdown\">\n                <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" href=\"#\">\n                    <span class=\"glyphicon glyphicon-option-horizontal\"></span>\n                </a>\n\n                <ul class=\"dropdown-menu\" role=\"menu\" id=\"" + (this.componentId("service-menu-items")) + "\"></ul>\n            </li>\n        </ul>\n    </div>\n</div>\n\n<div class=\"tab-content\" id=\"" + (this.componentId("body")) + "\"></div>\n\n<footer id=\"" + (this.componentId("footer")) + "\">\n    <span class=\"fs-deleted-message\">" + Cruddy.lang.model_deleted + "</span>\n\n    <button data-action=\"closeForm\" id=\"" + (this.componentId("close")) + "\" type=\"button\" class=\"btn btn-default\">" + Cruddy.lang.close + "</button><!--\n    --><button data-action=\"saveModel\" id=\"" + (this.componentId("save")) + "\" type=\"button\" class=\"btn btn-primary btn-save\"></button>\n\n    <div class=\"progress\">\n        <div id=\"" + (this.componentId("progress")) + "\" class=\"progress-bar form-save-progress\"></div>\n    </div>\n</footer>";
    };

    Form.prototype.renderServiceMenuItems = function() {
      var entity, html, isDeleted, items, model;
      entity = (model = this.model).entity;
      html = "";
      if (!((isDeleted = model.isDeleted) || _.isEmpty(items = model.meta.links))) {
        html += render_presentation_actions(items);
        html += render_divider();
      }
      html += "<li class=\"" + (value_if(isDeleted, "disabled")) + "\">\n    <a data-action=\"refreshModel\" href=\"#\">\n        " + Cruddy.lang.model_refresh + "\n    </a>\n</li>";
      html += "<li class=\"" + (value_if(!entity.createPermitted(), "disabled")) + "\">\n    <a data-action=\"copyModel\" href=\"#\">\n        " + Cruddy.lang.model_copy + "\n    </a>\n</li>";
      html += "<li class=\"divider\"></li>\n\n<li class=\"" + (value_if(isDeleted || !entity.deletePermitted(), "disabled")) + "\">\n    <a data-action=\"destroyModel\" href=\"#\">\n        <span class=\"glyphicon glyphicon-trash\"></span> " + Cruddy.lang.model_delete + "\n    </a>\n</li>";
      return html;
    };

    Form.prototype.renderExtraActionsButton = function() {
      var button, mainAction;
      if (_.isEmpty(this.model.meta.actions)) {
        return;
      }
      mainAction = _.find(this.model.meta.actions, function(item) {
        return !item.disabled;
      }) || _.first(this.model.meta.actions);
      button = "<button data-action=\"saveWithAction\" data-action-id=\"" + mainAction.id + "\" type=\"button\" class=\"btn btn-" + mainAction.state + "\" " + (value_if(mainAction.isDisabled, "disabled")) + ">\n    " + mainAction.title + "\n</button>";
      return this.wrapWithExtraActions(button, mainAction);
    };

    Form.prototype.wrapWithExtraActions = function(button, mainAction) {
      var action, actions, html, _i, _len;
      actions = _.filter(this.model.meta.actions, function(action) {
        return action !== mainAction;
      });
      if (_.isEmpty(actions)) {
        return button;
      }
      html = "";
      for (_i = 0, _len = actions.length; _i < _len; _i++) {
        action = actions[_i];
        html += "<li class=\"" + (value_if(action.disabled, "disabled")) + "\">\n    <a data-action=\"saveWithAction\" data-action-id=\"" + action.id + "\" href=\"#\">" + action.title + "</a>\n</li>";
      }
      return "<div class=\"btn-group dropup\">\n    " + button + "\n\n    <button type=\"button\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\">\n        <span class=\"caret\"></span>\n    </button>\n\n    <ul class=\"dropdown-menu dropdown-menu-right\" role=\"menu\">\n        " + html + "\n    </ul>\n</div>";
    };

    Form.prototype.remove = function() {
      this.trigger("remove", this);
      if (this.request) {
        this.request.abort();
      }
      $(document).off("." + this.cid);
      this.$el.one(TRANSITIONEND, (function(_this) {
        return function() {
          _this.trigger("removed", _this);
          return Form.__super__.remove.apply(_this, arguments);
        };
      })(this));
      this.$el.removeClass("opened");
      return Form.__super__.remove.apply(this, arguments);
    };

    Form.prototype.executeAction = function(e) {
      var $el, action;
      if (e.isDefaultPrevented()) {
        return;
      }
      if ((action = ($el = $(e.currentTarget)).data("action")) && action in this) {
        e.preventDefault();
        this[action].call(this, $el);
      }
    };

    return Form;

  })(Cruddy.Layout.Layout);

  Cruddy.Entity.Form.display = function(instance) {
    var form;
    form = new Cruddy.Entity.Form({
      model: instance
    });
    $(document.body).append(form.render().$el);
    after_break((function(_this) {
      return function() {
        return form.show();
      };
    })(this));
    return form;
  };

  Cruddy.Inputs = {};

  Cruddy.Inputs.Base = (function(_super) {
    __extends(Base, _super);

    function Base(options) {
      this.key = options.key;
      Base.__super__.constructor.apply(this, arguments);
    }

    Base.prototype.initialize = function() {
      this.listenTo(this.model, "change:" + this.key, function(model, value, _arg) {
        var input;
        input = _arg.input;
        return this.handleValueChanged(value, input === this);
      });
      return this;
    };

    Base.prototype.handleValueChanged = function(newValue, bySelf) {
      return this;
    };

    Base.prototype.render = function() {
      return this.handleValueChanged(this.getValue(), false);
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

    Base.prototype.emptyValue = function() {
      return null;
    };

    Base.prototype.empty = function() {
      return this.setValue(this.emptyValue());
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

    Static.prototype.handleValueChanged = function() {
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
      "change": "submitValue",
      "keydown": "handleKeydown"
    };

    BaseText.prototype.handleKeydown = function(e) {
      if (e.ctrlKey && e.keyCode === 13) {
        return this.submitValue();
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

    BaseText.prototype.submitValue = function() {
      return this.setValue(this.$el.val());
    };

    BaseText.prototype.handleValueChanged = function(newValue, bySelf) {
      if (!bySelf) {
        this.$el.val(newValue);
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

    Checkbox.prototype.events = {
      "change :checkbox": "handleCheckboxChanged"
    };

    Checkbox.prototype.initialize = function(options) {
      this.label = options.label || null;
      return Checkbox.__super__.initialize.apply(this, arguments);
    };

    Checkbox.prototype.handleCheckboxChanged = function() {
      return this.setValue(this.input.prop("checked"));
    };

    Checkbox.prototype.handleValueChanged = function(newValue, bySelf) {
      if (!bySelf) {
        this.input.prop("checked", newValue);
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
      var _ref;
      this.tripleState = (_ref = options.tripleState) != null ? _ref : false;
      return Boolean.__super__.initialize.apply(this, arguments);
    };

    Boolean.prototype.check = function(e) {
      var currentValue, value;
      currentValue = this.getValue();
      value = !!$(e.target).data("value");
      if (value === currentValue && this.tripleState) {
        value = null;
      }
      return this.setValue(value);
    };

    Boolean.prototype.handleValueChanged = function(value) {
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

    Boolean.prototype.focus = function() {
      var _ref;
      if ((_ref = this.values) != null) {
        _ref[0].focus();
      }
      return this;
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
      "click .ed-item>.input-group-btn>.btn-remove": "removeItem",
      "click .ed-item>.input-group-btn>.btn-edit": "editItem",
      "click .ed-item>.form-control": "executeFirstAction",
      "keydown .ed-item>.form-control": "handleItemKeydown",
      "keydown [type=search]": "handleSearchKeydown",
      "show.bs.dropdown": "handleDropdownShow",
      "shown.bs.dropdown": function() {
        after_break((function(_this) {
          return function() {
            return _this.selector.focus();
          };
        })(this));
        return this;
      },
      "hide.bs.dropdown": function(e) {
        this.opened = false;
      }
    };

    EntityDropdown.prototype.initialize = function(options) {
      var _ref, _ref1, _ref2;
      if (options.multiple != null) {
        this.multiple = options.multiple;
      }
      if (options.reference != null) {
        this.reference = options.reference;
      }
      if (options.owner != null) {
        this.owner = options.owner;
      }
      this.allowEdit = ((_ref = options.allowEdit) != null ? _ref : true) && this.reference.updatePermitted();
      this.placeholder = (_ref1 = options.placeholder) != null ? _ref1 : Cruddy.lang.not_selected;
      this.enabled = (_ref2 = options.enabled) != null ? _ref2 : true;
      this.editing = false;
      this.disableDropdown = false;
      this.opened = false;
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

    EntityDropdown.prototype.getValue = function() {
      return EntityDropdown.__super__.getValue.apply(this, arguments) || (this.multiple ? [] : null);
    };

    EntityDropdown.prototype.removeItem = function(e) {
      var i, value;
      if (this.multiple) {
        i = this.getKey(e);
        value = _.clone(this.getValue());
        value.splice(i, 1);
      } else {
        value = null;
      }
      return this.setValue(value);
    };

    EntityDropdown.prototype.executeFirstAction = function(e) {
      $(".btn:not(:disabled):last", $(e.currentTarget).next()).trigger("click");
      return false;
    };

    EntityDropdown.prototype.editItem = function(e) {
      var btn, item;
      if (this.editing || !this.allowEdit) {
        return;
      }
      item = this.model.get(this.key);
      if (this.multiple) {
        item = item[this.getKey(e)];
      }
      if (!item) {
        return;
      }
      btn = $(e.currentTarget);
      if (btn.is(".form-control")) {
        btn = btn.next().children(".btn-edit");
      }
      btn.prop("disabled", true);
      this.editing = this.reference.load(item.id).done((function(_this) {
        return function(instance) {
          var form;
          _this.editingForm = form = Cruddy.Entity.Form.display(instance);
          form.once("saved", function(model) {
            var _ref, _ref1;
            btn.parent().siblings(".form-control").html(_this.itemBody(model.meta.simplified));
            form.remove();
            return (_ref = _this.selector) != null ? (_ref1 = _ref.dataSource) != null ? _ref1.refresh() : void 0 : void 0;
          });
          form.once("destroyed", function(model) {
            return _this.removeItem(e);
          });
          return form.once("remove", function() {
            return _this.editingForm = null;
          });
        };
      })(this));
      this.editing.always((function(_this) {
        return function() {
          _this.editing = null;
          return btn.prop("disabled", false);
        };
      })(this));
      return this;
    };

    EntityDropdown.prototype.handleSearchKeydown = function(e) {
      if (e.keyCode === 27) {
        if (this.selector) {
          this.selector.$el.dropdown("toggle");
        }
        return false;
      }
    };

    EntityDropdown.prototype.handleItemKeydown = function(e) {
      if (e.keyCode === 13) {
        this.executeFirstAction(e);
        return false;
      }
    };

    EntityDropdown.prototype.applyConstraint = function(reset) {
      var field, value, _ref;
      if (reset == null) {
        reset = false;
      }
      if (this.selector) {
        field = this.model.entity.getField(this.constraint.field);
        value = this.model.get(this.constraint.field);
        if ((_ref = this.selector.dataSource) != null) {
          _ref.set("constraint", field.prepareAttribute(value));
        }
        this.selector.attributesForNewModel[this.constraint.otherField] = value;
      }
      if (reset) {
        this.model.set(this.key, this.multiple ? [] : null);
      }
      return this;
    };

    EntityDropdown.prototype.checkToDisable = function() {
      if (!this.enabled || this.constraint && _.isEmpty(this.model.get(this.constraint.field))) {
        this.disable();
      } else {
        this.enable();
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
      this.$el.toggleClass("disabled", this.disableDropdown);
      return this;
    };

    EntityDropdown.prototype.handleDropdownShow = function(e) {
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
      this.toggleOpenDirection();
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

    EntityDropdown.prototype.handleValueChanged = function() {
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
      if (this.enabled) {
        this.$el.append("<button type=\"button\" class=\"btn btn-default btn-block dropdown-toggle ed-dropdown-toggle\" data-toggle=\"dropdown\" id=\"" + this.cid + "-dropdown\" data-target=\"#" + this.cid + "\">\n    " + Cruddy.lang.choose + "\n    <span class=\"caret\"></span>\n</button>");
      }
      return this.renderItems();
    };

    EntityDropdown.prototype.renderItems = function() {
      var html, key, value, _i, _len, _ref;
      html = "";
      _ref = this.getValue();
      for (key = _i = 0, _len = _ref.length; _i < _len; key = ++_i) {
        value = _ref[key];
        html += this.itemTemplate(this.itemBody(value), key);
      }
      this.items.html(html);
      this.items.toggleClass("has-items", html !== "");
      return this;
    };

    EntityDropdown.prototype.renderSingle = function() {
      this.$el.html(this.itemTemplate(this.placeholder, "0"));
      this.itemTitle = this.$(".form-control");
      this.itemDelete = this.$(".btn-remove");
      this.itemEdit = this.$(".btn-edit");
      return this.updateItem();
    };

    EntityDropdown.prototype.updateItem = function() {
      var value;
      value = this.getValue();
      this.itemTitle.html(value ? this.itemBody(value) : this.placeholder);
      this.itemDelete.toggle(!!value);
      this.itemEdit.toggle(!!value);
      return this;
    };

    EntityDropdown.prototype.itemBody = function(item) {
      var data;
      if (!item.body) {
        data = this.selector.dataSource.getById(item.id);
        if (data) {
          item = data;
        }
      }
      if (item.body) {
        return item.body;
      }
      return item.id;
    };

    EntityDropdown.prototype.itemTemplate = function(value, key) {
      var buttons, html;
      if (key == null) {
        key = null;
      }
      html = "<div class=\"input-group ed-item " + (!this.multiple ? "ed-dropdown-toggle" : "") + "\" data-key=\"" + key + "\">\n    <div class=\"form-control\">" + value + "</div>";
      if (!_.isEmpty(buttons = this.buttonsTemplate())) {
        html += "<div class=\"input-group-btn\">\n    " + buttons + "\n</div>";
      }
      return html += "</div>";
    };

    EntityDropdown.prototype.buttonsTemplate = function() {
      var html;
      html = "";
      if (this.enabled) {
        html += "<button type=\"button\" class=\"btn btn-default btn-remove\" tabindex=\"-1\" title=\"" + Cruddy.lang.reset + "\">\n    " + (b_icon("remove")) + "\n</button>";
      }
      if (this.allowEdit) {
        html += "<button type=\"button\" class=\"btn btn-default btn-edit\" tabindex=\"-1\" title=\"" + Cruddy.lang.edit + "\">\n    " + (b_icon("pencil")) + "\n</button>";
      }
      if (!this.multiple) {
        html += "<button type=\"button\" class=\"btn btn-default btn-dropdown dropdown-toggle\" data-toggle=\"dropdown\" id=\"" + this.cid + "-dropdown\" data-target=\"#" + this.cid + "\" tab-index=\"1\" title=\"" + Cruddy.lang.list_show + "\">\n    " + (b_icon("chevron-down")) + "\n</button>";
      }
      return html;
    };

    EntityDropdown.prototype.focus = function() {
      var $el;
      $el = this.$component("dropdown");
      if (!this.multiple) {
        $el = $el.parent().prev();
      }
      $el[0].focus();
      if (_.isEmpty(this.getValue())) {
        $el.trigger("click");
      }
      return this;
    };

    EntityDropdown.prototype.emptyValue = function() {
      if (this.multiple) {
        return [];
      } else {
        return null;
      }
    };

    EntityDropdown.prototype.dispose = function() {
      var _ref, _ref1;
      if ((_ref = this.selector) != null) {
        _ref.remove();
      }
      if ((_ref1 = this.editingForm) != null) {
        _ref1.remove();
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
      "click .items>.item": "checkItem",
      "click .more": "loadMore",
      "click .btn-add": "showNewForm",
      "click .btn-refresh": "refresh",
      "click [type=search]": function() {
        return false;
      }
    };

    EntitySelector.prototype.initialize = function(options) {
      var _ref, _ref1, _ref2, _ref3;
      EntitySelector.__super__.initialize.apply(this, arguments);
      this.filter = (_ref = options.filter) != null ? _ref : false;
      this.multiple = (_ref1 = options.multiple) != null ? _ref1 : false;
      this.reference = options.reference;
      this.allowSearch = (_ref2 = options.allowSearch) != null ? _ref2 : true;
      this.allowCreate = ((_ref3 = options.allowCreate) != null ? _ref3 : true) && this.reference.createPermitted();
      this.attributesForNewModel = {};
      this.makeSelectedMap(this.getValue());
      if (this.reference.readPermitted()) {
        this.dataSource = this.reference.search({
          ajaxOptions: {
            data: {
              owner: options.owner
            }
          }
        });
        this.listenTo(this.dataSource, "request", this.displayLoading);
        this.listenTo(this.dataSource, "data", this.renderItems);
      }
      return this;
    };

    EntitySelector.prototype.getValue = function() {
      return EntitySelector.__super__.getValue.apply(this, arguments) || (this.multiple ? [] : null);
    };

    EntitySelector.prototype.displayLoading = function(dataSource, xhr) {
      this.$el.addClass("loading");
      xhr.always((function(_this) {
        return function() {
          return _this.$el.removeClass("loading");
        };
      })(this));
      return this;
    };

    EntitySelector.prototype.maybeLoadMore = function() {
      var height;
      height = this.items.parent().height();
      if ((this.$more != null) && height > 0 && height + 50 > this.$more.position().top) {
        this.loadMore();
      }
      return this;
    };

    EntitySelector.prototype.refresh = function(e) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      this.dataSource.refresh();
    };

    EntitySelector.prototype.checkItem = function(e) {
      e.preventDefault();
      e.stopPropagation();
      this.selectItem(this.dataSource.getById($(e.currentTarget).data("id")));
    };

    EntitySelector.prototype.selectItem = function(item) {
      var value;
      if (!item) {
        return;
      }
      if (this.multiple) {
        if (item.id in this.selected) {
          value = _.filter(this.getValue(), function(_item) {
            return _item.id.toString() !== item.id.toString();
          });
        } else {
          value = _.clone(this.getValue());
          value.push(item);
        }
      } else {
        value = item;
      }
      return this.setValue(value);
    };

    EntitySelector.prototype.loadMore = function() {
      if (!this.dataSource || this.dataSource.inProgress()) {
        return;
      }
      this.dataSource.next();
      return false;
    };

    EntitySelector.prototype.showNewForm = function(e) {
      var form, instance;
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      if (this.newModelForm) {
        return;
      }
      instance = this.reference.createInstance({
        attributes: this.attributesForNewModel
      });
      this.newModelForm = form = Cruddy.Entity.Form.display(instance);
      form.once("remove", (function(_this) {
        return function() {
          return _this.newModelForm = null;
        };
      })(this));
      form.once("created", (function(_this) {
        return function(model, resp) {
          var _ref;
          _this.selectItem(model.meta.simplified);
          form.remove();
          if ((_ref = _this.dataSource) != null) {
            _ref.refresh();
          }
        };
      })(this));
      return this;
    };

    EntitySelector.prototype.handleValueChanged = function(newValue) {
      this.makeSelectedMap(newValue);
      return this.renderItems();
    };

    EntitySelector.prototype.makeSelectedMap = function(data) {
      var item, _i, _len;
      this.selected = {};
      if (!data) {
        return this;
      }
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

    EntitySelector.prototype.renderItems = function() {
      var html, item, _i, _len, _ref;
      this.$more = null;
      html = "";
      if (this.dataSource.data.length || this.dataSource.more) {
        _ref = this.dataSource.data;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          item = _ref[_i];
          html += this.renderItem(item);
        }
        if (this.dataSource.more) {
          html += "<li class=\"more\">" + Cruddy.lang.more + "</li>";
        }
      } else {
        html += "<li class=\"empty\">" + Cruddy.lang.no_results + "</li>";
      }
      this.items.html(html);
      if (this.dataSource.more) {
        this.$more = this.items.children(".more");
        this.maybeLoadMore();
      }
      return this;
    };

    EntitySelector.prototype.renderItem = function(item) {
      var className;
      className = item.id in this.selected ? "selected" : "";
      return "<li class=\"item " + className + "\" data-id=\"" + item.id + "\">" + item.body + "</li>";
    };

    EntitySelector.prototype.render = function() {
      if (this.reference.readPermitted()) {
        this.dispose();
        this.$el.html(this.template());
        this.items = this.$(".items");
        this.renderItems();
        this.items.parent().on("scroll", $.proxy(this, "maybeLoadMore"));
        if (this.allowSearch) {
          this.renderSearch();
        }
        if (this.dataSource.isEmpty()) {
          this.dataSource.refresh();
        }
      } else {
        this.$el.html("<span class=error>" + Cruddy.lang.forbidden + "</span>");
      }
      return this;
    };

    EntitySelector.prototype.renderSearch = function() {
      this.searchInput = new Cruddy.Inputs.Search({
        model: this.dataSource,
        key: "keywords"
      });
      this.$el.prepend(this.searchInput.render().$el);
      this.searchInput.$el.wrap("<div class=search-input-container></div>");
      this.searchInput.appendButton("<button type=\"button\" class=\"btn btn-default btn-refresh\" tabindex=\"-1\" title=\"" + Cruddy.lang.refresh + "\">\n    " + (b_icon("refresh")) + "\n</button>");
      if (this.allowCreate) {
        this.searchInput.appendButton("<button type=\"button\" class='btn btn-default btn-add' tabindex='-1' title=\"" + Cruddy.lang.model_new_record + "\">\n    " + (b_icon("plus")) + "\n</button>");
      }
      return this;
    };

    EntitySelector.prototype.template = function() {
      return "<div class=\"items-container\"><ul class=\"items\"></ul></div>";
    };

    EntitySelector.prototype.focus = function() {
      var _ref;
      ((_ref = this.searchInput) != null ? _ref.focus() : void 0) || this.entity.done((function(_this) {
        return function() {
          return _this.searchInput.focus();
        };
      })(this));
      return this;
    };

    EntitySelector.prototype.dispose = function() {
      var _ref, _ref1;
      if ((_ref = this.searchInput) != null) {
        _ref.remove();
      }
      if ((_ref1 = this.newModelForm) != null) {
        _ref1.remove();
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
      "change [type=file]": "handleFilesChanged",
      "click .action-delete": "handleDeleteFile"
    };

    FileList.prototype.initialize = function(options) {
      var _ref;
      this.multiple = (_ref = options.multiple) != null ? _ref : false;
      this.storage = Cruddy.FileStorage.instance(options.storage);
      this.counter = 1;
      this.queue = this.storage.getUploadQueue();
      this.listenTo(this.queue, "started", function() {
        return this.$el.addClass("--loading");
      });
      this.listenTo(this.queue, "stopped", function() {
        return this.$el.removeClass("--loading");
      });
      this.listenTo(this.queue, "progress", function(stats) {
        return this.$uploadProgress.css("width", "" + (stats.uploaded / stats.total * 100) + "%");
      });
      this.listenTo(this.queue, "filestarted", function(file) {
        return this.$uploadProgress.text(file.file.name);
      });
      this.listenTo(this.queue, "filecompleted", function(resp, file) {
        return this.pushFile(resp.path);
      });
      this.listenTo(this.queue, "fileinvalid", function(message, code) {
        return this.showUploadError(Cruddy.lang["upload_error_" + code] || Cruddy.lang.upload_failed);
      });
      this.listenTo(this.queue, "fileerror", function() {
        return this.showUploadError(Cruddy.lang.upload_failed);
      });
      return FileList.__super__.initialize.apply(this, arguments);
    };

    FileList.prototype.showUploadError = function(message) {
      this.$uploadError.text(message).show();
      setTimeout(((function(_this) {
        return function() {
          return _this.$uploadError.hide();
        };
      })(this)), 3000);
      return this;
    };

    FileList.prototype.pushFile = function(file) {
      var value;
      if (this.multiple) {
        value = _.clone(this.getValue());
        value.push(file);
      } else {
        value = file;
      }
      return this.setValue(value);
    };

    FileList.prototype.removeFile = function(file) {
      if (this.multiple) {
        this.setValue(_.reject(this.getValue(), function(item) {
          return item === file;
        }));
      } else {
        this.setValue(null);
      }
      return this;
    };

    FileList.prototype.handleDeleteFile = function(e) {
      e.preventDefault();
      return this.removeFile($(e.currentTarget).data("file"));
    };

    FileList.prototype.handleFilesChanged = function(e) {
      if (!e.target.files.length) {
        return;
      }
      this.queue.push(e.target.files).start();
      return this;
    };

    FileList.prototype.handleValueChanged = function() {
      return this.render();
    };

    FileList.prototype.render = function() {
      var file, files, html, _i, _len;
      html = "";
      if (!_.isEmpty(files = this.getValue())) {
        files = _.isArray(files) ? files : [files];
        for (_i = 0, _len = files.length; _i < _len; _i++) {
          file = files[_i];
          html += this.renderFile(file);
        }
      }
      if (!_.isEmpty(html)) {
        html = this.wrapItems(html);
      }
      html += this.renderInput();
      this.$el.html(html);
      this.$uploadProgress = this.$component("progress");
      this.$uploadError = this.$component("error");
      return this;
    };

    FileList.prototype.wrapItems = function(html) {
      return "<ul class=\"list-group\">" + html + "</ul>";
    };

    FileList.prototype.renderInput = function() {
      var label;
      if (this.multiple) {
        label = "" + (b_icon("plus")) + " " + Cruddy.lang.add;
      } else {
        label = Cruddy.lang.choose;
      }
      return "<div class=\"progress\">\n    <div class=\"progress-bar\" id=\"" + (this.componentId("progress")) + "\"></div>\n</div>\n\n<div class=\"help-block error\" id=\"" + (this.componentId("error")) + "\" style=\"display:none;\"></div>\n\n<div class=\"btn btn-sm btn-default file-list-input-wrap\">\n    <input type=\"file\" id=\"" + (this.componentId("input")) + "\" accept=\"" + (this.getAccept()) + "\" " + (value_if(this.multiple, "multiple")) + ">\n    " + label + "\n</div>";
    };

    FileList.prototype.renderFile = function(file) {
      return "<li class=\"list-group-item\">\n    <a href=\"#\" class=\"action-delete pull-right\" data-cid=\"" + file + "\">\n        <span class=\"glyphicon glyphicon-remove\"></span>\n    </a>\n\n    <a href=\"" + (this.storage.url(file)) + "\" target=\"_blank\">" + file + "</a>\n</li>";
    };

    FileList.prototype.focus = function() {
      this.$component("input")[0].focus();
      return this;
    };

    FileList.prototype.getAccept = function() {
      return "";
    };

    return FileList;

  })(Cruddy.Inputs.Base);

  Cruddy.Inputs.ImageList = (function(_super) {
    __extends(ImageList, _super);

    function ImageList() {
      return ImageList.__super__.constructor.apply(this, arguments);
    }

    ImageList.prototype.className = "file-list --images";

    ImageList.prototype.initialize = function(options) {
      var _ref, _ref1;
      this.width = (_ref = options.width) != null ? _ref : 0;
      this.height = (_ref1 = options.height) != null ? _ref1 : 80;
      return ImageList.__super__.initialize.apply(this, arguments);
    };

    ImageList.prototype.wrapItems = function(html) {
      return "<ul class=\"image-group\">" + html + "</ul>";
    };

    ImageList.prototype.renderFile = function(file) {
      return "<li class=\"image-group-item\">\n    " + (this.renderImage(file)) + "\n\n    <a href=\"#\" class=\"action-delete\" data-cid=\"" + file + "\">\n        <span class=\"glyphicon glyphicon-remove\"></span>\n    </a>\n</li>";
    };

    ImageList.prototype.renderImage = function(file) {
      return "<a href=\"" + (this.storage.url(file)) + "\" target=\"_blank\" class=\"img-wrap\" data-trigger=\"fancybox\">\n    <img src=\"" + (this.storage.url(file, {
        width: this.width,
        height: this.height
      })) + "\">\n</a>";
    };

    ImageList.prototype.getAccept = function() {
      return "image/*,image/jpeg,image/png,image/gif,image/jpeg";
    };

    return ImageList;

  })(Cruddy.Inputs.FileList);

  Cruddy.Inputs.Search = (function(_super) {
    __extends(Search, _super);

    function Search() {
      return Search.__super__.constructor.apply(this, arguments);
    }

    Search.prototype.className = "input-group";

    Search.prototype.events = {
      "click .btn-search": "handleBtnClick"
    };

    Search.prototype.initialize = function(options) {
      this.input = new Cruddy.Inputs.Text({
        model: this.model,
        key: options.key,
        attributes: {
          type: "search",
          placeholder: Cruddy.lang.search
        }
      });
      return Search.__super__.initialize.apply(this, arguments);
    };

    Search.prototype.handleBtnClick = function(e) {
      e.preventDefault();
      e.stopPropagation();
      this.input.submitValue();
    };

    Search.prototype.appendButton = function(btn) {
      return this.$btns.append(btn);
    };

    Search.prototype.render = function() {
      this.$el.append(this.input.render().$el);
      this.$el.append(this.$btns = $("<div class=\"input-group-btn\"></div>"));
      this.appendButton("<button type=\"button\" class=\"btn btn-default btn-search\" title=\"" + Cruddy.lang.find + "\">\n    <span class=\"glyphicon glyphicon-search\"></span>\n</button>");
      return this;
    };

    Search.prototype.focus = function() {
      this.input.focus();
      return this;
    };

    return Search;

  })(Cruddy.View);

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
      this.ref = _.isArray(options.field) ? options.field : options.field ? [options.field] : void 0;
      if (!this.ref) {
        this.$el.removeClass("input-group");
      }
      return Slug.__super__.initialize.apply(this, arguments);
    };

    Slug.prototype.handleValueChanged = function() {
      if (!this.linkable()) {
        this.unlink();
      }
      return this;
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
      modelValue = this.getValue();
      value = this.getSlug();
      return value === modelValue;
    };

    Slug.prototype.sync = function() {
      return this.setValue(this.getSlug());
    };

    Slug.prototype.getSlug = function() {
      var components, key, refValue, _i, _len, _ref;
      components = [];
      _ref = this.ref;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        key = _ref[_i];
        refValue = this.model.get(key);
        if (refValue) {
          components.push(refValue);
        }
      }
      if (components.length) {
        return components.join(" ");
      } else {
        return null;
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

  })(Cruddy.Inputs.Base);

  Cruddy.Inputs.Select = (function(_super) {
    __extends(Select, _super);

    function Select() {
      return Select.__super__.constructor.apply(this, arguments);
    }

    Select.prototype.tagName = "select";

    Select.prototype.initialize = function(options) {
      var _ref, _ref1, _ref2, _ref3;
      this.items = (_ref = options.items) != null ? _ref : {};
      this.prompt = (_ref1 = options.prompt) != null ? _ref1 : null;
      this.required = (_ref2 = options.required) != null ? _ref2 : false;
      this.multiple = (_ref3 = options.multiple) != null ? _ref3 : false;
      if (this.multiple) {
        this.$el.attr("multiple", "multiple");
      }
      return Select.__super__.initialize.apply(this, arguments);
    };

    Select.prototype.render = function() {
      this.$el.html(this.template());
      if (this.required && !this.getValue()) {
        this.setValue(this.$el.val());
      }
      return Select.__super__.render.apply(this, arguments);
    };

    Select.prototype.handleValueChanged = function(newValue, bySelf) {
      if (!bySelf) {
        this.$el.val(this._transformValue(newValue));
      }
      return this;
    };

    Select.prototype._transformValue = function(value) {
      if ((_.isString(value) || _.isArray(value)) && !value.length) {
        return this.emptyValue();
      }
      if (_.isArray(value)) {
        if (this.multiple) {
          return value;
        } else {
          return value[0];
        }
      } else {
        if (this.multiple) {
          return [value];
        } else {
          return value;
        }
      }
    };

    Select.prototype.emptyValue = function() {
      if (this.multiple) {
        return [];
      } else {
        return null;
      }
    };

    Select.prototype.template = function() {
      var html, key, value, _ref, _ref1;
      html = "";
      if (this.hasPrompt()) {
        html += this.optionTemplate("", (_ref = this.prompt) != null ? _ref : Cruddy.lang.not_selected, this.required);
      }
      _ref1 = this.items;
      for (key in _ref1) {
        value = _ref1[key];
        html += this.optionTemplate(key, value);
      }
      return html;
    };

    Select.prototype.optionTemplate = function(value, title, disabled) {
      if (disabled == null) {
        disabled = false;
      }
      return "<option value=\"" + (_.escape(value)) + "\" " + (value_if(disabled, "disabled")) + ">\n    " + (_.escape(title)) + "\n</option>";
    };

    Select.prototype.hasPrompt = function() {
      return !this.multiple && (!this.required || this.prompt);
    };

    return Select;

  })(Cruddy.Inputs.BaseText);

  Cruddy.Inputs.NumberFilter = (function(_super) {
    __extends(NumberFilter, _super);

    function NumberFilter() {
      return NumberFilter.__super__.constructor.apply(this, arguments);
    }

    NumberFilter.prototype.className = "input-group number-filter";

    NumberFilter.prototype.events = {
      "click .dropdown-menu a": "handleOperatorSelected",
      "change": "handleInputChanged"
    };

    NumberFilter.prototype.initialize = function() {
      this.defaultOp = ">";
      if (!this.getValue()) {
        this.setValue(this.emptyValue(), {
          silent: true
        });
      }
      return NumberFilter.__super__.initialize.apply(this, arguments);
    };

    NumberFilter.prototype.handleOperatorSelected = function(e) {
      var op, value;
      e.preventDefault();
      op = $(e.currentTarget).data("op");
      value = this.getValue();
      if (value.op !== op) {
        this.setValue(this.makeValue(op, value.val));
      }
      return this;
    };

    NumberFilter.prototype.handleInputChanged = function(e) {
      var value;
      value = this.getValue();
      this.setValue(this.makeValue(value.op, e.target.value));
      return this;
    };

    NumberFilter.prototype.handleValueChanged = function(value, external) {
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

    NumberFilter.prototype.emptyValue = function() {
      return this.makeValue(this.defaultOp, "");
    };

    return NumberFilter;

  })(Cruddy.Inputs.Base);

  Cruddy.Inputs.DateTime = (function(_super) {
    __extends(DateTime, _super);

    function DateTime() {
      return DateTime.__super__.constructor.apply(this, arguments);
    }

    DateTime.prototype.tagName = "input";

    DateTime.prototype.initialize = function(options) {
      this.format = options.format;
      if (options.mask != null) {
        this.$el.mask(options.mask);
      }
      return DateTime.__super__.initialize.apply(this, arguments);
    };

    DateTime.prototype.handleValueChanged = function(newValue, bySelf) {
      this.$el.val(newValue === null ? "" : !bySelf ? moment.unix(newValue).format(this.format) : void 0);
      return this;
    };

    DateTime.prototype.submitValue = function() {
      var value;
      value = this.$el.val();
      value = _.isEmpty(value) ? null : moment(value, this.format).unix();
      this.setValue(value);
      return this.handleValueChanged(value, true);
    };

    return DateTime;

  })(Cruddy.Inputs.BaseText);

  Cruddy.Fields.BaseView = (function(_super) {
    __extends(BaseView, _super);

    function BaseView(options) {
      var className, field, model, _ref;
      this.field = field = options.field;
      model = options.model;
      this.inputId = [model.entity.id, field.id, model.cid].join("__");
      className = "form-group field field__" + (field.getType()) + " field--" + field.id + " field--" + model.entity.id + "--" + field.id;
      this.className = this.className ? className + " " + this.className : className;
      this.forceDisable = (_ref = options.forceDisable) != null ? _ref : false;
      BaseView.__super__.constructor.apply(this, arguments);
    }

    BaseView.prototype.initialize = function(options) {
      this.listenTo(this.model, "sync", this.handleSync);
      this.listenTo(this.model, "request", this.handleRequest);
      this.listenTo(this.model, "invalid", this.handleInvalid);
      return this.updateContainer();
    };

    BaseView.prototype.handleSync = function() {
      return this.updateContainer();
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

    BaseView.prototype.updateContainer = function() {
      this.isEditable = !this.forceDisable && this.field.isEditable(this.model);
      this.$el.toggle(this.isVisible());
      this.$el.toggleClass("required", this.field.isRequired(this.model));
      return this;
    };

    BaseView.prototype.hideError = function() {
      this.error.hide();
      return this;
    };

    BaseView.prototype.showError = function(message) {
      this.error.text(message).show();
      this.handleValidationError(message);
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
      this.error = this.$component("error");
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
      return "<span class=\"help-block error\" style=\"display:none;\" id=\"" + (this.componentId("error")) + "\"></span>";
    };

    BaseView.prototype.isVisible = function() {
      return this.isEditable || !this.model.isNew();
    };

    BaseView.prototype.isFocusable = function() {
      return this.field.isEditable(this.model);
    };

    BaseView.prototype.dispose = function() {
      return this;
    };

    BaseView.prototype.remove = function() {
      this.dispose();
      return BaseView.__super__.remove.apply(this, arguments);
    };

    return BaseView;

  })(Cruddy.Layout.Element);

  Cruddy.Fields.InputView = (function(_super) {
    __extends(InputView, _super);

    function InputView() {
      return InputView.__super__.constructor.apply(this, arguments);
    }

    InputView.prototype.updateContainer = function() {
      var isEditable;
      isEditable = this.isEditable;
      InputView.__super__.updateContainer.apply(this, arguments);
      if ((isEditable != null) && isEditable !== this.isEditable) {
        return this.render();
      }
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
      this.input = this.field.createInput(this.model, this.inputId, this.forceDisable);
      this.$el.append(this.input.render().el);
      this.$el.append(this.errorTemplate());
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
      var _ref;
      if ((_ref = this.input) != null) {
        _ref.focus();
      }
      return this;
    };

    InputView.prototype.dispose = function() {
      var _ref;
      if ((_ref = this.input) != null) {
        _ref.remove();
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

    Base.prototype.createView = function(model, forceDisable, parent) {
      if (forceDisable == null) {
        forceDisable = false;
      }
      return new this.viewConstructor({
        model: model,
        field: this,
        forceDisable: forceDisable
      }, parent);
    };

    Base.prototype.createInput = function(model, inputId, forceDisable) {
      var input;
      if (forceDisable == null) {
        forceDisable = false;
      }
      if (!forceDisable && this.isEditable(model)) {
        input = this.createEditableInput(model, inputId);
      }
      return input || this.createStaticInput(model);
    };

    Base.prototype.createStaticInput = function(model) {
      return new Cruddy.Inputs.Static({
        model: model,
        key: this.id,
        formatter: this
      });
    };

    Base.prototype.createEditableInput = function(model, inputId) {
      return null;
    };

    Base.prototype.createFilterInput = function(model) {
      return null;
    };

    Base.prototype.getFilterLabel = function() {
      return this.attributes.label;
    };

    Base.prototype.format = function(value) {
      return value || NOT_AVAILABLE;
    };

    Base.prototype.getLabel = function() {
      return this.attributes.label;
    };

    Base.prototype.isEditable = function(model) {
      return model.canBeSaved() && this.attributes.disabled !== true && this.attributes.disabled !== model.action();
    };

    Base.prototype.isRequired = function(model) {
      return this.attributes.required === true || this.attributes.required === model.action();
    };

    Base.prototype.isUnique = function() {
      return this.attributes.unique;
    };

    Base.prototype.hasChangedSinceSync = function(model) {
      return !this.valuesEqual(model.get(this.id), model.getOriginal(this.id));
    };

    Base.prototype.valuesEqual = function(a, b) {
      return a === b;
    };

    Base.prototype.isCopyable = function() {
      return !this.isUnique();
    };

    Base.prototype.copyAttribute = function(model, copy) {
      return model.get(this.id);
    };

    Base.prototype.parse = function(model, value) {
      return value;
    };

    Base.prototype.prepareAttribute = function(value) {
      return value;
    };

    Base.prototype.prepareFilterData = function(value) {
      return this.prepareAttribute(value);
    };

    Base.prototype.parseFilterData = function(value) {
      return value;
    };

    return Base;

  })(Cruddy.Attribute);

  Cruddy.Fields.Input = (function(_super) {
    __extends(Input, _super);

    function Input() {
      return Input.__super__.constructor.apply(this, arguments);
    }

    Input.prototype.createEditableInput = function(model, inputId) {
      var input;
      input = this.createBaseInput(model, inputId);
      if (this.attributes.prepend || this.attributes.append) {
        return new Cruddy.Fields.Input.PrependAppendWrapper({
          prepend: this.attributes.prepend,
          append: this.attributes.append,
          input: input
        });
      }
      return input;
    };

    Input.prototype.createBaseInput = function(model, inputId) {
      return new Cruddy.Inputs.Text({
        model: model,
        key: this.id,
        mask: this.attributes.mask,
        attributes: {
          placeholder: this.attributes.placeholder,
          id: inputId,
          type: this.attributes.input_type || "input"
        }
      });
    };

    Input.prototype.createFilterInput = function(model) {
      return this.createBaseInput(model);
    };

    Input.prototype.format = function(value) {
      if (value === null || value === "") {
        return NOT_AVAILABLE;
      }
      if (this.attributes.append) {
        value += " " + this.attributes.append;
      }
      if (this.attributes.prepend) {
        value = this.attributes.prepend + " " + value;
      }
      return value;
    };

    Input.prototype.prepareAttribute = function(value) {
      if (_.isArray(value)) {
        return value.join(",");
      } else {
        return value;
      }
    };

    Input.prototype.getType = function() {
      return "string";
    };

    return Input;

  })(Cruddy.Fields.Base);

  Cruddy.Fields.Input.PrependAppendWrapper = (function(_super) {
    __extends(PrependAppendWrapper, _super);

    function PrependAppendWrapper() {
      return PrependAppendWrapper.__super__.constructor.apply(this, arguments);
    }

    PrependAppendWrapper.prototype.className = "input-group";

    PrependAppendWrapper.prototype.initialize = function(options) {
      if (options.prepend) {
        this.$el.append(this.createAddon(options.prepend));
      }
      this.$el.append((this.input = options.input).$el);
      if (options.append) {
        return this.$el.append(this.createAddon(options.append));
      }
    };

    PrependAppendWrapper.prototype.render = function() {
      this.input.render();
      return this;
    };

    PrependAppendWrapper.prototype.createAddon = function(text) {
      return "<span class=input-group-addon>" + _.escape(text) + "</span>";
    };

    return PrependAppendWrapper;

  })(Cruddy.View);

  Cruddy.Fields.Text = (function(_super) {
    __extends(Text, _super);

    function Text() {
      return Text.__super__.constructor.apply(this, arguments);
    }

    Text.prototype.createEditableInput = function(model, inputId) {
      return new Cruddy.Inputs.Textarea({
        model: model,
        key: this.id,
        attributes: {
          placeholder: this.attributes.placeholder,
          id: inputId,
          rows: this.attributes.rows
        }
      });
    };

    Text.prototype.getType = function() {
      return "text";
    };

    return Text;

  })(Cruddy.Fields.Base);

  Cruddy.Fields.BaseDateTime = (function(_super) {
    __extends(BaseDateTime, _super);

    function BaseDateTime() {
      return BaseDateTime.__super__.constructor.apply(this, arguments);
    }

    BaseDateTime.prototype.inputFormat = null;

    BaseDateTime.prototype.mask = null;

    BaseDateTime.prototype.createEditableInput = function(model, inputId) {
      return new Cruddy.Inputs.DateTime({
        model: model,
        key: this.id,
        format: this.inputFormat,
        mask: this.mask,
        attributes: {
          id: this.inputId
        }
      });
    };

    BaseDateTime.prototype.formatDate = function(value) {
      return moment.unix(value).format(this.inputFormat);
    };

    BaseDateTime.prototype.format = function(value) {
      if (value === null) {
        return NOT_AVAILABLE;
      } else {
        return this.formatDate(value);
      }
    };

    BaseDateTime.prototype.getType = function() {
      return "datetime";
    };

    return BaseDateTime;

  })(Cruddy.Fields.Base);

  Cruddy.Fields.Date = (function(_super) {
    __extends(Date, _super);

    function Date() {
      return Date.__super__.constructor.apply(this, arguments);
    }

    Date.prototype.inputFormat = "YYYY-MM-DD";

    Date.prototype.mask = "9999-99-99";

    return Date;

  })(Cruddy.Fields.BaseDateTime);

  Cruddy.Fields.Time = (function(_super) {
    __extends(Time, _super);

    function Time() {
      return Time.__super__.constructor.apply(this, arguments);
    }

    Time.prototype.inputFormat = "HH:mm:ss";

    Time.prototype.mask = "99:99:99";

    return Time;

  })(Cruddy.Fields.BaseDateTime);

  Cruddy.Fields.DateTime = (function(_super) {
    __extends(DateTime, _super);

    function DateTime() {
      return DateTime.__super__.constructor.apply(this, arguments);
    }

    DateTime.prototype.inputFormat = "YYYY-MM-DD HH:mm:ss";

    DateTime.prototype.mask = "9999-99-99 99:99:99";

    DateTime.prototype.formatDate = function(value) {
      return moment.unix(value).fromNow();
    };

    return DateTime;

  })(Cruddy.Fields.BaseDateTime);

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

    Boolean.prototype.prepareAttribute = function(value) {
      if (value === false) {
        return 0;
      }
      if (value === true) {
        return 1;
      }
      return null;
    };

    Boolean.prototype.parseFilterData = function(value) {
      value = parseInt(value);
      if (value === 1) {
        return true;
      }
      if (value === 0) {
        return false;
      }
      return null;
    };

    Boolean.prototype.getType = function() {
      return "bool";
    };

    return Boolean;

  })(Cruddy.Fields.Base);

  Cruddy.Fields.BaseRelation = (function(_super) {
    __extends(BaseRelation, _super);

    function BaseRelation() {
      return BaseRelation.__super__.constructor.apply(this, arguments);
    }

    BaseRelation.prototype.isVisible = function() {
      return this.getReferencedEntity().readPermitted() && BaseRelation.__super__.isVisible.apply(this, arguments);
    };

    BaseRelation.prototype.getReferencedEntity = function() {
      if (!this.reference) {
        this.reference = Cruddy.app.entity(this.attributes.reference);
      }
      return this.reference;
    };

    BaseRelation.prototype.getFilterLabel = function() {
      return this.getReferencedEntity().getSingularTitle();
    };

    BaseRelation.prototype.formatItem = function(item) {
      return item.body;
    };

    BaseRelation.prototype.format = function(value) {
      if (_.isEmpty(value)) {
        return NOT_AVAILABLE;
      }
      if (this.attributes.multiple) {
        return _.map(value, (function(_this) {
          return function(item) {
            return _this.formatItem(item);
          };
        })(this)).join(", ");
      } else {
        return this.formatItem(value);
      }
    };

    BaseRelation.prototype.getType = function() {
      return "relation";
    };

    return BaseRelation;

  })(Cruddy.Fields.Base);

  Cruddy.Fields.Relation = (function(_super) {
    __extends(Relation, _super);

    function Relation() {
      return Relation.__super__.constructor.apply(this, arguments);
    }

    Relation.prototype.createInput = function(model, inputId, forceDisable) {
      if (forceDisable == null) {
        forceDisable = false;
      }
      return new Cruddy.Inputs.EntityDropdown({
        model: model,
        key: this.id,
        multiple: this.attributes.multiple,
        reference: this.getReferencedEntity(),
        owner: this.entity.id + "." + this.id,
        constraint: this.attributes.constraint,
        enabled: !forceDisable && this.isEditable(model)
      });
    };

    Relation.prototype.createFilterInput = function(model) {
      return new Cruddy.Inputs.EntityDropdown({
        model: model,
        key: this.id,
        reference: this.getReferencedEntity(),
        allowEdit: false,
        placeholder: Cruddy.lang.any_value,
        owner: this.entity.id + "." + this.id,
        constraint: this.attributes.constraint,
        multiple: true
      });
    };

    Relation.prototype.isEditable = function() {
      return this.getReferencedEntity().readPermitted() && Relation.__super__.isEditable.apply(this, arguments);
    };

    Relation.prototype.canFilter = function() {
      return this.getReferencedEntity().readPermitted() && Relation.__super__.canFilter.apply(this, arguments);
    };

    Relation.prototype.formatItem = function(item) {
      var ref;
      ref = this.getReferencedEntity();
      if (!ref.readPermitted()) {
        return item.body;
      }
      return "<a href=\"" + (ref.link(item.id)) + "\">" + item.body + "</a>";
    };

    Relation.prototype.prepareAttribute = function(value) {
      if (value == null) {
        return null;
      }
      if (_.isArray(value)) {
        return _.pluck(value, "id").join(",");
      }
      return value.id;
    };

    Relation.prototype.prepareFilterData = function(value) {
      value = Relation.__super__.prepareFilterData.apply(this, arguments);
      if (_.isEmpty(value)) {
        return null;
      } else {
        return value;
      }
    };

    Relation.prototype.parseFilterData = function(value) {
      if (!(_.isString(value) || _.isNumber(value))) {
        return null;
      }
      value = value.toString();
      if (!value.length) {
        return null;
      }
      value = value.split(",");
      return _.map(value, function(value) {
        return {
          id: value
        };
      });
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
        storage: this.attributes.storage
      });
    };

    File.prototype.getType = function() {
      return "file";
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
        storage: this.attributes.storage
      });
    };

    Image.prototype.createStaticInput = function(model) {
      return new Cruddy.Inputs.Static({
        model: model,
        key: this.id,
        formatter: new Cruddy.Fields.Image.Formatter({
          width: this.attributes.width,
          height: this.attributes.height,
          storage: this.attributes.storage
        })
      });
    };

    Image.prototype.getType = function() {
      return "image";
    };

    return Image;

  })(Cruddy.Fields.File);

  Cruddy.Fields.Image.Formatter = (function() {
    function Formatter(options) {
      this.options = {
        width: options.width || 0,
        height: options.height || 0
      };
      this.storage = Cruddy.FileStorage.instance(options.storage);
      return;
    }

    Formatter.prototype.imageUrl = function(image) {
      return this.storage.url(image);
    };

    Formatter.prototype.imageThumb = function(image) {
      return this.storage.url(image, this.options);
    };

    Formatter.prototype.format = function(value) {
      var html, image, _i, _len;
      html = "<ul class=\"image-group\">";
      if (!_.isArray(value)) {
        value = [value];
      }
      for (_i = 0, _len = value.length; _i < _len; _i++) {
        image = value[_i];
        html += "<li class=\"image-group-item\">\n    <a href=\"" + (this.imageUrl(image)) + "\" class=\"img-wrap\" target=\"_blank\" data-trigger=\"fancybox\">\n        <img src=\"" + (this.imageThumb(image)) + "\">\n    </a>\n</li>";
      }
      return html + "</ul>";
    };

    return Formatter;

  })();

  Cruddy.Fields.Slug = (function(_super) {
    __extends(Slug, _super);

    function Slug() {
      return Slug.__super__.constructor.apply(this, arguments);
    }

    Slug.prototype.createEditableInput = function(model) {
      return new Cruddy.Inputs.Slug({
        model: model,
        key: this.id,
        field: this.attributes.field,
        attributes: {
          placeholder: this.attributes.placeholder
        }
      });
    };

    Slug.prototype.getType = function() {
      return "slug";
    };

    return Slug;

  })(Cruddy.Fields.Base);

  Cruddy.Fields.Enum = (function(_super) {
    __extends(Enum, _super);

    function Enum() {
      return Enum.__super__.constructor.apply(this, arguments);
    }

    Enum.prototype.createBaseInput = function(model, inputId) {
      return new Cruddy.Inputs.Select({
        model: model,
        key: this.id,
        prompt: this.attributes.prompt,
        items: this.attributes.items,
        required: this.attributes.required,
        multiple: this.attributes.multiple,
        attributes: {
          id: inputId
        }
      });
    };

    Enum.prototype.createFilterInput = function(model) {
      return new Cruddy.Inputs.Select({
        model: model,
        key: this.id,
        prompt: Cruddy.lang.any_value,
        items: this.attributes.items,
        multiple: true
      });
    };

    Enum.prototype.format = function(value) {
      var items, key, labels;
      items = this.attributes.items;
      if (!_.isArray(value)) {
        value = [value];
      }
      labels = (function() {
        var _i, _len, _results;
        _results = [];
        for (_i = 0, _len = value.length; _i < _len; _i++) {
          key = value[_i];
          _results.push(key in items ? items[key] : key);
        }
        return _results;
      })();
      return labels.join(", ");
    };

    Enum.prototype.parseFilterData = function(value) {
      if (_.isString(value)) {
        return value.split(",");
      } else {
        return null;
      }
    };

    Enum.prototype.getType = function() {
      return "enum";
    };

    return Enum;

  })(Cruddy.Fields.Input);

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
      var collection;
      this.views = {};
      this.collection = collection = this.model.get(this.field.id);
      this.listenTo(collection, "add", this.add);
      this.listenTo(collection, "remove", this.removeItem);
      this.listenTo(collection, "removeSoftly restore", this.update);
      this.listenTo(collection, "reset", this.render);
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
      this.collection.add(this.field.getReferencedEntity().createInstance(), {
        focus: true
      });
      return this;
    };

    EmbeddedView.prototype.add = function(model, collection, options) {
      var itemOptions, view;
      itemOptions = {
        model: model,
        collection: this.collection,
        disable: !this.isEditable
      };
      this.views[model.cid] = view = new Cruddy.Fields.EmbeddedItemView(itemOptions, this);
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
      var model, _i, _len, _ref;
      this.dispose();
      this.$el.html(this.template());
      this.body = this.$component("body");
      this.createButton = this.$(".btn-create");
      _ref = this.collection.models;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        model = _ref[_i];
        this.add(model);
      }
      return EmbeddedView.__super__.render.apply(this, arguments);
    };

    EmbeddedView.prototype.update = function() {
      this.createButton.toggle(this.field.isMultiple() || this.collection.hasSpots());
      return this;
    };

    EmbeddedView.prototype.template = function() {
      var buttons;
      buttons = this.canCreate() ? b_btn("", "plus", ["default", "create"]) : "";
      return "<div class='header field-label'>\n    " + (this.helpTemplate()) + (_.escape(this.field.getLabel())) + " " + buttons + "\n</div>\n<div class=\"error-container has-error\">" + (this.errorTemplate()) + "</div>\n<div class=\"body\" id=\"" + (this.componentId("body")) + "\"></div>";
    };

    EmbeddedView.prototype.canCreate = function() {
      return this.isEditable && this.field.getReferencedEntity().createPermitted();
    };

    EmbeddedView.prototype.dispose = function() {
      var cid, view, _ref;
      _ref = this.views;
      for (cid in _ref) {
        view = _ref[cid];
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

    EmbeddedView.prototype.isFocusable = function() {
      if (!EmbeddedView.__super__.isFocusable.apply(this, arguments)) {
        return false;
      }
      return (this.field.isMultiple() && this.canCreate()) || (!this.field.isMultiple() && (this.focusable != null));
    };

    EmbeddedView.prototype.focus = function() {
      var _ref, _ref1;
      if (this.field.isMultiple()) {
        if ((_ref = this.createButton[0]) != null) {
          _ref.focus();
        }
      } else {
        if ((_ref1 = this.focusable) != null) {
          _ref1.focus();
        }
      }
      return this;
    };

    return EmbeddedView;

  })(Cruddy.Fields.BaseView);

  Cruddy.Fields.EmbeddedItemView = (function(_super) {
    __extends(EmbeddedItemView, _super);

    EmbeddedItemView.prototype.className = "has-many-item-view";

    EmbeddedItemView.prototype.events = {
      "click .btn-toggle": "toggleItem"
    };

    function EmbeddedItemView(options) {
      this.collection = options.collection;
      this.listenTo(this.collection, "restore removeSoftly", function(m) {
        if (m !== this.model) {
          return;
        }
        this.$container.toggle(!this.model.isDeleted);
        return this.$btn.html(this.buttonContents());
      });
      EmbeddedItemView.__super__.constructor.apply(this, arguments);
    }

    EmbeddedItemView.prototype.toggleItem = function(e) {
      if (this.model.isDeleted) {
        this.collection.restore(this.model);
      } else {
        this.collection.removeSoftly(this.model);
      }
      return false;
    };

    EmbeddedItemView.prototype.buttonContents = function() {
      if (this.model.isDeleted) {
        return Cruddy.lang.restore;
      } else {
        return b_icon("trash") + " " + Cruddy.lang["delete"];
      }
    };

    EmbeddedItemView.prototype.setupDefaultLayout = function() {
      this.append(new FieldList({}, this));
      return this;
    };

    EmbeddedItemView.prototype.render = function() {
      this.$el.html(this.template());
      this.$container = this.$component("body");
      this.$btn = this.$component("btn");
      return EmbeddedItemView.__super__.render.apply(this, arguments);
    };

    EmbeddedItemView.prototype.template = function() {
      var html;
      html = "<div id=\"" + (this.componentId("body")) + "\"></div>";
      if (!this.disabled && (this.model.entity.deletePermitted() || this.model.isNew())) {
        html += "<button type=\"button\" class=\"btn btn-default btn-sm btn-toggle\" id=\"" + (this.componentId("btn")) + "\">\n    " + (this.buttonContents()) + "\n</button>";
      }
      return html;
    };

    return EmbeddedItemView;

  })(Cruddy.Layout.Layout);

  Cruddy.Fields.RelatedCollection = (function(_super) {
    __extends(RelatedCollection, _super);

    function RelatedCollection() {
      return RelatedCollection.__super__.constructor.apply(this, arguments);
    }

    RelatedCollection.prototype.model = Cruddy.Entity.Instance;

    RelatedCollection.prototype.initialize = function(items, options) {
      this.entity = options.entity;
      this.owner = options.owner;
      this.field = options.field;
      this.maxItems = options.maxItems;
      this.deleted = false;
      this.removedSoftly = 0;
      this.listenTo(this.owner, "sync", function(model, resp, options) {
        this.deleted = false;
        return this._triggerItems("sync", {}, options);
      });
      this.listenTo(this.owner, "request", function(model, xhr, options) {
        return this._triggerItems("request", xhr, options);
      });
      this.listenTo(this.owner, "invalid", this._handleInvalidEvent);
      return RelatedCollection.__super__.initialize.apply(this, arguments);
    };

    RelatedCollection.prototype._handleInvalidEvent = function(model, errors) {
      var attr, cid, error, innerErrors, item, itemErrors, key, match, re;
      re = new RegExp("^" + this.field.id + "\\.(\\d+)\\.(.+)$");
      innerErrors = {};
      for (key in errors) {
        error = errors[key];
        if (!(match = key.match(re))) {
          continue;
        }
        cid = match[1];
        attr = match[2];
        (innerErrors[cid] = innerErrors[cid] || {})[attr] = error;
      }
      console.log(re, innerErrors);
      for (cid in innerErrors) {
        itemErrors = innerErrors[cid];
        if (item = this.get(cid)) {
          item.trigger("invalid", item, itemErrors);
        }
      }
    };

    RelatedCollection.prototype._triggerItems = function(event, param1, param2) {
      var model, _i, _len, _ref;
      _ref = this.models;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        model = _ref[_i];
        model.trigger(event, model, param1, param2);
      }
    };

    RelatedCollection.prototype.add = function() {
      if (this.maxItems && this.models.length >= this.maxItems) {
        this.removeSoftDeleted();
      }
      return RelatedCollection.__super__.add.apply(this, arguments);
    };

    RelatedCollection.prototype.removeSoftDeleted = function() {
      return this.remove(this.filter(function(m) {
        return m.isDeleted;
      }));
    };

    RelatedCollection.prototype.remove = function(models) {
      var item, _i, _len;
      this.deleted = true;
      if (_.isArray(models)) {
        for (_i = 0, _len = models.length; _i < _len; _i++) {
          item = models[_i];
          if (item.isDeleted) {
            this.removedSoftly--;
          }
        }
      } else {
        if (modes.isDeleted) {
          this.removedSoftly--;
        }
      }
      return RelatedCollection.__super__.remove.apply(this, arguments);
    };

    RelatedCollection.prototype.removeSoftly = function(m) {
      if (m.isDeleted) {
        return;
      }
      m.isDeleted = true;
      this.removedSoftly++;
      this.trigger("removeSoftly", m);
      return this;
    };

    RelatedCollection.prototype.restore = function(m) {
      if (!m.isDeleted) {
        return;
      }
      m.isDeleted = false;
      this.removedSoftly--;
      this.trigger("restore", m);
      return this;
    };

    RelatedCollection.prototype.hasSpots = function(num) {
      if (num == null) {
        num = 1;
      }
      return (this.maxItems == null) || this.models.length - this.removedSoftly + num <= this.maxItems;
    };

    RelatedCollection.prototype.hasChangedSinceSync = function() {
      var item, _i, _len, _ref;
      if (this.deleted || this.removedSoftly) {
        return true;
      }
      _ref = this.models;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        item = _ref[_i];
        if (item.hasChangedSinceSync()) {
          return true;
        }
      }
      return false;
    };

    RelatedCollection.prototype.copy = function(copy) {
      var item, items;
      items = this.field.isUnique() ? [] : (function() {
        var _i, _len, _ref, _results;
        _ref = this.models;
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          item = _ref[_i];
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
      var data, item, models, _i, _len;
      models = this.filter(function(model) {
        return model.canBeSaved();
      });
      data = {};
      for (_i = 0, _len = models.length; _i < _len; _i++) {
        item = models[_i];
        data[item.cid] = item.serialize();
      }
      return data;
    };

    return RelatedCollection;

  })(Backbone.Collection);

  Cruddy.Fields.Embedded = (function(_super) {
    __extends(Embedded, _super);

    function Embedded() {
      return Embedded.__super__.constructor.apply(this, arguments);
    }

    Embedded.prototype.viewConstructor = Cruddy.Fields.EmbeddedView;

    Embedded.prototype.parse = function(model, items) {
      var collection, item, ref;
      if (items instanceof Cruddy.Fields.RelatedCollection) {
        return items;
      }
      if (items && !_.isArray(items)) {
        items = [items];
      }
      items = items || [];
      if (_.isEmpty(items) && this.isRequired(model)) {
        items.push({});
      }
      ref = this.getReferencedEntity();
      items = (function() {
        var _i, _len, _results;
        _results = [];
        for (_i = 0, _len = items.length; _i < _len; _i++) {
          item = items[_i];
          _results.push(ref.createInstance(item));
        }
        return _results;
      })();
      if (collection = model.get(this.id)) {
        collection.reset(items);
        return collection;
      }
      return new Cruddy.Fields.RelatedCollection(items, {
        entity: ref,
        owner: model,
        field: this,
        maxItems: this.isMultiple() ? null : 1
      });
    };

    Embedded.prototype.hasChangedSinceSync = function(model) {
      return model.get(this.id).hasChangedSinceSync();
    };

    Embedded.prototype.copy = function(copy, items) {
      return items.copy(copy);
    };

    Embedded.prototype.isMultiple = function() {
      return this.attributes.multiple;
    };

    Embedded.prototype.copyAttribute = function(model, copy) {
      return model.get(this.id).copy(copy);
    };

    Embedded.prototype.prepareAttribute = function(value) {
      if (value) {
        return value.serialize();
      } else {
        return null;
      }
    };

    Embedded.prototype.isCopyable = function() {
      return true;
    };

    Embedded.prototype.getType = function() {
      return "inline-relation";
    };

    return Embedded;

  })(Cruddy.Fields.BaseRelation);

  Cruddy.Fields.Number = (function(_super) {
    __extends(Number, _super);

    function Number() {
      return Number.__super__.constructor.apply(this, arguments);
    }

    Number.prototype.createFilterInput = function(model) {
      return new Cruddy.Inputs.NumberFilter({
        model: model,
        key: this.id
      });
    };

    Number.prototype.prepareFilterData = function(value) {
      if (_.isEmpty(value.val)) {
        return null;
      }
      return (value.op === "=" ? "" : value.op) + value.val;
    };

    Number.prototype.parseFilterData = function(value) {
      var op, val;
      op = ">";
      val = null;
      if (_.isString(value) && value.length) {
        op = value[0];
        if (op === "=" || op === "<" || op === ">") {
          val = value.substr(1);
        } else {
          op = "=";
          val = value;
        }
      } else if (_.isNumber(value)) {
        op = "=";
        val = value;
      }
      return {
        op: op,
        val: val
      };
    };

    Number.prototype.getType = function() {
      return "number";
    };

    return Number;

  })(Cruddy.Fields.Input);

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

    Computed.prototype.getType = function() {
      return "computed";
    };

    return Computed;

  })(Cruddy.Fields.Base);

  Cruddy.Columns.Base = (function(_super) {
    __extends(Base, _super);

    function Base() {
      return Base.__super__.constructor.apply(this, arguments);
    }

    Base.prototype.render = function(item) {
      return this.format(item.attributes[this.id]);
    };

    Base.prototype.format = function(value) {
      return value;
    };

    Base.prototype.getHeader = function() {
      return this.attributes.header;
    };

    Base.prototype.getClass = function() {
      return "col-" + this.id + (this.canOrder() ? " col__sortable" : "");
    };

    Base.prototype.canOrder = function() {
      return this.attributes.can_order;
    };

    return Base;

  })(Cruddy.Attribute);

  Cruddy.Columns.Proxy = (function(_super) {
    __extends(Proxy, _super);

    function Proxy() {
      return Proxy.__super__.constructor.apply(this, arguments);
    }

    Proxy.prototype.initialize = function(attributes) {
      var field, _ref;
      field = (_ref = attributes.field) != null ? _ref : attributes.id;
      this.field = attributes.entity.fields.get(field);
      return Proxy.__super__.initialize.apply(this, arguments);
    };

    Proxy.prototype.format = function(value) {
      return this.field.format(value);
    };

    Proxy.prototype.getClass = function() {
      return Proxy.__super__.getClass.apply(this, arguments) + " col__" + this.field.getType();
    };

    return Proxy;

  })(Cruddy.Columns.Base);

  Cruddy.Columns.Computed = (function(_super) {
    __extends(Computed, _super);

    function Computed() {
      return Computed.__super__.constructor.apply(this, arguments);
    }

    Computed.prototype.getClass = function() {
      return Computed.__super__.getClass.apply(this, arguments) + " col__computed";
    };

    return Computed;

  })(Cruddy.Columns.Base);

  Cruddy.Columns.ViewButton = (function(_super) {
    __extends(ViewButton, _super);

    function ViewButton() {
      return ViewButton.__super__.constructor.apply(this, arguments);
    }

    ViewButton.prototype.id = "__viewButton";

    ViewButton.prototype.getHeader = function() {
      return "";
    };

    ViewButton.prototype.getClass = function() {
      return "col__view-button col__auto-hide";
    };

    ViewButton.prototype.canOrder = function() {
      return false;
    };

    ViewButton.prototype.render = function(model) {
      return this.wrapWithActions(model, "<a onclick=\"Cruddy.app.entityView.displayForm('" + model.meta.id + "', this);return false;\" class=\"btn btn-default btn-view btn-xs auto-hide-target\" href=\"" + (this.entity.link(model.meta.id)) + "\">\n    " + (b_icon("pencil")) + "\n</a>");
    };

    ViewButton.prototype.wrapWithActions = function(item, html) {
      if (_.isEmpty(item.meta.links) && _.isEmpty(item.meta.actions)) {
        return html;
      }
      html = "<div class=\"btn-group btn-group-xs auto-hide-target\">" + html;
      html += this.dropdownToggleTemplate();
      html += this.renderActions(item);
      html += "</div>";
      return html;
    };

    ViewButton.prototype.dropdownToggleTemplate = function() {
      return "<button type=\"button\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\">\n    <span class=\"caret\"></span>\n</button>";
    };

    ViewButton.prototype.renderActions = function(model) {
      var action, html, noPresentationActions, _i, _len, _ref;
      html = "<ul class=\"dropdown-menu\" role=\"menu\">";
      if (!(noPresentationActions = _.isEmpty(model.meta.links))) {
        html += render_presentation_actions(model.meta.links);
      }
      if (!_.isEmpty(model.meta.actions)) {
        if (!noPresentationActions) {
          html += render_divider();
        }
        _ref = model.meta.actions;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          action = _ref[_i];
          html += this.renderAction(action, model);
        }
      }
      html += "</ul>";
      return html;
    };

    ViewButton.prototype.renderAction = function(action, model) {
      return "<li class=\"" + (action.disabled ? "disabled" : "") + "\">\n    <a onclick=\"Cruddy.app.entityView.executeCustomAction('" + action.id + "', '" + model.meta.id + "', this);return false;\" href=\"javascript:void;\">\n        " + (_.escape(action.title)) + "\n    </a>\n</li>";
    };

    return ViewButton;

  })(Cruddy.Columns.Base);

  Cruddy.Columns.DeleteButton = (function(_super) {
    __extends(DeleteButton, _super);

    function DeleteButton() {
      return DeleteButton.__super__.constructor.apply(this, arguments);
    }

    DeleteButton.prototype.id = "__deleteButton";

    DeleteButton.prototype.getHeader = function() {
      return "";
    };

    DeleteButton.prototype.getClass = function() {
      return "col__delete-button col__button col__auto-hide";
    };

    DeleteButton.prototype.canOrder = function() {
      return false;
    };

    DeleteButton.prototype.render = function(item) {
      return "<a href=\"#\" data-action=\"deleteItem\" class=\"btn btn-default btn-xs auto-hide-target\">\n    " + (b_icon("trash")) + "\n</a>";
    };

    return DeleteButton;

  })(Cruddy.Columns.Base);

  Cruddy.Filters.Base = (function(_super) {
    __extends(Base, _super);

    function Base() {
      return Base.__super__.constructor.apply(this, arguments);
    }

    Base.prototype.getLabel = function() {
      return this.attributes.label;
    };

    Base.prototype.getClass = function() {
      return "filter filter__" + this.attributes.type + " filter--" + this.id;
    };

    Base.prototype.createFilterInput = function() {
      throw "Implement required";
    };

    Base.prototype.prepareData = function(value) {
      return value;
    };

    Base.prototype.parseData = function(value) {
      return value;
    };

    Base.prototype.getDataKey = function() {
      return this.get("data_key") || this.id;
    };

    return Base;

  })(Cruddy.Attribute);

  Cruddy.Filters.Proxy = (function(_super) {
    __extends(Proxy, _super);

    function Proxy() {
      return Proxy.__super__.constructor.apply(this, arguments);
    }

    Proxy.prototype.initialize = function(attributes) {
      var field, _ref;
      field = (_ref = attributes.field) != null ? _ref : attributes.id;
      this.field = attributes.entity.fields.get(field);
      return Proxy.__super__.initialize.apply(this, arguments);
    };

    Proxy.prototype.createFilterInput = function(model) {
      return this.field.createFilterInput(model);
    };

    Proxy.prototype.prepareData = function(value) {
      return this.field.prepareFilterData(value);
    };

    Proxy.prototype.parseData = function(value) {
      return this.field.parseFilterData(value);
    };

    return Proxy;

  })(Cruddy.Filters.Base);

  App = (function(_super) {
    __extends(App, _super);

    function App() {
      return App.__super__.constructor.apply(this, arguments);
    }

    App.prototype.initialize = function() {
      this.container = $("body");
      this.mainContent = $("#content");
      this.loadingRequests = 0;
      this.entities = {};
      this.dfd = $.Deferred();
      this.$title = $("title");
      this.$error = $(this.errorTemplate()).appendTo(this.container);
      this.$error.on("click", ".close", (function(_this) {
        return function() {
          return _this.$error.stop(true).fadeOut();
        };
      })(this));
      this.on("change:entity", this.displayEntity, this);
      $(document).ajaxError((function(_this) {
        return function(event, xhr, xhrOptions) {
          return _this.handleAjaxError(xhr, xhrOptions);
        };
      })(this));
      $(window).on("beforeunload", (function(_this) {
        return function() {
          return _this.pageUnloadConfirmationMessage();
        };
      })(this));
      return this;
    };

    App.prototype.errorTemplate = function() {
      return "<p class=\"alert alert-danger cruddy-global-error\">\n    <button type=\"button\" class=\"close\">&times;</button>\n    <span class=\"data\"></span>\n</p>";
    };

    App.prototype.init = function() {
      this._loadSchema();
      return this;
    };

    App.prototype.ready = function(callback) {
      return this.dfd.done(callback);
    };

    App.prototype._loadSchema = function() {
      var req;
      req = $.ajax({
        url: Cruddy.schemaUrl,
        displayLoading: true
      });
      req.done((function(_this) {
        return function(resp) {
          var entity, modelClass, _i, _len;
          for (_i = 0, _len = resp.length; _i < _len; _i++) {
            entity = resp[_i];
            modelClass = get(entity.model_class) || Cruddy.Entity.Entity;
            _this.entities[entity.id] = new modelClass(entity);
          }
          _this.dfd.resolve(_this);
          $(document).trigger("started.cruddy", _this);
        };
      })(this));
      req.fail((function(_this) {
        return function() {
          _this.dfd.reject();
          _this.displayError(Cruddy.lang.schema_failed);
        };
      })(this));
      return req;
    };

    App.prototype.displayEntity = function(model, entity) {
      this.dispose();
      this.mainContent.hide();
      if (entity) {
        this.container.append((this.entityView = entity.createController()).render().el);
      }
      return this.updateTitle();
    };

    App.prototype.displayError = function(error) {
      this.dispose();
      this.mainContent.html("<p class='alert alert-danger'>" + error + "</p>").show();
      return this;
    };

    App.prototype.displayActionResult = function(result) {
      var message;
      console.log(result);
      message = result.message;
      if (!message) {
        message = result.successful ? Cruddy.lang.action_applied : Cruddy.lang.action_failed;
      }
      alert(message);
      return this;
    };

    App.prototype.handleAjaxError = function(xhr) {
      var error, _ref;
      if (xhr.status === VALIDATION_FAILED_CODE) {
        return;
      }
      if ((_ref = xhr.responseJSON) != null ? _ref.error : void 0) {
        if (_.isObject(error = xhr.responseJSON.error)) {
          error = error.type + ": " + error.message;
        }
      } else {
        error = "Unknown error occurred";
      }
      this.$error.children(".data").text(error).end().stop(true).fadeIn();
    };

    App.prototype.pageUnloadConfirmationMessage = function() {
      var _ref;
      return (_ref = this.entityView) != null ? _ref.pageUnloadConfirmationMessage() : void 0;
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
      if (!(id in this.entities)) {
        throw "Unknown entity " + id;
      }
      return this.entities[id];
    };

    App.prototype.dispose = function() {
      var _ref;
      if ((_ref = this.entityView) != null) {
        _ref.remove();
      }
      this.entityView = null;
      return this;
    };

    App.prototype.updateTitle = function() {
      var title;
      title = Cruddy.brandName;
      if (this.entityView != null) {
        title = this.entityView.getPageTitle() + TITLE_SEPARATOR + title;
      }
      this.$title.text(title);
      return this;
    };

    return App;

  })(Backbone.Model);

  Router = (function(_super) {
    __extends(Router, _super);

    function Router() {
      return Router.__super__.constructor.apply(this, arguments);
    }

    Router.prototype.initialize = function() {
      var entities, history, root;
      this.query = $.query;
      entities = Cruddy.entities;
      this.addRoute("index", entities);
      root = Cruddy.baseUrl;
      history = Backbone.history;
      $(document).on("click", "a", (function(_this) {
        return function(e) {
          var fragment, handler, _i, _len, _ref;
          if (e.isDefaultPrevented()) {
            return;
          }
          fragment = e.currentTarget.href;
          if (fragment.indexOf(root) !== 0) {
            return;
          }
          fragment = history.getFragment(fragment.slice(root.length));
          _ref = history.handlers;
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            handler = _ref[_i];
            if (!(handler.route.test(fragment))) {
              continue;
            }
            e.preventDefault();
            history.navigate(fragment, {
              trigger: true
            });
            break;
          }
        };
      })(this));
      return this;
    };

    Router.prototype.execute = function() {
      this.query = $.query.parseNew(location.search);
      return Router.__super__.execute.apply(this, arguments);
    };

    Router.prototype.navigate = function(fragment) {
      this.query = this.query.load(fragment);
      return Router.__super__.navigate.apply(this, arguments);
    };

    Router.prototype.getQuery = function(key) {
      return this.query.GET(key);
    };

    Router.prototype.setQuery = function(key, value, options) {
      return this.updateQuery(this.query.set(key, value), options);
    };

    Router.prototype.refreshQuery = function(params, defaults, options) {
      var key, query, value;
      if (defaults == null) {
        defaults = {};
      }
      query = this.query.copy();
      for (key in params) {
        value = params[key];
        if (value === null || (key in defaults && value === defaults[key])) {
          query.REMOVE(key);
        } else {
          query.SET(key, value);
        }
      }
      return this.updateQuery(query, options);
    };

    Router.prototype.removeQuery = function(key, options) {
      return this.updateQuery(this.query.remove(key), options);
    };

    Router.prototype.updateQuery = function(query, options) {
      var path, qs, uri;
      if ((qs = query.toString()) !== this.query.toString()) {
        this.query = query;
        path = location.pathname;
        uri = "/" + Cruddy.uri + "/";
        if (path.indexOf(uri) === 0) {
          path = path.slice(uri.length);
        }
        Backbone.history.navigate(path + qs, options);
      }
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
      route += "(\\?.*)?$";
      this.route(new RegExp(route), name);
      return this;
    };

    Router.prototype.resolveEntity = function(id, callback) {
      return Cruddy.ready(function(app) {
        var entity;
        entity = app.entity(id);
        if (entity.readPermitted()) {
          Cruddy.app.set("entity", entity);
          if (callback) {
            callback.call(this, entity);
          }
        } else {
          Cruddy.app.displayError(Cruddy.lang.entity_forbidden);
        }
      });
    };

    Router.prototype.index = function(entity) {
      return this.resolveEntity(entity);
    };

    return Router;

  })(Backbone.Router);

  $(function() {
    Cruddy.router = new Router;
    return Backbone.history.start({
      root: Cruddy.getHistoryRoot(),
      pushState: true,
      hashChange: false
    });
  });

  $(document).on("started.cruddy", function(e, app) {
    var $navbar, changeEntity;
    $navbar = $(".navbar");
    changeEntity = function(entity) {
      var $el;
      $navbar.find(".navbar-nav li.active").removeClass("active");
      if (entity == null) {
        return;
      }
      $el = $navbar.find(".navbar-nav [data-entity=" + entity.id + "]");
      $el.addClass("active");
      return $el.find(".badge").fadeOut();
    };
    app.on("change:entity", function(app, entity) {
      changeEntity(entity);
    });
    changeEntity(app.get("entity"));
  });

}).call(this);

//# sourceMappingURL=app.js.map
