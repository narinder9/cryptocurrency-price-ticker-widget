<?php 

    add_action( 'widgets_init', 'ccpwt_register_widget' );
    // Register the widget.
    function ccpwt_register_widget() { 
      register_widget( 'CCPWT_Widget' );
    }


  class CCPWT_Widget extends WP_Widget {
      // Set up the widget name and description.
      public function __construct() {
        $widget_options = array( 'classname' => 'ccpw_widget', 'description' => 'Crypto Currency Price Ticker Widget' );
        parent::__construct( 'ccpw_widget', 'Crypto Widget', $widget_options );
      }

    // Create the admin area widget settings form.
    public function form( $instance ) {
      $ccpw_shortcode_id = ! empty( $instance['ccpw_shortcode'] ) ? $instance['ccpw_shortcode'] : ''; 
    $title = ! empty( $instance['title'] ) ? $instance['title'] : ''; 
      ?>
  <p>
    <span class="imp-note" style="color:red">Important Note : Use widget shortcode according to available width</br></span>
    <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
    <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
  </p>

      <p>
        <label for="<?php echo $this->get_field_id( 'ccpw_shortcode' ); ?>"> Shortcode:</label>
         <select style="width:70%" id="<?php echo $this->get_field_id( 'ccpw_shortcode' ); ?>" name="<?php echo $this->get_field_name( 'ccpw_shortcode' ); ?>" >
          
    <?php    
        global $post;
       $args = array( 'numberposts' => -1, 'post_type' => 'ccpw');
         $postlist = get_posts($args);
         if($postlist){
        foreach ( $postlist as $post ) : setup_postdata( $post ); 
          $p_id=get_the_id();

          if($ccpw_shortcode_id==$p_id){
            echo'<option selected="selected" value="'.$p_id.'">[ccpw id="'.$p_id.'"]'.'</option>';
          }else{
            echo'<option value="'.$p_id.'">[ccpw id="'.$p_id.'"]'.'</option>';
          }
     
          endforeach; 
        }else{
           echo'<option value="">Shortcode Not Found</option>';
        }
          ?>
       
        </select>
      </p><?php
    }


    // Apply settings to the widget instance.
    public function update( $new_instance, $old_instance ) {
      $instance = $old_instance;
      $instance[ 'ccpw_shortcode' ] = strip_tags( $new_instance[ 'ccpw_shortcode' ] );
       $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
      return $instance;
    }

    // Create the widget output.
    public function widget( $args, $instance ) {
      $ccpw_shortcode=$instance['ccpw_shortcode'];
      $title = apply_filters('widget_title',$instance[ 'title' ] );

     echo $args['before_widget'] . $args['before_title'] . $title . $args['after_title'];

     if(isset($ccpw_shortcode) && !empty($ccpw_shortcode)){
         echo do_shortcode('[ccpw id="'.$ccpw_shortcode.'"]');
       }

       echo $args['after_widget'];
    }

    

}

