<?php
/*
Plugin Name: Activist Network Network-wide Posts
Description: Widget that shows a list of posts.
Author: Pea, Glocal
Author URI: http://glocal.coop
Version: 0.1
License: GPL
*/

/************* Parameters *****************/
// @number_posts - the total number of posts to display (default: 10)
// @posts_per_site - the number of posts for each site (default: no limit)
// @include_categories - the categories of posts to include (default: all categories)
// @exclude_sites - the site from which posts should be excluded (default: all sites (public sites, except archived, deleted and spam))
// @output - HTML or array (default: HTML)
// @style - normal or highlights (default: normal) - ignored if @output is 'array'
// @ignore_styles - don't use plugin stylesheet (default: false) - ignored if @output is 'array'
// @id - ID used in list markup (default: network-posts-RAND) - ignored if @output is 'array'
// @class - class used in list markup (default: post-list) - ignored if @output is 'array'
// @title - title displayed for list (default: Posts) - ignored unless @style is 'highlights'
// @title_image - image displayed behind title (default: home-highlight.png) - ignored unless @style is 'highlights'
// @show_thumbnail - display post thumbnail (default: False) - ignored if @output is 'array'
// @show_meta - To Do
// @show_excerpt - To Do
// @show_site_name - To Do

// Puts it all together
// Input: user-selected options array
// Output: list of posts from all sites, rendered as HTML or returned as array
function glocal_networkwide_posts_module($parameters = []) {

    // Default parameters
    // There aren't any now, but there might be some day.
    $defaults = array(
        'number_posts' => 10, //
        'exclude_sites' => null, 
        'include_categories' => null,
        'posts_per_site' => null,
        'output' => 'html',
        'style' => 'normal',
        'id' => 'network-posts-' . rand(),
        'class' => 'post-list',
        'title' => 'Posts',
        'title_image' => null,
        'show_thumbnail' => False,
        'show_meta' => True,
        'show_excerpt' => True,
        'show_site_name' => True
    );

    // Parse & merge parameters with the defaults - http://codex.wordpress.org/Function_Reference/wp_parse_args
    $settings = wp_parse_args( $parameters, $defaults );

    // Strip out tags
    foreach($settings as $parameter => $value) {
        // Strip everything
        $settings[$parameter] = strip_tags($value);
    }

    // Extract each parameter as its own variable
    extract( $settings, EXTR_SKIP );

    $exclude = $exclude_sites;
    // Strip out all characters except numbers and commas. This is working!
    $exclude = preg_replace("/[^0-9,]/", "", $exclude);
    $exclude = explode(",", $exclude);
    
    // Question: Is it a good idea to set a global variable for settings so that they can be accessed in all functions without having to explicitly pass them?
    // Half of the functions use at least some of the values
    //$GLOBALS['settings'] = $settings;
    
    // Get a list of sites
    $siteargs = array(
        'archived'   => 0,
        'spam'       => 0,
        'deleted'    => 0,
        'public'     => 1
    );
    $sites = wp_get_sites($siteargs);

    // CALL EXCLUDE SITES FUNCTION
    $sites_list = exclude_sites($exclude, $sites);
    
    // CALL GET POSTS FUNCTION
    $posts_list = get_posts_list($sites_list, $settings);  
    
    if($output == 'array') {
        
        // Return an array
        //return $posts_list;
        
        return '<pre>' . var_dump($posts_list) . '</pre>';
            
    } else {
        // CALL RENDER FUNCTION
        return render_html($posts_list, $settings);
        
        // Return rendered HMTL
        //return $rendered_html;
    }

}

// Inputs: uses global variable $styles
// Output: conditionally renders stylesheet using WP add_action() method
// Conditionals don't work. Loading on all pages...
add_action('wp_enqueue_scripts','load_highlight_styles', 200);
function load_highlight_styles() {    
    wp_enqueue_style( 'glocal-network-posts', plugins_url( '/stylesheets/css/style.css' , __FILE__ ) );
}

// Inputs: exclude array and sites array
// Output: array of sites, excluding those specfied in parameters
function exclude_sites($exclude_array, $sites_array) {

    $exclude = $exclude_array;
    $sites = $sites_array;

    $exclude_length = sizeof($exclude);
    $sites_length = sizeof($sites);

    // If there are any sites to exclude, remove them from the array of sites
    if($exclude_length) {

        for($i = 0; $i < $exclude_length; $i++) {

            for($j = 0; $j < $sites_length; $j++) {

                if($sites[$j]['blog_id'] == $exclude[$i]) {
                    // Remove the site from the list
                    unset($sites[$j]);
                }

            }
        }

        // Fix the array indexes so they're in order again
        $sites = array_values($sites);

        return $sites;

    }

}

