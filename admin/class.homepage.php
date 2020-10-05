<?php
class HomePage {
    public function __construct(  ) {
    }
    
    public function render() {
        include_once(MAIN_DIR.'/views/home.php');
    }
}
?>