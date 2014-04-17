<?php
  /*
    Posts From Sermons
  */
?>
  

<?php
  class Posts_From_Sermons extends WP_Widget {

    public function __construct() {
      $widget_ops = array( 'classname'=>'postfromsermons', 'description'=>'Allows you to display a list of sermons posts within a particular category.' );
      $control_ops = array( 'width'=>250, 'height'=>250, 'id_base'=>'postfromsermons_id' );
      $this->WP_Widget( 'postfromsermons_id', 'Sermon Posts', $widget_ops, $control_ops );
    }

    function form( $instance ) {
      $this->taxonomies = get_object_taxonomies( 'wpfc_sermon', 'objects' );
      $defaults = array('taxonomy'=>'none', 'term'=>'off' );
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
                  <input type="checkbox" name="<?php echo $instance['term']; ?>" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked($instance['term'], 'on'); ?> /><?php echo $term->name; ?>
                </li>
            <?php } ?>
          </ul>
        </div>

      <?php } ?>
    </div>
<?php
     
    }

    function update( $new_instance, $old_instance ) {
      // foreach($_REQUEST['widget-postfromsermons_id'] as $key=>$value) {
      //   foreach($value['term'] as $val_term) {
      //     $new_terms[] = $val_term;
      //   }
      // }
      $instance = $old_instance;
      $instance['taxonomy'] = $new_instance['taxonomy'];
     // $instance['term'] = $new_terms;
       $instance['term'] = $new_instance['term'];
      return $instance;
    }

  function widget( $args, $instance ) {

   /* extract( $args ); */
    
  }
}

function posts_from_sermons_widgets() {
  register_widget( 'Posts_From_Sermons' );
}
add_action( 'widgets_init', 'posts_from_sermons_widgets' );
?>

<?php
  add_action( 'admin_footer', 'select_data_javascript' );
  function select_data_javascript() {
?>
  <script type="text/javascript" >
    jQuery(document).ready(function($) {
      if(jQuery("#widget-postfromsermons_id-2-taxonomy").val()=="none") {
          jQuery(".total_term_div label").css({
            display : "none"
          });
        }
        else {
          jQuery(".total_term_div label").css({
            display : "block"
          });
        }
      jQuery("#widget-postfromsermons_id-2-taxonomy").on("change", function() {
        jQuery("div.terms_div_class").css({
          display : "none"
        });
        var presentClassName = $(this).val();
        if(jQuery(this).val()=="none") {
          jQuery(".total_term_div label").css({
            display : "none"
          });
        }
        else {
          jQuery(".total_term_div label").css({
            display : "block"
          });
        }
        jQuery("div.taxonomy-"+presentClassName).css({
          display : "block"
        });
        if(jQuery.trim(jQuery("div.taxonomy-"+presentClassName+" ul").html()) == "") {
          jQuery("div.taxonomy-"+presentClassName+" ul").html("No terms avialeble");
        }
      });
    });
  </script>
<?php
}



