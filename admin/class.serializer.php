<?php
class Serializer {
    private $import;

    public function __construct( $import ) {
        $this->import = $import;
    }

    public function init() {
       add_action( 'admin_post', array( $this, 'save' ) );
    }
 
    public function save() { 
        // if ( ! ( $this->has_valid_nonce() && current_user_can( 'manage_options' ) ) ) {
        //     // TODO: Display an error message.
        // }

        $result = $this->import->importPrice($_FILES['file']);        
        echo $result;
        // $this->redirect();
    }

    private function has_valid_nonce() {
        if ( ! isset( $_POST['nonce-import-url'] ) ) {
            return false;
        }
     
        $field  = wp_unslash( $_POST['nonce-import-url'] );
        $action = 'import-xml-process';
     
        return wp_verify_nonce($field, $action);
    }

    private function redirect() {
        if ( ! isset( $_POST['_wp_http_referer'] ) ) {
            $_POST['_wp_http_referer'] = wp_login_url();
        }

        $url = sanitize_text_field(
                wp_unslash( $_POST['_wp_http_referer'] )
        );
 
        wp_safe_redirect( urldecode( $url ) );
        exit;
    }
}
?>