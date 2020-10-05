<?php
class Menu {
    private $home_page;

    public function __construct( $home_page ) {
        $this->home_page = $home_page;
    }
 
    public function init() {
         add_action('admin_menu', array($this, 'add_menu'));
    }

    public function add_menu() {
        add_menu_page( 
            'Import Price Data', 
            'Import Price Data', 
            'manage_options', 
            'import-price-plugin', 
            array($this->home_page, 'render')
        );
    }
}
?>