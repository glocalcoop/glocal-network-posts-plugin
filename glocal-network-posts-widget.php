<?php
/*
Activist Network Posts Widget
Description: Display the posts in your network in a widget.
Author: Pea, Glocal
Author URI: http://glocal.coop
Version: 0.1
License: GPL
*/

/************* TODOs *****************/
// Add image upload
// Add multi-select list of sites to exclude

/************* Parameters *****************/
// - @return - Return (display list of sites or return array of sites) (default: display)
// - @numbersites - Number of sites to display/return (default: no limit)
// - @excludesites - ID of sites to exclude (default: 1 (usually, the main site))
// - @sortby - newest, updated, active, alpha (registered, last_updated, post_count, blogname) (default: alpha)
// - @defaultimage - Default image to display if site doesn't have a custom header image (default: none)
// - @instanceid - ID name for site list instance (default: network-sites-RAND)
// - @classname - CSS class name(s) (default: network-sites-list)
// - @hidemeta - Select in order to hide update date and latest post. Only relevant when return = 'display'. (default: false)
// - @hideimage - Select in order to hide the site image (default: false)

/**
 * Widget Class
 */
class glocal_network_posts_widget extends WP_Widget {
    // all of our widget code will go here
    
    /** Constructor **/
    function glocal_network_posts_widget() {
        // parent::WP_Widget(false, $name = 'Network Sites Widget');
        //_e('Display list of sites in your network.')
        $widget_ops = array(
            'classname' => 'network-posts-list', 
            'description' => __( 'Display list of posts in your network.','glocal-network-posts'),
        );
        $this->WP_Widget('glocal_network_posts_widget', 'Network Sites', $widget_ops);
    }
    
