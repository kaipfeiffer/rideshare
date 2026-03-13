((admin, $) => {
  let base_class = admin.get_lib("base", null, 1);
  class user_class extends base_class {
    constructor(config) {
      super(config);
      this.name = "User";
    }
  }
  admin.add_lib("user-tab", user_class);
})(rideshare_admin, jQuery);