// Inputs: array of sites and parameters array
// Output: single array of posts with site information, sorted by post-date
function get_posts_list($sites_array, $options_array) {

    $sites = $sites_array;
    $settings = $options_array;

    // Make each parameter as its own variable
    extract( $settings, EXTR_SKIP );

    $post_list = array();

    // For each site, get the posts
    foreach($sites as $site => $detail) {

        $site_id = $detail['blog_id'];
        $site_details = get_blog_details($site_id);

        // Switch to the site to get details and posts
        switch_to_blog($site_id);
        
        // CALL GET SITE'S POST FUNCTION
        // And add to array of posts
        $post_list = $post_list + get_sites_posts($site_id, $settings);

        // Unswitch the site
        restore_current_blog();

    }

    // CALL SORT FUNCTION
    $post_list = sort_by_date($post_list);
    
    // CALL LIMIT FUNCTIONS
    $post_list = limit_number_posts($post_list, $number_posts);

    return $post_list;

}

// Input: site id and parameters array
// Ouput: array of posts for site
function get_sites_posts($site_id, $options_array) {
    
    $site_id = $site_id;
    $settings = $options_array;

    // Make each parameter as its own variable
    extract( $settings, EXTR_SKIP );
    
    $site_details = get_blog_details($site_id);
    
    $post_args = array(
        'posts_per_page' => $posts_per_site,
        'category_name' => $include_categories
    );
    
    $recent_posts = wp_get_recent_posts($post_args);

    // Put all the posts in a single array
    foreach($recent_posts as $post => $postdetail) {

        global $post;

        $post_id = $postdetail['ID'];
        $author_id = $postdetail['post_author'];
        $prefix = $postdetail['post_date'] . '-' . $postdetail['post_name'];

        //CALL POST MARKUP FUNCTION
        $post_markup_class = get_post_markup_class($postdetail['post-id']);
        $post_markup_class .= ' siteid-' . $site_id;

        //Returns an array
        $post_thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'thumbnail' );

        if($postdetail['post_excerpt']) {
            $excerpt = $postdetail['post_excerpt'];
        } else {
            $excerpt = custom_post_excerpt($post_id);
        }

        $post_list[$prefix] = array(
            'post-id' => $post_id,
            'post-title' => $postdetail['post_title'],
            'post-date' => $postdetail['post_date'],
            'post-author' => get_the_author_meta( 'display_name', $postdetail['post_author'] ),
            'post-content' => $postdetail['post_content'],
            'post-excerpt' => $excerpt,
            'permalink' => get_permalink($post_id),
            'post-image' => $post_thumbnail[0],
            'post-class' => $post_markup_class,
            'site-id' => $site_id,
            'site-name' => $site_details->blogname,
            'site-link' => $site_details->siteurl,
        );

        //Get post categories
        $post_categories = wp_get_post_categories($post_id);

        foreach($post_categories as $post_category) {
            $cat = get_category($post_category);
            $post_list[$prefix]['categories'][] = $cat->name;
        }

    }
        
    return $post_list;
    
}

// Input: array of posts and max number parameter
// Output: array of posts reduced to max number
function limit_number_posts($posts_array, $max_number) {
    
    $posts = $posts_array;
    $limit = $max_number;
    
    if(count($posts) > $limit ) {
        array_splice($posts, $limit);
    }
    
    return $posts;
}

// Input: array of posts and parameters
// Output: rendered as 'normal' or 'highlight' HTML
function render_html($posts_array, $options_array) {
    
    $posts_array = $posts_array;
    $settings = $options_array;

    // Make each parameter as its own variable
    extract( $settings, EXTR_SKIP );
    
    if($style == 'highlights') {
        
        //CALL RENDER HIGHLIGHTS HTML FUNCTION
        $rendered_html = render_highlights_html($posts_array, $settings);
        
    } else {
        
        //CALL RENDER PLAIN HTML FUNCTION
        $rendered_html = render_plain_html($posts_array, $settings);
                
    }
    
    return $rendered_html;
}

