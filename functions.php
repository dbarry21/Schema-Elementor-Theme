<?php
add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );

function enqueue_parent_styles() {
   wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}

//Dave Barry Creation of child them controlled "Organization Schema Part"
function ssseo_astra_child_customizer_settings($wp_customize) {
    // Add Section for Schema Settings
    $wp_customize->add_section('ssseo_schema_section', array(
        'title' => __('Schema Settings', 'ssseo-astra-child'),
        'description' => __('Add your Organization Schema here', 'ssseo-astra-child'),
        'priority' => 120,
    ));

    // Add Setting for Organization Schema
    $wp_customize->add_setting('organization_schema', array(
        'default' => '',
        'sanitize_callback' => 'wp_kses_post' // Allows HTML but sanitizes for safety
    ));

    // Add Control for the Schema
    $wp_customize->add_control('organization_schema_control', array(
        'label' => __('Organization Schema', 'ssseo-astra-child'),
        'section' => 'ssseo_schema_section',
        'settings' => 'organization_schema',
        'type' => 'textarea'
    ));
}

add_action('customize_register', 'ssseo_astra_child_customizer_settings');

//End Schem
//
//Dave Barry setting up location page types for schema and rich results
function ssseo_register_location_post_type() {
    $labels = array(
        'name'                  => _x('Locations', 'Post type general name', 'ssseo'),
        'singular_name'         => _x('Location', 'Post type singular name', 'ssseo'),
        'menu_name'             => _x('Locations', 'Admin Menu text', 'ssseo'),
        'name_admin_bar'        => _x('Location', 'Add New on Toolbar', 'ssseo'),
        'add_new'               => __('Add New', 'ssseo'),
        'add_new_item'          => __('Add New Location', 'ssseo'),
        'new_item'              => __('New Location', 'ssseo'),
        'edit_item'             => __('Edit Location', 'ssseo'),
        'view_item'             => __('View Location', 'ssseo'),
        'all_items'             => __('All Locations', 'ssseo'),
        'search_items'          => __('Search Locations', 'ssseo'),
        'not_found'             => __('No locations found.', 'ssseo'),
        'not_found_in_trash'    => __('No locations found in Trash.', 'ssseo'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'location'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
    );

    register_post_type('location', $args);
}

add_action('init', 'ssseo_register_location_post_type');

function ssseo_add_location_meta_boxes() {
    add_meta_box(
        'ssseo_location_phone',      // Unique ID
        'Phone Number',              // Box title
        'ssseo_location_phone_html', // Content callback, must be of type callable
        'location'                   // Post type
    );
}

add_action('add_meta_boxes', 'ssseo_add_location_meta_boxes');

function ssseo_location_phone_html($post) {
    $value = get_post_meta($post->ID, '_ssseo_location_phone', true);
    echo '<input type="text" name="ssseo_location_phone" value="' . esc_attr($value) . '">';
}

function ssseo_save_postdata($post_id) {
    if (array_key_exists('ssseo_location_phone', $_POST)) {
        update_post_meta(
            $post_id,
            '_ssseo_location_phone',
            $_POST['ssseo_location_phone']
        );
    }
}

add_action('save_post', 'ssseo_save_postdata');

function add_org_schema_meta_box() {
    $screens = ['post', 'page']; // Add other post types here if needed
    foreach ($screens as $screen) {
        add_meta_box(
            'org_schema_meta_box',           // Unique ID
            'Include Organization Schema',   // Box title
            'org_schema_meta_box_html',      // Content callback, must be of type callable
            $screen,                         // Post type
            'side'                           // Context
        );
    }
}
add_action('add_meta_boxes', 'add_org_schema_meta_box');

function org_schema_meta_box_html($post) {
    $value = get_post_meta($post->ID, '_org_schema_include', true);
    ?>
    <label for="org_schema_field">Include Organization Schema:</label>
    <input type="checkbox" name="org_schema_field" id="org_schema_field" value="1" <?php checked($value, 1); ?>>
    <?php
}

function save_org_schema_meta_box_data($post_id) {
    if (array_key_exists('org_schema_field', $_POST)) {
        update_post_meta(
            $post_id,
            '_org_schema_include',
            $_POST['org_schema_field']
        );
    }
}
add_action('save_post', 'save_org_schema_meta_box_data');

function create_services_post_type() {
    register_post_type('services',
        array(
            'labels' => array(
                'name' => __('Services'),
                'singular_name' => __('Service')
            ),
            'public' => true,
            'has_archive' => false,
            'rewrite' => array('slug' => 'services'),
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
            'show_in_rest' => true, // Set to true if you want to use Gutenberg editor
            'template' => array(
                // Add default blocks if you want to set a default structure (optional)
            ),
        )
    );
}
add_action('init', 'create_services_post_type');

////
function generate_service_schema() {
        global $post;
        $service_name = get_the_title($post->ID);
        $service_description = get_the_excerpt($post->ID);
        $service_url = get_permalink($post->ID);

        // Replace these with your organization's details
        $organization_name = get_bloginfo('name');
        $organization_url = get_site_url();
        $custom_logo_id = get_theme_mod('custom_logo');
        $organization_logo = wp_get_attachment_image_url($custom_logo_id, 'full');
        $organization_telephone = get_theme_mod('org_phone', 'Default Phone');
        $organization_address = array(
            "@type" => "PostalAddress",
            "streetAddress" => get_theme_mod('org_streetaddress', 'Default Street Address'),
            "addressLocality" => get_theme_mod('org_addresslocality', 'Default Locality'),
            "addressRegion" => get_theme_mod('org_addressregion', 'Default Region'),
            "postalCode" => get_theme_mod('org_postalcode', 'Default Postal Code'),
            "addressCountry" => "US"
        );

        $schema = array(
            "@context" => "http://schema.org",
            "@type" => "Service",
            "name" => $service_name,
            "description" => $service_description,
            "url" => $service_url,
            "provider" => array(
                "@type" => "Organization",
                "name" => $organization_name,
                "url" => $organization_url,
                "logo" => $organization_logo,
                "telephone" => $organization_telephone,
                "address" => $organization_address
            ),
            "areaServed" => array(
                array("@type" => "Place", "name" => "Miami Beach"),
                array("@type" => "Place", "name" => "Midtown Miami"),
                array("@type" => "Place", "name" => "Coconut Grove"),
                array("@type" => "Place", "name" => "Doral")
            )
        );

        echo '<script type="application/ld+json">' . json_encode($schema) . '</script>';
    }


//
function mytheme_customize_register($wp_customize) {
    // Add Section for Organization Details
    $wp_customize->add_section('org_details_section', array(
        'title'    => __('Organization Details', 'ssseo'),
        'priority' => 30,
    ));

    // Add Settings for Organization Details
    $fields = array(
        'org_phone' => __('Phone', 'ssseo'),
        'org_streetaddress' => __('Street Address', 'ssseo'),
        'org_addresslocality' => __('Address Locality', 'ssseo'),
        'org_addressregion' => __('Address Region', 'ssseo'),
        'org_postalcode' => __('Postal Code', 'ssseo'),
        'org_addresscountry' => __('Address Country', 'ssseo')
    );

    foreach ($fields as $id => $label) {
        $wp_customize->add_setting($id, array('default' => '', 'sanitize_callback' => 'sanitize_text_field'));
        $wp_customize->add_control($id, array(
            'label'    => $label,
            'section'  => 'org_details_section',
            'type'     => 'text',
        ));
    }

    // Add Setting for About Us Schema
    $wp_customize->add_setting('about_us_schema', array(
        'default'   => '',
        'transport' => 'refresh',
        'sanitize_callback' => 'mytheme_sanitize_textarea'
    ));

    // Add Control for About Us Schema
    $wp_customize->add_control('about_us_schema', array(
        'label'     => __('About Us Schema', 'ssseo'),
        'section'   => 'org_details_section',
        'type'      => 'textarea',
        'settings'  => 'about_us_schema'
    ));

    // Add Setting for Contact Us Schema
    $wp_customize->add_setting('contact_us_schema', array(
        'default'   => '',
        'transport' => 'refresh',
        'sanitize_callback' => 'mytheme_sanitize_textarea'
    ));

    // Add Control for Contact Us Schema
    $wp_customize->add_control('contact_us_schema', array(
        'label'     => __('Contact Us Schema', 'ssseo'),
        'section'   => 'org_details_section',
        'type'      => 'textarea',
        'settings'  => 'contact_us_schema'
    ));
}

// Custom sanitization callback function for textareas
function mytheme_sanitize_textarea($input) {
    return wp_kses_post(force_balance_tags($input));
}

add_action('customize_register', 'mytheme_customize_register');

function generate_blog_post_schema() {
    if (is_singular('post')) { // Check if it's a single blog post
        global $post;
        $post_title = get_the_title($post->ID);
        $post_description = get_the_excerpt($post->ID);
        $post_url = get_permalink($post->ID);
        $post_publish_date = get_the_date('c', $post->ID);
        $post_modified_date = get_the_modified_date('c', $post->ID);

        // Organization details
        $organization_name = get_bloginfo('name');
        $organization_url = get_site_url();
        $custom_logo_id = get_theme_mod('custom_logo');
        $organization_logo = wp_get_attachment_image_url($custom_logo_id, 'full');

        $schema = array(
            "@context" => "http://schema.org",
            "@type" => "BlogPosting",
            "headline" => $post_title,
            "description" => $post_description,
            "url" => $post_url,
            "datePublished" => $post_publish_date,
            "dateModified" => $post_modified_date,
            "publisher" => array(
                "@type" => "Organization",
                "name" => $organization_name,
                "url" => $organization_url,
                "logo" => array(
                    "@type" => "ImageObject",
                    "url" => $organization_logo
                )
            ),
            "mainEntityOfPage" => array(
                "@type" => "WebPage",
                "@id" => $post_url
            )
        );

        echo '<script type="application/ld+json">' . json_encode($schema) . '</script>';
    }
}



add_filter( 'wpseo_json_ld_output', '__return_false' );

//
//
//
//