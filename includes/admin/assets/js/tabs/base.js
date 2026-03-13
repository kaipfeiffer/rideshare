((admin, $) => {
    // let cms_base_class = admin.get_lib("base", 1);
    class base_class {
        constructor(config){

        }

        get(page){
            return this.name;
        }
    }
    admin.add_lib("base", base_class);
})(rideshare_admin, jQuery)