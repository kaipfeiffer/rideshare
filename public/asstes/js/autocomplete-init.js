((window, $) => {
  const namespace = (window.rideshare_autocomplete =
    window.rideshare_autocomplete || {});

  namespace.init_autocomplete = (params) => {
    const settings = params.settings ?? {};
    const $input = params.$input;
    const $value_input = params.$value_input;
    const selected_label_attr = settings.selected_label_attr ?? "data-selected-label";

    if (!$input.length || !$value_input.length || !$.fn.autocomplete) {
      return;
    }

    $input.autocomplete({
      minLength: settings.min_length ?? settings.minLength ?? 2,
      source: (request, response) => {
        $.ajax({
          url: settings.ajaxurl,
          dataType: "json",
          type: "POST",
          data: {
            action: settings.action,
            target: settings.target,
            class: settings.class,
            nonce: settings.nonce,
            term: request.term,
          },
          success: (items) => {
            response(items);
          },
          error: () => {
            response([]);
          },
        });
      },
      select: (event, ui) => {
        $value_input.val(ui.item.id);
        $input.attr(selected_label_attr, ui.item.label);
      },
      change: () => {
        if ($input.val() !== $input.attr(selected_label_attr)) {
          $value_input.val("");
        }
      },
    });

    $input.on("input", () => {
      if ($input.val() !== $input.attr(selected_label_attr)) {
        $value_input.val("");
      }
    });
  };
})(window, jQuery);
