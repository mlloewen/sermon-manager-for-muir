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
		$tax_names = get_taxonomies( array( 'public' => true ), 'names' );
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_entries']) )
			delete_option('widget_recent_entries');

		$instance['taxonomy'] = $new_instance['taxonomy'];
		$instance['term'] = $new_instance['term'];
		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_recent_sermons', 'widget');
	}

	
	function form( $instance ) {
		$this->taxonomies = get_object_taxonomies( 'wpfc_sermon', 'objects' );
	  	$defaults = array('taxonomy'=>'none', 'term'=>array() );
	    $instance = wp_parse_args( (array) $instance, $defaults );
	    extract( $instance );
?>
		<ul class="tabs">
			<li class="active"><a href="#tab1">Tab 1</a></li>
			<li><a href="#tab2">Tab 2</a></li>
			<li><a href="#tab3">Tab 3</a></li>
		</ul>
	 
		<div class="tab_container">
		 
			<div id="tab1" class="tab_content">
				<p> 
			        <label for="<?php echo $this->get_field_id('taxonomy'); ?>"><?php _e('Select a taxonomy:','sermon-manager'); ?></label> 
			        <select class="posts_sermon_class" name="<?php echo $this->get_field_name('taxonomy'); ?>" id="<?php echo $this->get_field_id('taxonomy'); ?>">
			          <option value="none" <?php echo 'none' == $instance['taxonomy'] ? ' selected="selected"' : ''; ?>><?php _e('Ignore Taxonomy &amp; Term','sermon-manager'); ?></option>
			          <?php
			            foreach ($this->taxonomies as $option) {
			              echo '<option value="' . $option->name . '"', $instance['taxonomy'] == $option->name ? ' selected="selected"' : '', '>', $option->label, '</option>';
			            }
			          ?>
			        </select>   
		      	</p>
		      	<?php $ter= $instance['term'];
		      	 ?>
			    <div class="total_term_div">
			      <label><?php _e('Select Terms:','sermon-manager'); ?></label>
			      <?php foreach ($this->taxonomies as $option) { 
			      		if($instance['taxonomy']==$option->name){
			      			$display='';
			      		}else{
			      			$display='style="display:none;"';
			      		}
			      	?>
			        <div class="taxonomy-<?php echo $option->name; ?> terms_div_class" <?php echo $display; ?> >
			          <ul>
			            <?php 
			              $terms = get_terms( $option->name, array('hide_empty'=>0) );
			              foreach($terms as $term) {
			            ?>
			                <li>
			                  <input type="checkbox" name="<?php echo $this->get_field_name('term'); ?>[]" value="<?php echo esc_attr( $term->slug ); ?>" <?php echo checked(in_array(esc_attr( $term->slug ), $ter),true, false); ?> /><?php echo $term->name; ?>
			                </li>
			            <?php } ?>
			          </ul>
			        </div>
			      <?php } ?>
			    </div>
			</div>
		
			<div id="tab2" class="tab_content" style="display:none;"></div>
		 
			<div id="tab3" class="tab_content" style="display:none;">
				<?php
					//echo "Hello Rick you are in tab 3";
				?>
				<?php
					$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
					$number = isset($instance['number']) ? absint($instance['number']) : 5;
				?>
					<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

					<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of sermons to show:'); ?></label>
					<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
			</div>
		 
		</div>
	 
	<div class="tab-clear"></div>
<?php
	}
}
add_action( 'widgets_init', create_function('', 'return register_widget("WP4C_Recent_Sermons");') );

?>