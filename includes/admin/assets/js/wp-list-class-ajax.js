(function ($, settings) {
  var current_page;
  var pagination_aria_labels = settings.pagination_aria_labels;
  var pagination_href;
  var pagination_labels;
  var $pagination_inactive;
  var $pagination_link;
  var timer;
  var total_pages;
  var url_params;

  function pagination_click_handler(e, param) {
    param = param ?? {};
    console.log(param, param?.no_prevent);
    if (!param?.no_prevent) {
      // We don't want to actually follow these links
      e.preventDefault();
      // Simple way: use the URL to extract our needed variables
      var url = $(e.currentTarget).attr("href");
      var data = {
        paged: get_url_param(url, "paged") || "1",
      };
      update(data, e);
    } else {
      return true;
    }
  }

  function pagination_keyup_handler(e, param) {
    param = param ?? {};

    if (!param?.no_prevent) {
      if (13 == e.which) e.preventDefault();

      // This time we fetch the variables in inputs
      var data = {
        paged: parseInt($("input[name=paged]").val()) || "1",
      };

      // dom't update data immediately
      window.clearTimeout(timer);
      timer = window.setTimeout(function () {
        update(data, e);
      }, 500);
    }
  }

  function search_submit_handler(e, param) {
    param = param ?? {};
    console.log(
      `#${settings.search_input_id}-search-input`,
      $(`#${settings.search_input_id}-search-input`)
    );
    var search_string =
      encodeURI($(`#${settings.search_input_id}-search-input`).val()) || "";

    if (!search_string) {
      return false;
    }
    if (!param?.no_prevent) {
      e.preventDefault();

      var data = {
        paged: 0,
        s: search_string,
      };

      update(data, e);
    }
  }

  function assign_handlers() {
    // This will have its utility when dealing with the page number input
    var timer;
    var delay = 500;

    if (!$pagination_link) {
      $pagination_link = $(".tablenav-pages a").first().clone();
      $pagination_link.removeClass("first-page prev-page nect-page last-page");
      $pagination_inactive = $(".tablenav-pages .button.disabled")
        .first()
        .clone();
      console.log($pagination_inactive);
      pagination_href = $pagination_link.attr("href");
      $(".tablenav.top .pagination-links [aria-hidden=true]").each(function (
        index
      ) {
        if (!pagination_labels) {
          pagination_labels = [];
        }
        pagination_labels[index] = $(this).text();
      });
    }
    console.log($(".search-box > .search-submit"));
    $("#search-submit").closest("form").on("submit", search_submit_handler);

    $(".tablenav-pages a").on("click", pagination_click_handler);

    $("input[name=paged]").on("keyup", pagination_keyup_handler);
  }

  /**
   * update
   *
   * @param   object  data The data to pass through AJAX
   * @param   event   the event to trigger default actions on failure
   */
  function update(data, e) {
    data[settings.nonce_field] = $(`#${settings.nonce_field}`).val();
    data = {
      ...url_params,
      ...data,
      action: settings.action,
      class: settings.class,
      target: settings.ajax_target,
    };
    $.ajax({
      url: ajaxurl,
      data: data,

      // Handle the successful result
      success: function (response) {
        // WP_List_Table::ajax_response() returns json
        var response = $.parseJSON(response);

        current_page = response.current_page;
        total_pages = response.total_pages;

        console.log(response);

        if (response.rows.length) {
          console.log($(".wp-list-table thead .hidden"));

          var $response_rows = $("<div></div>").append($(response.rows));
          $(".wp-list-table thead .hidden").each(function () {
            console.log(this.id, $(response.rows).find(`.${this.id}`));
            $response_rows.find(`.${this.id}`).addClass("hidden");
          });
          $("#the-list").html($response_rows.html());
        }

        console.log($(".tablenav.top .pagination-links .button"));
        $(".tablenav.top .pagination-links .button").each(set_pagination_links);
        $(".tablenav.bottom .pagination-links .button").each(
          set_pagination_links
        );
        $("#current-page-selector").val(current_page);
        $(".tablenav-paging-text").html(
          `${current_page} of <span class="total-pages">${total_pages}</span></span>`
        );

        $(".tablenav-pages .displaying-num").html(response.total_items_i18n);
        // Init back our event handlers
        assign_handlers();
      },
      error: function () {
        console.log(
            $(e.currentTarget)[0].nodeName,
          $(e.currentTarget).attr("name"),
          `${settings.search_input_id}-search-input`
        );
        switch ($(e.currentTarget)[0].nodeName) {
          case "INPUT": {
            switch ($(e.currentTarget).attr("name")) {
              case "paged": {
                if (13 === e.which) {
                  let new_location,
                    location = window.location.href;
                  console.log(e.which, location, $(e.currentTarget));
                  alert("Val:" + $(e.currentTarget).val());
                  new_location = location.replace(
                    /(paged=)\d+/,
                    "$1" + (parseInt($(e.currentTarget).val()) || "1")
                  );
                  if (new_location === location) {
                    new_location +=
                      "&paged=" + (parseInt($(e.currentTarget).val()) || "1");
                  }
                  window.location = new_location;
                }
                break;
              }
            }
            break;
          }
          case "A": {
            $(e.currentTarget).trigger("click", { no_prevent: true });
            // console.log($(e.currentTarget).attr("href"));
            window.location = $(e.currentTarget).attr("href");
            break;
          }
          case "FORM":{
              alert("Search");
              $(e.currentTarget).trigger("submit", { no_prevent: true });
              break;
            }
          default:
        }
      },
    });
  }

  function get_url_param(url, key) {
    url = new URL(url)?.searchParams;
    if (url) {
      return url.get(key);
    }
    return false;
  }

  function set_pagination_links(index) {
    let $link = $pagination_inactive.clone();
    switch (index) {
      case 0: {
        if (current_page > 2) {
          $link = $pagination_link.clone().addClass("first-page");
          $link.attr("href", $link.attr("href").replace(/\&paged=\d+/, ""));
          $link.find("[aria-hidden=true]").html(pagination_labels[index]);
        } else {
          $link.html(pagination_labels[index]);
        }
        break;
      }
      case 1: {
        if (current_page > 1) {
          $link = $pagination_link.clone().addClass("previous-page");
          $link.attr(
            "href",
            $link.attr("href").replace(/(paged=)\d+/, "$1" + (current_page - 1))
          );
          $link.find("[aria-hidden=true]").html(pagination_labels[index]);
        } else {
          $link.html(pagination_labels[index]);
        }
        break;
      }
      case 2: {
        if (current_page < total_pages) {
          $link = $pagination_link.clone().addClass("next-page");
          $link.attr(
            "href",
            $link.attr("href").replace(/(paged=)\d+/, "$1" + (current_page + 1))
          );
          $link.find("[aria-hidden=true]").html(pagination_labels[index]);
        } else {
          $link.html(pagination_labels[index]);
        }
        break;
      }
      case 3: {
        if (total_pages - current_page >= 2) {
          $link = $pagination_link.clone().addClass("last-page");
          $link.attr(
            "href",
            $link.attr("href").replace(/(paged=)\d+/, "$1" + total_pages)
          );
          $link.find("[aria-hidden=true]").html(pagination_labels[index]);
        } else {
          $link.html(pagination_labels[index]);
        }
        break;
      }
    }
    console.log($link);
    $link.find(".screen-reader-text").html(pagination_aria_labels[index]);
    $(this).replaceWith($link.clone());
  }

  // Show time!
  $(document).ready(() => {
    if (!url_params) {
      url_params = {};
    }
    new URL(window.location)?.searchParams?.forEach((value, key) => {
      url_params[key] = value;
    });
    assign_handlers();
    console.log("loaded", settings, url_params, pagination_labels);
  });
})(jQuery, rideshare_admin_subpage_data);
