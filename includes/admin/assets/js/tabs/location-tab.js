((admin, $) => {
    let base_class = admin.get_lib("base", null, 1);
    class location_class extends base_class {
        constructor(config){
            super (config);
            this.name = "Location";
        }
    }
    admin.add_lib("location-tab", location_class);
})(rideshare_admin, jQuery)