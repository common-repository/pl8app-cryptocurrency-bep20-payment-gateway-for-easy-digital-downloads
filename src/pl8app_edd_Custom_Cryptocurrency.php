<?php


class pl8app_edd_Custom_Cryptocurrency{

    var $parameters;
    var $post_type;

    function __construct()
    {
        $labels = array(
            'name' => __('Crypto Currencies', 'pl8app-cryptocurrency-bep20-payment-gateway-for-easy-digital-downloads'),
            'singular_name' => __('Crypto Currency', 'pl8app-cryptocurrency-bep20-payment-gateway-for-easy-digital-downloads'),
            'add_new' => __('Add New', 'pl8app-cryptocurrency-bep20-payment-gateway-for-easy-digital-downloads'),
            'add_new_item' => __('Add New Crypto Currency', 'pl8app-cryptocurrency-bep20-payment-gateway-for-easy-digital-downloads'),
            'edit_item' => __('Edit Crypto Currency', 'pl8app-cryptocurrency-bep20-payment-gateway-for-easy-digital-downloads'),
            'new_item' => __('New Crypto Currency', 'pl8app-cryptocurrency-bep20-payment-gateway-for-easy-digital-downloads'),
            'all_items' => __('All Crypto Currencies', 'pl8app-cryptocurrency-bep20-payment-gateway-for-easy-digital-downloads'),
            'view_item' => __('View Crypto Currency', 'pl8app-cryptocurrency-bep20-payment-gateway-for-easy-digital-downloads'),
            'search_items' => __('Search Crypto Currency', 'pl8app-cryptocurrency-bep20-payment-gateway-for-easy-digital-downloads'),
            'not_found' => __('No Crypto Currencies found', 'pl8app-cryptocurrency-bep20-payment-gateway-for-easy-digital-downloads'),
            'not_found_in_trash' => __('No Crypto Currencies found in Trash', 'pl8app-cryptocurrency-bep20-payment-gateway-for-easy-digital-downloads'),
            'parent_item_colon' => '',
            'menu_name' => __('pl8app Crypto Payments', 'pl8app-cryptocurrency-bep20-payment-gateway-for-easy-digital-downloads')
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'thumbnail')
        );

        $this->parameters = $args;
        $this->post_type = 'pl8app_edd_cstm_crpt';

        add_action( 'init', array($this, 'add_post_type'), 1);
        add_filter( 'manage_edit-pl8app_edd_custom_crypto_columns', array($this, 'pl8app_edd_custom_crypto_columns') ) ;
        add_action( 'manage_pl8app_edd_custom_crypto_posts_custom_column', array($this, 'pl8app_edd_custom_crypto_posts_custom_column'), 10, 2 ) ;
        add_filter( 'post_row_actions', array($this, 'remove_bulk_actions'), 10 , 2);
        add_filter( 'user_has_cap', array($this, 'limit_user_editable_row'), 10, 3);
    }

    public function limit_user_editable_row($allcaps, $caps, $args){
        $reduxOptions = get_option(pl8app_edd_REDUX_ID, array());

        if(!isset($reduxOptions['pl8app_edd_default_token_post_id'])){

            $arg = array (
                'name' => 'pl8app',
                'post_type' => 'pl8app_edd_cstm_crpt',
                'post_status' => 'publish',
                'post_title' => 'pl8app'
            );

            $post_id = wp_insert_post( $arg );
            update_post_meta($post_id, 'contract_address', '0xb77178a0fdead814296eae631be8e8171c02592b');

            $reduxOptions['pl8app_edd_default_token_post_id'] = $post_id;

            //Update redux options
            update_option(pl8app_edd_REDUX_ID,$reduxOptions);

        }

        if(is_array($args) && $args[0] == 'edit_post' && isset($args[2]) && $args[2] == $reduxOptions['pl8app_edd_default_token_post_id']){
            unset($allcaps[$caps[0]]);
        }

        return $allcaps;
    }

    public function add_post_type()
    {
        register_post_type($this->post_type, $this->parameters);
    }

    public function pl8app_edd_custom_crypto_columns($columns){

        $columns['contract_address'] = __('Contract Address', 'pl8app-cryptocurrency-bep20-payment-gateway-for-easy-digital-downloads');
        $columns['logo'] = __('Logo', 'pl8app-cryptocurrency-bep20-payment-gateway-for-easy-digital-downloads');
        $columns['tolerance'] = __('Tolerance Rate(%)', 'pl8app-cryptocurrency-bep20-payment-gateway-for-easy-digital-downloads');
        $columns['title'] = __('Token Name', 'pl8app-cryptocurrency-bep20-payment-gateway-for-easy-digital-downloads');

        return $columns;
    }

    public function pl8app_edd_custom_crypto_posts_custom_column( $column, $post_id ){

        global $post;


        switch( $column ) {
            case 'contract_address' :

                echo esc_html( get_post_meta( $post->ID, 'contract_address', true ) );
                break;

            case 'logo' :
                if($post->post_title == 'pl8app'){
                    echo '<img src="'.esc_attr(pl8app_edd_PLUGIN_DIR . '/assets/img/pl8app_logo.png').'" width="30" height="30" class="image_logo_preview" />';
                }
                else{
                    echo '<img src="'.esc_attr(get_the_post_thumbnail_url( $post->ID  )).'" class="image_logo_preview" />';
                }
                break;
                case 'tolerance':
                    $tolerance_rate = get_post_meta( $post->ID, 'token_tolerance', true );

                    echo !empty($tolerance_rate)? esc_html($tolerance_rate) .'(%)' : 'Default (2%)';

            /* Just break out of the switch statement for everything else. */
            default :
                break;
        }
    }

    public function remove_bulk_actions($actions, $post){

        if($post->post_title == 'pl8app' && $post->post_type=='pl8app_edd_cstm_crpt'){
            unset($actions['edit']);
            unset($actions['trash']);
            unset($actions['view']);
            unset($actions['inline hide-if-no-js']);
        }
        return $actions;
    }

}
