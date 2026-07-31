(function ($, settings) {
  var request_timer;
  var url_params = {};

  function assign_handlers() {
    get_search_form().off("submit.wpbaseListAjax").on("submit.wpbaseListAjax", search_submit_handler);
    $(".tablenav-pages a").off("click.wpbaseListAjax").on("click.wpbaseListAjax", pagination_click_handler);
    $("input[name=paged]").off("keyup.wpbaseListAjax").on("keyup.wpbaseListAjax", pagination_keyup_handler);
  }

  function pagination_click_handler(e) {
    e.preventDefault();

    update({
      paged: get_url_param($(e.currentTarget).attr("href"), "paged") || "1",
    }, e);
  }

  function pagination_keyup_handler(e) {
    if (13 === e.which) {
      e.preventDefault();
    }

    window.clearTimeout(request_timer);
    request_timer = window.setTimeout(function () {
      update({
        paged: parseInt($(e.currentTarget).val(), 10) || "1",
      }, e);
    }, 500);
  }

  function search_submit_handler(e) {
    e.preventDefault();

    update({
      paged: "1",
      s: get_search_input().val() || "",
    }, e);
  }

  function update(data, e) {
    var request_data = $.extend({}, url_params, data, {
      action: settings.action,
      class: settings.class,
      target: settings.ajax_target,
    });

    request_data[settings.nonce_field] = $("#" + settings.nonce_field).val();

    $.ajax({
      url: settings.ajaxurl || window.ajaxurl,
      data: request_data,
      success: function (response) {
        render_response(parse_response(response));
        update_url_params(data);
        assign_handlers();
      },
      error: function () {
        run_default_action(e);
      },
    });
  }

  function render_response(response) {
    if (!response) {
      return;
    }

    if (typeof response.rows === "string") {
      $("#the-list").html(response.rows);
    }

    if (response.column_headers) {
      render_column_headers(response.column_headers);
    }

    if (response.pagination) {
      render_pagination(response.pagination);
    } else {
      render_legacy_pagination(response);
    }
  }

  function render_column_headers(column_headers) {
    if (typeof column_headers.top === "string") {
      $(".wp-list-table thead tr").html(column_headers.top);
    }

    if (typeof column_headers.bottom === "string") {
      $(".wp-list-table tfoot tr").html(column_headers.bottom);
    }
  }

  function render_pagination(pagination) {
    if (typeof pagination.top === "string") {
      $(".tablenav.top .tablenav-pages").replaceWith(pagination.top);
    }

    if (typeof pagination.bottom === "string") {
      $(".tablenav.bottom .tablenav-pages").replaceWith(pagination.bottom);
    }
  }

  function render_legacy_pagination(response) {
    if (response.current_page) {
      $("#current-page-selector").val(response.current_page);
    }

    if (response.total_pages) {
      $(".tablenav-paging-text").html(
        response.current_page + ' of <span class="total-pages">' + response.total_pages + "</span>"
      );
    }

    if (response.total_items_i18n) {
      $(".tablenav-pages .displaying-num").html(response.total_items_i18n);
    }
  }

  function parse_response(response) {
    if ("string" !== typeof response) {
      return response;
    }

    try {
      return $.parseJSON(response);
    } catch (error) {
      return null;
    }
  }

  function update_url_params(data) {
    $.each(data, function (key, value) {
      if (value) {
        url_params[key] = value;
      } else {
        delete url_params[key];
      }
    });
  }

  function run_default_action(e) {
    if (!e || !e.currentTarget) {
      return;
    }

    if ("A" === e.currentTarget.nodeName) {
      window.location = $(e.currentTarget).attr("href");
      return;
    }

    if ("FORM" === e.currentTarget.nodeName) {
      e.currentTarget.submit();
    }
  }

  function get_search_form() {
    return get_search_input().closest("form");
  }

  function get_search_input() {
    return $("#" + settings.search_input_id + "-search-input");
  }

  function get_url_param(url, key) {
    var parsed_url;

    try {
      parsed_url = new URL(url, window.location.href);
    } catch (error) {
      return false;
    }

    return parsed_url.searchParams.get(key);
  }

  $(document).ready(function () {
    new URL(window.location.href).searchParams.forEach(function (value, key) {
      url_params[key] = value;
    });

    assign_handlers();
  });
})(jQuery, rideshare_admin_subpage_data);
