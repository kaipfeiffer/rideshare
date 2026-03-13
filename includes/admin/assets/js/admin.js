const rideshare_admin = (($, settings) => {
  const classes = {};
  const libraries = {};

  function add_lib(lib_name, script, params) {
    params = params ?? {};
    classes[lib_name] = script;
    libraries[lib_name] = new script(params);
  }

  function get_lib(lib_name, path, as_class) {
    if (!(libraries[lib_name] ?? null)) {
      let default_path = settings.path ?? "";
      path = path ?? `${default_path}${lib_name}.js`;
      console.log(path);
      $.ajax({
        async: false,
        // Im Produktiv-Modus Cache anschalten:
        // cache: true,
        url: path,
        dataType: "script",
        success: (data) => {
          if (!libraries[lib_name]) {
            libraries[lib_name] = null;
          }
        },
      });
    }

    if (as_class ?? null) {
      return classes[lib_name];
    }
    return libraries[lib_name];
  }

  function tab_click_handler(e) {
    let $tablist = $(e.currentTarget).closest("ul");
    let $tab = $(e.currentTarget).closest("li");
    let data_store = $(e.currentTarget).closest("li").attr("data-store");

    // console.log(lib_regex,tabpanel_id,lib_name);

    $tablist.find("li").each(function (i) {
      $this = $(this);
      $("#" + $this.attr("aria-controls")).attr("hidden", true);
      $this.attr("aria-selected", false).removeClass("active");
    });

    $tab.addClass("active").attr("aria-selected", true);
    $("#" + $tab.attr("aria-controls")).removeAttr("hidden");

    // alert(settings.path + "\n" + settings.prefix + "\n" + tabpanel_id);

    console.log("data_store", data_store);
    if (+data_store) {
      let tabpanel_id = $(e.currentTarget).closest("li").attr("aria-controls");
      let lib_regex = new RegExp(`${settings.prefix}_(\\w+)_content`);
      let lib_name = lib_regex.exec(tabpanel_id)[1] ?? "";
      let lib = get_lib(lib_name + "-tab");
      let name = lib.get();

      console.log(name, settings);
      $.ajax({
        url: settings.ajaxurl,
        dataType: "json",
        type: 'POST',
        data: {
          action: settings.action,
          target: settings.target,
          table: lib_name,
        },
        success: (data) => {
          console.log(data.html);
            $("#"+tabpanel_id).html(data.html);
        },
      });
    }
  }

  $(document).ready(() => {
    settings.prefix = settings.prefix ?? "";
    $(".rideshare_tab_headers li a").on("click", tab_click_handler);
  });
  return {
    add_lib: add_lib,
    get_lib: get_lib,
  };
})(jQuery, rideshare_admin_data);
