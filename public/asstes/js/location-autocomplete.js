(($, settings, namespace) => {
  if (!namespace || typeof namespace.init_autocomplete !== "function") {
    return;
  }

  $(() =>
    namespace.init_autocomplete({
      $input: $(settings.input_selector),
      $value_input: $(settings.value_selector),
      settings: settings,
    })
  );
})(jQuery, rideshare_location_autocomplete_data, window.rideshare_autocomplete);