    $widget_fields = array(
        'number_posts',
        'exclude_sites',
        'include_categories',
        'posts_per_site',
        'output',
        'style',
        'id',
        'class',
        'title',
        'title_image',
    );
    
    
    /** Form **/
    /** @see WP_Widget::form */
    function form($instance) {
        
//        foreach($widget_fields as $field) {
//            '$' . $field .  =  esc_attr($instance["'" . $field . "'"]);
//        }

        $title = esc_attr($instance['title']);
        // $text = esc_attr($instance['text']);
        // $checkbox = esc_attr($instance['checkbox']);
        // $textarea = esc_attr($instance['textarea']);
        // $select = esc_attr($instance['select']);
        
        $numbersites = esc_attr($instance['numbersites']);
        $excludesites = esc_attr($instance['excludesites']);
        $sortby = esc_attr($instance['sortby']);
        $defaultimage = esc_attr($instance['defaultimage']);
        $instanceid = esc_attr($instance['instanceid']);
        $classname = esc_attr($instance['classname']);
        $hidemeta = esc_attr($instance['hidemeta']);
        $hideimage = esc_attr($instance['hideimage']);

        ?>

        <!-- Title -->
         <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','glocal-network-sites'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>


        <!-- Number of Sites-->
         <p>
            <label for="<?php echo $this->get_field_id('numbersites'); ?>"><?php _e('Number of sites:','glocal-network-sites'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('numbersites','glocal-network-sites'); ?>" name="<?php echo $this->get_field_name('numbersites'); ?>" type="number" value="<?php echo $numbersites; ?>"  min="0" max="100" />
        </p>

        <!-- Exclude sites-->
         <p>
            <label for="<?php echo $this->get_field_id('excludesites'); ?>"><?php _e('Sites to exclude:','glocal-network-sites'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('excludesites'); ?>" name="<?php echo $this->get_field_name('excludesites'); ?>" type="text" value="<?php echo $excludesites; ?>" />
        </p>

        <!-- Sort by -->
        <!-- registered, last_updated, post_count, blogname-->
        <p>
            <label for="<?php echo $this->get_field_id('sortby'); ?>"><?php _e('Order by:','glocal-network-sites'); ?></label>
            <select name="<?php echo $this->get_field_name('sortby'); ?>" id="<?php echo $this->get_field_id('sortby'); ?>" class="widefat">
                <?php
                $sortoptions = array(
                    'Alphabetical' => 'blogname', 
                    'Recently Active' => 'last_updated', 
                    'Most Active' =>'post_count', 
                    'Date Created' => 'registered'
                );
                foreach ($sortoptions as $key => $value) {
                    echo '<option value="' . $value . '" id="' . $value . '"', $sortby == $value ? ' selected="selected"' : '', '>', $key, '</option>';
                }
                ?>
            </select>
        </p>

        <!-- Default image -->
         <p>
            <label for="<?php echo $this->get_field_id('defaultimage'); ?>"><?php _e('Default site image:','glocal-network-sites'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('defaultimage'); ?>" name="<?php echo $this->get_field_name('defaultimage'); ?>" type="text" value="<?php echo $defaultimage; ?>" />
        </p>

        <!-- Hide meta -->
        <p>
            <input id="<?php echo $this->get_field_id('hidemeta'); ?>" name="<?php echo $this->get_field_name('hidemeta'); ?>" type="checkbox" value="1" <?php checked( '1', $hidemeta ); ?> />
            <label for="<?php echo $this->get_field_id('hidemeta'); ?>"><?php _e('Hide meta info (update date and recent post)', 'glocal-network-sites'); ?></label>
        </p>

        <!-- Hide image -->
        <p>
            <input id="<?php echo $this->get_field_id('hideimage'); ?>" name="<?php echo $this->get_field_name('hideimage'); ?>" type="checkbox" value="1" <?php checked( '1', $hideimage ); ?> />
            <label for="<?php echo $this->get_field_id('hideimage'); ?>"><?php _e('Hide image (site image)', 'glocal-network-sites'); ?></label>
        </p>

        <!-- Instances ID -->
         <p>
            <label for="<?php echo $this->get_field_id('instanceid'); ?>"><?php _e('List ID:','glocal-network-sites'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('instanceid'); ?>" name="<?php echo $this->get_field_name('instanceid'); ?>" type="text" value="<?php echo $instanceid; ?>" />
        </p>

        <!-- Class name -->
         <p>
            <label for="<?php echo $this->get_field_id('classname'); ?>"><?php _e('List class:','glocal-network-sites'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('classname'); ?>" name="<?php echo $this->get_field_name('classname'); ?>" type="text" value="<?php echo $classname; ?>" />
        </p>


        <?php
    }
    

    /** Update **/
    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        // $instance['text'] = strip_tags($new_instance['text']);
        // $instance['checkbox'] = strip_tags($new_instance['checkbox']);
        // $instance['textarea'] = strip_tags($new_instance['textarea']);
        // $instance['select'] = strip_tags($new_instance['select']);

        $instance['numbersites'] = strip_tags($new_instance['numbersites']);
        $instance['excludesites'] = strip_tags($new_instance['excludesites']);
        $instance['sortby'] = strip_tags($new_instance['sortby']);
        $instance['defaultimage'] = strip_tags($new_instance['defaultimage']);
        $instance['instanceid'] = strip_tags($new_instance['instanceid']);
        $instance['classname'] = strip_tags($new_instance['classname']);
        $instance['hidemeta'] = strip_tags($new_instance['hidemeta']);
        $instance['hideimage'] = strip_tags($new_instance['hideimage']);
        
        return $instance;
    }

  
    /** Display **/
    /** @see WP_Widget::widget */
	function widget($args, $instance) {
	    extract( $args );

        // these are our widget options
	    $title = apply_filters('widget_title', $instance['title']);
        // $text = $instance['text'];
        // $checkbox = $instance['checkbox'];
        // $textarea = $instance['textarea'];
        // $select = $instance['select'];
        
        $numbersites = $instance['numbersites'];
        $excludesites = $instance['excludesites'];
        $sortby = $instance['sortby'];
        $defaultimage = $instance['defaultimage'];
        $instanceid = $instance['instanceid'];
        $classname = $instance['classname'];
        $hidemeta = $instance['hidemeta'];
        $hideimage = $instance['hideimage'];
        
        echo $before_widget;
        
        // if the title is set
        if ( $title ) {
            echo $before_title . $title . $after_title;
        }
        
        // Use glocal_networkwide_sites function to display sites
        if(function_exists('glocal_networkwide_sites')) {
            glocal_networkwide_sites( $instance );
        }
        
        echo $after_widget;
    }

}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("glocal_network_posts_widget");'));

