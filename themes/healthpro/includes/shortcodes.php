<?php
/*
 * Display Site URL. Used in internal links
 */
add_shortcode( 'siteurl', 'wtd_siteurl_func' );
function wtd_siteurl_func( $atts ) {
	return get_bloginfo( 'url' );
}

/*
 * Display database name, useful for Copyright notice
 */
add_shortcode( 'sitedb', 'wtd_sitedb_func' );
function wtd_sitedb_func() {
	global $wpdb;
	return $wpdb->dbname;
}

/*
 * Display current year, useful for Copyright notice
 */
add_shortcode( 'currentyear', 'wtd_year_func' );
function wtd_year_func( $atts ) {
	return date( 'Y' );
}

/*
 * Custom Post List
 *
 * usage: [postlist posttype=post category_id="1,2,3" category_sulg="cat1,cat2,cat3" orderby=date order=DESC number=5 offset=1 type=list|featured show_excerpt=true|false]

 */
add_shortcode( 'postlist', 'wtd_postlist_func' );
function wtd_postlist_func( $atts ) {

	( ! isset( $atts['posttype'] ) ) ? $posttype           = 'post' : $posttype = $atts['posttype'];
	( ! isset( $atts['category_id'] ) ) ? $category_id     = '' : $category_id = $atts['category_id'];
	( ! isset( $atts['category_slug'] ) ) ? $category_slug = '' : $category_slug = $atts['category_slug'];
	( ! isset( $atts['orderby'] ) ) ? $orderby             = 'date' : $orderby = $atts['orderby'];
	( ! isset( $atts['order'] ) ) ? $order                 = 'DESC' : $order = $atts['order'];
	( ! isset( $atts['number'] ) ) ? $number               = 5 : $number = $atts['number'];
	( ! isset( $atts['offset'] ) ) ? $offset               = 0 : $offset = $atts['offset'];

	( ! isset( $atts['type'] ) ) ? $type          = 'list' : $type = $atts['type'];
	( isset( $atts['show_excerpt'] ) ) ? $excerpt = true : $excerpt = false;

	$args = array(
		'post_type'      => $posttype,
		'orderby'        => $orderby,
		'order'          => $order,
		'posts_per_page' => $number,
		'offset'         => $offset,
		'category_name'  => $category_slug,
		'cat'            => $category_id,
	);

	$query = new WP_Query( $args );

	$output = '';
	if ( $query->have_posts() ) :
		switch ( $type ) {
			case 'list':
				$output .= '<ul class="postlist">';
				while ( $query->have_posts() ) :
					$query->the_post();
					$output .= '<li>';
					$output .= '<a href="' . get_the_permalink( get_the_ID() ) . '" id="' . get_the_ID() . '">' . get_the_title() . '</a>';
					if ( $excerpt ) {
						$output .= '<p>' . get_the_excerpt() . '</p>';
					}
					$output .= '</li>';
				endwhile;
				$output .= '</ul>';
				break;
			case 'featured':
				while ( $query->have_posts() ) :
					$query->the_post();
					$output .= '<a href="' . get_the_permalink( get_the_ID() ) . '" id="' . get_the_ID() . '">' . get_the_title() . '</a>';
					if ( $excerpt ) {
						$output .= get_the_post_thumbnail( get_the_ID(), 'thumbnail', array( 'class' => 'alignleft' ) );

						$output .= '<p>' . get_the_excerpt() . '</p>';
					}
					$output .= '</li>';
				endwhile;
				break;
		}
	endif;
	wp_reset_postdata();
	return $output;
}

add_shortcode( 'lorem', 'wtd_lorem_func' );
function wtd_lorem_func( $atts ) {
	$lorem = array(
		'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In pharetra fermentum pharetra. Aenean quis dui sit amet enim aliquet imperdiet a sit amet lectus. Sed luctus vehicula massa vel consectetur. Donec sagittis dictum augue, quis euismod metus scelerisque in. Etiam rutrum, lorem at porttitor fringilla, nisi tortor euismod nibh, eu iaculis magna magna vitae purus. Aliquam ac elementum felis. Aenean tincidunt quam nec mattis pharetra. Morbi quis nibh metus. Nulla facilisi. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Sed tincidunt aliquam est, eu congue massa lobortis vitae. Duis vel rhoncus felis, et porttitor tortor. Phasellus nec sem eget eros consectetur lobortis.',
		'Aliquam id porta risus. Maecenas scelerisque pharetra gravida. Pellentesque ac blandit justo. Sed ultricies quis sem vitae venenatis. Aliquam ac pellentesque neque, et fermentum metus. Curabitur ut justo gravida, suscipit velit vel, faucibus lacus. Vestibulum ornare sollicitudin tincidunt. Nulla interdum eros vel purus aliquam, vitae aliquet purus volutpat. Integer et risus augue. Quisque rhoncus enim neque, nec efficitur ante semper a. Mauris in ex tempor, gravida erat sit amet, feugiat augue. Cras vitae consectetur felis, at tempor nunc. Nam pretium tempor justo, in egestas lectus lacinia id. Proin sit amet sapien elit.',
		'Cras a odio id tellus fermentum eleifend. Praesent velit ipsum, hendrerit id magna vel, eleifend sollicitudin tellus. Vivamus interdum, urna sed faucibus viverra, urna ligula pretium tortor, vitae porta ipsum odio nec felis. Donec euismod dolor nunc, at tincidunt ligula accumsan vitae. Phasellus auctor turpis mollis libero pellentesque ultrices. Ut tincidunt risus a sem consectetur, eu semper enim vehicula. In ultricies auctor neque a interdum. Donec commodo magna elit, id gravida neque auctor non. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Etiam ullamcorper est ac volutpat posuere. Aenean rhoncus tellus vitae molestie euismod. In nec viverra augue.',
		'Suspendisse facilisis rutrum volutpat. Quisque ultrices gravida tellus ut egestas. Donec sed sagittis metus. Proin sit amet malesuada tellus. Cras aliquam elit quis magna accumsan, eu convallis turpis aliquet. Aliquam dictum dignissim nunc quis viverra. Aenean nec lorem sed eros semper congue non sit amet lacus. Sed sodales metus lacus, at posuere lacus mollis id. Morbi quis metus vel nunc vestibulum luctus. Duis suscipit eu est non molestie. Aenean nulla tortor, condimentum ut justo ullamcorper, feugiat faucibus ipsum. Cras non tempus dolor. Nulla magna lacus, tempor eu lacinia maximus, pulvinar at erat. Proin ut arcu in lacus pharetra suscipit in placerat felis. In convallis tempor lorem at efficitur. Nam vestibulum leo purus, sit amet iaculis dui ultrices non.',
		'Cras congue augue vitae arcu consequat, quis ultrices nisi dignissim. Praesent consectetur nunc ut cursus egestas. Etiam scelerisque fermentum maximus. Nulla egestas pharetra velit non iaculis. Nullam varius lorem eget tellus vehicula molestie. Nam eu lacus ullamcorper tellus viverra malesuada. Pellentesque vel metus sed neque rutrum maximus. Sed dui nunc, egestas in massa nec, tristique lobortis diam. Vestibulum blandit nec tellus quis fringilla. Sed molestie fermentum accumsan. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Mauris sit amet purus sodales, pharetra arcu nec, hendrerit tellus.',
	);

	$count  = $atts['n'] - 1;
	$output = '';

	for ( $x = 0; $x <= $count; $x++ ) {
		$output .= '<p>' . $lorem[ $x ] . '</p>';

	}

	return $output;
}
