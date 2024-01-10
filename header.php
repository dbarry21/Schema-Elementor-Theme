<?php
/**
 * The template for displaying the header
 *
 * This is the template that displays all of the <head> section, opens the <body> tag and adds the site's header.
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$viewport_content = apply_filters( 'hello_elementor_viewport_content', 'width=device-width, initial-scale=1' );
$enable_skip_link = apply_filters( 'hello_elementor_enable_skip_link', true );
$skip_link_url = apply_filters( 'hello_elementor_skip_link_url', '#content' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="<?php echo esc_attr( $viewport_content ); ?>">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
	<?php
// Check if it's the homepage and output the organization schema
$organization_schema = get_theme_mod('organization_schema');
if (!empty($organization_schema) && is_front_page()) {
	?>
	<script type="application/ld+json"><?php
    echo $organization_schema; // Output the organization schema directly without escaping JS
	?>
	</script><?php
}

// Check of Include Org Schema is checked in wordpress editor /////////////////////
if (is_single() || is_page()) {
    $include_schema = get_post_meta(get_the_ID(), '_org_schema_include', true);
    if ($include_schema) {
        // Code to output the organization schema
        ?>
	<script type="application/ld+json"><?php
    echo $organization_schema; // Output the organization schema directly without escaping JS
	?>
	</script><?php
    }
}
//End Organizational Schema //////////////////////////////////////////////////////
//Check for Contact Us Page

$contact_us_page_id = get_theme_mod('contact_us_page', false);
$current_page_id = get_queried_object_id();

if ($contact_us_page_id && $contact_us_page_id == $current_page_id) {
    // This is the Contact Us page
    // Perform your action here
     echo '<meta name="contactpage" value="true"/>'; 
	// Retrieve the Contact Us Schema
	$contact_us_schema = get_theme_mod('contact_us_schema', '');
	if (!empty($contact_us_schema)) {
		echo '<script type="application/ld+json">' . $contact_us_schema . '</script>';
		}
}

//Check for About Us Page

$about_us_page_id = get_theme_mod('about_us_page', false);
$current_page_id = get_queried_object_id();

if ($about_us_page_id && $about_us_page_id == $current_page_id) {
    // Retrieve the About Us Schema
    $about_us_schema = get_theme_mod('about_us_schema', '');
	if (!empty($about_us_schema)) {
		echo '<script type="application/ld+json">' . $about_us_schema . '</script>';
	}
}
	
//Begin WebPage Schema EVERY PAGE \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
// Retrieves the site title (Organization Name)
    $publisher_name = get_bloginfo('name');
    // Retrieves the custom logo ID
    $custom_logo_id = get_theme_mod('custom_logo');
    // Retrieves the custom logo URL
    $logo = wp_get_attachment_image_url($custom_logo_id, 'full');
    $webpage_schema = array(
        "@context" => "http://schema.org",
        "@type" => "WebPage",
        "name" => get_the_title(),
        "description" => get_the_excerpt(),
        "url" => get_permalink(),
        "publisher" => array(
            "@type" => "Organization",
            "name" => $publisher_name,
            "logo" => array(
                "@type" => "ImageObject",
                "url" => $logo
            )
        )
    );
    ?>
    <script type="application/ld+json">
    <?php echo json_encode($webpage_schema); ?>
    </script>
	<?php
//end ////////////////////////////////////////////////////////////////////////////
if (is_singular('services')) {
	generate_service_schema();
}

// If LOCATION Page Include Location Schema - currently a custom field on location pages
if (is_singular('location') || is_page()) {
    // Fetch the 'Location Schema' custom field value
    $location_schema = get_post_meta(get_the_ID(), 'Location Schema', true);

    // Check if the 'Location Schema' custom field has a value
    if (!empty($location_schema)) {
        // Output the 'Location Schema' as a script of type application/ld+json
        echo '<script type="application/ld+json">' . $location_schema . '</script>';
    }
}
//////////////////////////////////////////////////////////////////////////////////////
echo generate_blog_post_schema();
?>
	<?php
if (is_singular()) {
    $post_type = get_post_type();
    echo '<meta name="posttype" value="' . esc_html($post_type) . '"/>'; 
}
?>
</head>
<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<?php if ( $enable_skip_link ) { ?>
<a class="skip-link screen-reader-text" href="<?php echo esc_url( $skip_link_url ); ?>"><?php echo esc_html__( 'Skip to content', 'hello-elementor' ); ?></a>
<?php } ?>

<?php
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
	if ( hello_elementor_display_header_footer() ) {
		if ( did_action( 'elementor/loaded' ) && hello_header_footer_experiment_active() ) {
			get_template_part( 'template-parts/dynamic-header' );
		} else {
			get_template_part( 'template-parts/header' );
		}
	}
}
