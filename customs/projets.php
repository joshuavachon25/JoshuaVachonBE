<?php
function create_custom_projet() {
    $labels = array(
        'name' => _x( 'projets', 'Post Type General Name', 'textdomain' ),
        'singular_name' => _x( 'projet', 'Post Type Singular Name', 'textdomain' ),
        'menu_name' => _x( 'projets', 'Admin Menu text', 'textdomain' ),
        'name_admin_bar' => _x( 'projet', 'Add New on Toolbar', 'textdomain' ),
        'archives' => __( 'Archives projet', 'textdomain' ),
        'attributes' => __( 'Attributs projet', 'textdomain' ),
        'parent_item_colon' => __( 'Parents projet:', 'textdomain' ),
        'all_items' => __( 'Tous projets', 'textdomain' ),
        'add_new_item' => __( 'Ajouter nouvel projet', 'textdomain' ),
        'add_new' => __( 'Ajouter', 'textdomain' ),
        'new_item' => __( 'Nouvel projet', 'textdomain' ),
        'edit_item' => __( 'Modifier projet', 'textdomain' ),
        'update_item' => __( 'Mettre à jour projet', 'textdomain' ),
        'view_item' => __( 'Voir projet', 'textdomain' ),
        'view_items' => __( 'Voir projets', 'textdomain' ),
        'search_items' => __( 'Rechercher dans les projet', 'textdomain' ),
        'not_found' => __( 'Aucun projettrouvé.', 'textdomain' ),
        'not_found_in_trash' => __( 'Aucun projettrouvé dans la corbeille.', 'textdomain' ),
        'featured_image' => __( 'Image mise en avant', 'textdomain' ),
        'set_featured_image' => __( 'Définir l’image mise en avant', 'textdomain' ),
        'remove_featured_image' => __( 'Supprimer l’image mise en avant', 'textdomain' ),
        'use_featured_image' => __( 'Utiliser comme image mise en avant', 'textdomain' ),
        'insert_into_item' => __( 'Insérer dans projet', 'textdomain' ),
        'uploaded_to_this_item' => __( 'Téléversé sur cet projet', 'textdomain' ),
        'items_list' => __( 'Liste projets', 'textdomain' ),
        'items_list_navigation' => __( 'Navigation de la liste projets', 'textdomain' ),
        'filter_items_list' => __( 'Filtrer la liste projets', 'textdomain' ),
    );
    $args = array(
        'label' => __( 'projet', 'textdomain' ),
        'description' => __( 'Éléments pour le portfolio', 'textdomain' ),
        'labels' => $labels,
        'menu_icon' => 'dashicons-album',
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'taxonomies' => array('genre'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'hierarchical' => false,
        'exclude_from_search' => false,
        'show_in_rest' => true,
        'publicly_queryable' => true,
        'capability_type' => 'post',
    );
    register_post_type( 'projet', $args );
}
add_action( 'init', 'create_custom_projet', 0 );

class projetsMetabox {
    private $screen = array(
        'post',
        'page',
        'projet',
    );
    private $meta_fields = array(
        array(
            'label' => 'URL du projet',
            'id' => 'urlprojet',
            'type' => 'url',
        )
    );
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_fields' ) );
    }
    public function add_meta_boxes() {
        foreach ( $this->screen as $single_screen ) {
            add_meta_box(
                'projets',
                __( 'projets', 'textdomain' ),
                array( $this, 'meta_box_callback' ),
                $single_screen,
                'advanced',
                'default'
            );
        }
    }
    public function meta_box_callback( $post ) {
        wp_nonce_field( 'projets_data', 'projets_nonce' );
        echo 'Options pour projets';
        $this->field_generator( $post );
    }
    public function field_generator( $post ) {
        $output = '';
        foreach ( $this->meta_fields as $meta_field ) {
            $label = '<label for="' . $meta_field['id'] . '">' . $meta_field['label'] . '</label>';
            $meta_value = get_post_meta( $post->ID, $meta_field['id'], true );
            if ( empty( $meta_value ) ) {
                if ( isset( $meta_field['default'] ) ) {
                    $meta_value = $meta_field['default'];
                }
            }
            switch ( $meta_field['type'] ) {
                default:
                    $input = sprintf(
                        '<input %s id="%s" name="%s" type="%s" value="%s">',
                        $meta_field['type'] !== 'color' ? 'style="width: 100%"' : '',
                        $meta_field['id'],
                        $meta_field['id'],
                        $meta_field['type'],
                        $meta_value
                    );
            }
            $output .= $this->format_rows( $label, $input );
        }
        echo '<table class="form-table"><tbody>' . $output . '</tbody></table>';
    }
    public function format_rows( $label, $input ) {
        return '<tr><th>'.$label.'</th><td>'.$input.'</td></tr>';
    }
    public function save_fields( $post_id ) {
        if ( ! isset( $_POST['projets_nonce'] ) )
            return $post_id;
        $nonce = $_POST['projets_nonce'];
        if ( !wp_verify_nonce( $nonce, 'projets_data' ) )
            return $post_id;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;
        foreach ( $this->meta_fields as $meta_field ) {
            if ( isset( $_POST[ $meta_field['id'] ] ) ) {
                switch ( $meta_field['type'] ) {
                    case 'email':
                        $_POST[ $meta_field['id'] ] = sanitize_email( $_POST[ $meta_field['id'] ] );
                        break;
                    case 'text':
                        $_POST[ $meta_field['id'] ] = sanitize_text_field( $_POST[ $meta_field['id'] ] );
                        break;
                }
                update_post_meta( $post_id, $meta_field['id'], $_POST[ $meta_field['id'] ] );
            } else if ( $meta_field['type'] === 'checkbox' ) {
                update_post_meta( $post_id, $meta_field['id'], '0' );
            }
        }
    }
}
if (class_exists('projetsMetabox')) {
    new projetsMetabox;
};

?>