// Input: array of post data
// Output: HTML list of posts
function render_plain_html($posts_array, $options_array) {

    $posts_array = $posts_array;
    $settings = $options_array;
    
    // Make each parameter as its own variable
    extract( $settings, EXTR_SKIP );
        
    $html = '<ul class="network-posts-list">';

    foreach($posts_array as $post => $post_detail) {

        global $post;
        
        $post_id = $post_detail['post-id'];
        $post_categories = implode(", ", $post_detail['categories']);
                    
        $html .= '<li class="post type-post list-item siteid-' . $post_detail['site-id'] . '">';
        if($show_thumbnail && $post_detail['post-image']) {
            //Show image
            //$html .= '<div class="item-image thumbnail">';
            $html .= '<a href="' . $post_detail['permalink'] . '" class="post-thumbnail">';
            $html .= '<img class="attachment-post-thumbnail wp-post-image item-image" src="' . $post_detail['post-image'] . '">';
            $html .= '</a>';
            //$html .= '</div>';
        }
        $html .= '<h4 class="post-title">';
        $html .= '<a href="' . $post_detail['permalink'] . '">';
        $html .= $post_detail['post-title'];
        $html .= '</a>';
        $html .= '</h4>';
        $html .= '<div class="meta">';
        $html .= '<span class="blog-name"><a href="' . $post_detail['site-link'] . '">';
        $html .= $post_detail['site-name'];
        $html .= '</a></span>';
        $html .= '<span class="post-date posted-on date"><time class="entry-date published updated" datetime="' . $post_detail['post-date'] . '">';
        $html .= date_i18n( get_option( 'date_format' ), strtotime( $post_detail['post-date'] ) );
        $html .= '</time></span>';
        $html .= '<span class="post-author byline author vcard"><a href="' . $post_detail['site-link'] . '/author/' . $post_detail['post-author'] . '">';
        $html .= $post_detail['post-author'];
        $html .= '</a></span>';
        $html .= '</div>';
        $html .= '<div class="post-excerpt" itemprop="articleBody">' . $post_detail['post-excerpt'] . '</div>';
        $html .= '<div class="meta">';
        $html .= '<div class="post-categories cat-links tags">' . $post_categories . '</div>';
        $html .= '</div>';
        $html .= '</li>';

    }

    $html .= '</ul>';

    return $html;

}

// Input: array of post data and parameters
// Output: HTML list of posts
function render_highlights_html($posts_array, $options_array) {
    
    $highlight_posts = $posts_array;
    $settings = $options_array;
    
    // Extract each parameter as its own variable
    extract( $settings, EXTR_SKIP );
    
    //var_dump($settings);
    
    if($title_image) {
        $title_image = 'style="background-image:url(' . $title_image . ')"';
    }
    
    $html = '';
    
    $html .= '<article id="highlights-module" class="highlights ' . $class . '">';
    $html .= '<h2 class="module-heading" ' . $title_image . '>';
    $html .= $title;
    $html .= '</h2>';
    $html .= render_plain_html($highlight_posts, $settings);
    $html .= '</article>';

    return $html;

}

// Input: post excerpt text
// Output: cleaned up excerpt text
function custom_post_excerpt($post_id, $length='55', $trailer=' ...') {
    $the_post = get_post($post_id); //Gets post ID

        $the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
        $excerpt_length = $length; //Sets excerpt length by word count
        $the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
        $words = explode(' ', $the_excerpt, $excerpt_length + 1);

        if(count($words) > $excerpt_length) :
            array_pop($words);
            $trailer = '<a href="' . get_permalink($post_id) . '">' . $trailer . '</a>';
            array_push($words, $trailer);
            $the_excerpt = implode(' ', $words);
        endif;

    return $the_excerpt;
}

// Input: post_id
// Output: string of post classes (to be used in markup)
function get_post_markup_class($post_id) {
    
    $post_id = $post_id;
    
    $markup_class_array = get_post_class(array('list-item'), (int) $post_id);
    
    $post_markup_class = implode(" ", $markup_class_array);
    
    return $post_markup_class;
}

// Inputs: array of posts data
// Output: array of posts data sorted by post-date
function sort_by_date($posts_array) {

    $posts_array = $posts_array;

    usort($posts_array, function ($b, $a) {
        return strcmp($a['post-date'], $b['post-date']);
    });

    return $posts_array;

}

// Inputs: array of posts data
// Output: array of posts data sorted by site
function sort_by_site($posts_array) {

    $posts_array = $posts_array;

    usort($posts_array, function ($b, $a) {
        return strcmp($a['site-id'], $b['site-id']);
    });

    return $posts_array;

}

//Usage
//[anp_network_posts number_posts=10 exclude_sites="1,5" include_categories="news" posts_per_site=2 style= "highlights" id="my-highlights" class="post-list" title="Network Highlights" title_image="http://activistnetwork.site/wp-content/uploads/2015/03/my_photo.jpg" show_thumbnail=0]

// Shortcode
// Inputs: optional parameters
// Output: rendered HTML list of posts
function glocal_networkwide_posts_shortcode( $atts, $content = null ) {

	// Attributes
	extract( shortcode_atts(
		array(), $atts )
	);

    if(function_exists('glocal_networkwide_posts_module')) {
        $html .= glocal_networkwide_posts_module( $atts );
        echo $html;
    }
    
}
add_shortcode( 'anp_network_posts', 'glocal_networkwide_posts_shortcode' );

/************* WIDGET *****************/

// Add Widget
//require_once dirname( __FILE__ ) . '/glocal-network-sites-widget.php';

/************* TINYMCE EDITOR BUTTON *****************/

// Add TinyMCE button
//require_once dirname( __FILE__ ) . '/glocal-network-tinymce.php';