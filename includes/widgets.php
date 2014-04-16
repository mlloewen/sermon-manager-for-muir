<?php

/**
 * Recent Sermons Widget
 */
class WP4C_Recent_Sermons extends WP_Widget {

	function WP4C_Recent_Sermons() {
		$widget_ops = array('classname' => 'widget_recent_sermons', 'description' => __( 'The most recent sermons on your site', 'sermon-manager') );
		parent::__construct('recent-sermons', __('Recent Sermons', 'sermon-manager'), $widget_ops);
		$this->alt_option_name = 'widget_recent_entries';

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	function widget($args, $instance) {
		$cache = wp_cache_get('widget_recent_sermons', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Sermons', 'sermon-manager') : $instance['title'], $instance, $this->id_base);
		if ( ! $number = absint( $instance['number'] ) )
 			$number = 10;

		$r = new WP_Query(array(
				'post_type' => 'wpfc_sermon', 
				'meta_key' => 'sermon_date',
                'meta_value' => date("m/d/Y"),
                'meta_compare' => '>=',
                'orderby' => 'meta_value',
                'order' => 'DESC',
				'posts_per_page' => $number, 
				'no_found_rows' => true, 
				'post_status' => 'publish', 
				'ignore_sticky_posts' => true));
		if ($r->have_posts()) :
		?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) echo $before_title . $title . $after_title; ?>
		<ul>
		<?php  while ($r->have_posts()) : $r->the_post(); ?>
		<?php global $post; ?>
		<li>
		<a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a>
		<span class="meta">
			<?php 
			$terms = get_the_terms( $post->ID, 'wpfc_preacher' );
									
			if ( $terms && ! is_wp_error( $terms ) ) : 

				$preacher_links = array();

				foreach ( $terms as $term ) {
					$preacher_links[] = $term->name;
				}
									
				$preacher = join( ", ", $preacher_links );
			?>

			<?php echo $preacher; ?>, 

			<?php endif; 
			wpfc_sermon_date(get_option('date_format')); 
			?>
		</span>
		</li>
		<?php endwhile; ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_recent_sermons', $cache, 'widget');
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_entries']) )
			delete_option('widget_recent_entries');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_recent_sermons', 'widget');
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of sermons to show:'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
}
add_action( 'widgets_init', create_function('', 'return register_widget("WP4C_Recent_Sermons");') );


/* Enhanced Sermon Widget */
// This should be merged into the existing widget

add_action('admin_enqueue_scripts', 'wpfc_admin_load_scripts');	
function wpfc_admin_load_scripts($hook) {
	if( $hook != 'widgets.php' ) 
		return;
	wp_enqueue_script('sermon-admin-widget', SM_PLUGIN_URL.'includes/js/admin-widget.js', array('jquery'));
}
	
class Posts_From_Sermons extends WP_Widget {

    public function __construct() {
      $widget_ops = array( 'classname'=>'postfromsermons', 'description'=>'Display a list of the most recent sermons.' );
      $control_ops = array( 'width'=>250, 'height'=>250, 'id_base'=>'postfromsermons_id' );
      $this->WP_Widget( 'postfromsermons_id', 'Recent Sermons', $widget_ops, $control_ops );
	}
	
    function form( $instance ) {
      $this->taxonomies = get_object_taxonomies( 'wpfc_sermon', 'objects' );
      $defaults = array('taxonomy'=>'none', 'term'=>array() );
      $instance = wp_parse_args( (array) $instance, $defaults );
      extract( $instance );
?>
      <p> 
        <label for="<?php echo $this->get_field_id('taxonomy'); ?>"><?php echo 'Select a taxonomy:'; ?></label> 
        <select class="posts_sermon_class" name="<?php echo $this->get_field_name('taxonomy'); ?>" id="<?php echo $this->get_field_id('taxonomy'); ?>">
          <option value="none" <?php echo 'none' == $instance['taxonomy'] ? ' selected="selected"' : ''; ?>><?php echo 'Ignore Taxonomy &amp; Term'; ?></option>
          <?php

            foreach ($this->taxonomies as $option) {
              echo '<option value="' . $option->name . '"', $instance['taxonomy'] == $option->name ? ' selected="selected"' : '', '>', $option->label, '</option>';
            }
          ?>
        </select>   
      </p>
    <div class="total_term_div">
      <label>Select Terms:</label>
      <?php foreach ($this->taxonomies as $option) { ?>
        <div class="taxonomy-<?php echo $option->name; ?> terms_div_class" id="terms_div_id"  style="display:none;">
          <ul>
            <?php 
              $terms = get_terms( $option->name, array('hide_empty'=>0) );
              foreach($terms as $term) {
            ?>
                <li>
                  <input type="checkbox" name="<?php echo $this->get_field_name('term'); ?>[]" value="<?php echo esc_attr( $term->slug ); ?>" /><?php echo $term->name; ?>
                </li>
            <?php } ?>
          </ul>
        </div>

      <?php } ?>
    </div>
<?php
     
    }

    function update( $new_instance, $old_instance ) {
      foreach($_REQUEST['widget-postfromsermons_id'] as $key=>$value) {
        foreach($value['term'] as $val_term) {
          $new_terms[] = $val_term;
        }
      }
      $instance = $old_instance;
      $instance['taxonomy'] = $new_instance['taxonomy'];
      $instance['term'] = $new_terms;
      return $instance;
    }

  function widget( $args, $instance ) {

   /* extract( $args ); */
    
  }
}
// This is not how it should be done. Please see how I did it in the original widget above.
function wpfc_posts_from_sermons_widgets() {
  register_widget( 'Posts_From_Sermons' );
}
add_action( 'widgets_init', 'wpfc_posts_from_sermons_widgets' );

?>