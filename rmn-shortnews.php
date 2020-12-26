<?php
/*
	Plugin Name: Short News
	Plugin URI:
	Description: Add custom post type for short news
	Version: 0.0.1
	Author: Roman Seredenko
	Author URI: https://romans.website
	License: GPLv2
*/



/********************************
 
	Custom post type
 
 **********************************/

add_action( 'init', 'rmn_sn_create_custom_post_type' );

function rmn_sn_create_custom_post_type() {
	register_post_type( 'rmn_short_news',
		array(
			'labels' => array(
				'name' => 'Short News',
				'singular_name' => 'Short News',
				'add_new_item' => 'Add News',
				'edit_item' => 'Edit News',
				'new_item' => 'New News',
				'view_item' => 'View tbe News',
				'view_items' => 'View News',
				'search_items' => 'Search News',
				'not_found' => 'No news found',
				'not_found_in_trash' => 'No news found in Trash',
				'all_items' => 'All News',
				'items_list' => 'News List',
				'item_published' => 'News published',
				'item_published_privately' => 'News published privately',
				'item_updated' => 'News updated'
			),
			'public' => true,
			'menu_position' => 5,
			'supports' => array( 'title', 'editor' ),
			'taxonomies' => array( '' ),
			'menu_icon' => 'dashicons-feedback',
			'has_archive' => true, //TODO Create an option for this
			'exclude_from_search' => true,
			'rewrite' => array( 'slug' => 'news' )
		)
	);
}

/********************************
 
	Admin menu and options page 
 
 **********************************/

add_action( 'admin_menu', 'rmn_sn_settings_menu' );

function rmn_sn_settings_menu() {
	add_options_page(
		'Short News Configuraion',
		'Short News',
		'manage_options',
		'rmn-short-news',
		'rmn_sn_options_page'
	);
}
function rmn_sn_options_page() {
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline">Short News Options</h1>
		<hr class="wp-header-end">
		<h2>Shortcode Configuraion</h2>
		<form method="post" action="admin-post.php">
			<input type="hidden" name="action" value="save_rmn_sn_options">
			<?php wp_nonce_field( 'rmn_sn' ); ?>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<label for="shortcode">Shortcode</label>
						</th>
						<td>
							<input name="shortcode" type="text" id="shortcode" value="[short-news]" readonly="readonly" class="regular-text">
							<span id="shortcode-copy" class="description">Copied!</span>
							<p class="description" id="shortcode-description">Click on the field to copy the shortcode</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="rmn_sn_posts_per_page">Number of news</label>
						</th>
						<td>
							<input name="rmn_sn_posts_per_page" type="number" id="rmn_sn_posts_per_page" value="<?php echo esc_html( get_option( 'rmn_sn_posts_per_page' ) ); ?>" class="regular-text">
							<p class="description" id="rmn_sn_posts_per_page-description">Number of news to be displayed by shortcode</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Date</th>
						<td>
							<label for="rmn_sn_display_date">
								<input type="checkbox" name="rmn_sn_display_date" id="rmn_sn_display_date" <?php checked( get_option( 'rmn_sn_display_date' ) ); ?>>
								Show dates of the news
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="rmn_sn_date_format">Date Format</label>
						</th>
						<td>
							<input name="rmn_sn_date_format" type="text" id="rmn_sn_date_format" value="<?php echo esc_html( get_option( 'rmn_sn_date_format' ) ); ?>" class="regular-text">
							<p class="description" id="drmn_sn_date_format-description">Detailed information about formatting dates you can find here: <a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank">Formatting Date and Time</a></p>
						</td>
					</tr>
					<tr>
						<th scope="row">Title</th>
						<td>
							<label for="rmn_sn_display_title">
								<input type="checkbox" name="rmn_sn_display_title" id="rmn_sn_display_title" <?php checked( get_option( 'rmn_sn_display_title' ) ); ?>>
								Show titles of the news
							</label>
						</td>
					</tr>
				</tbody>
			</table>
			<input type="submit" value="Save Options" class="button-primary">
		</form>
		<!--<h2>Layout Configuraion</h2>-->
		<style>
			#shortcode-copy {
				opacity: 0;
			}
			#shortcode-copy.-copied {
				animation: copied 1s
			}
			@keyframes copied {
				0%, 50% { opacity: 1; }
				100% { opacity: 0; }
			}
		</style>
		<script>
			let sortcodeInput = document.getElementById( 'shortcode' );
			let copiedNotification = document.getElementById( 'shortcode-copy' );
			sortcodeInput.addEventListener( 'click', () => {
				let sortcode = sortcodeInput.select();
				document.execCommand( 'copy' );
				copiedNotification.classList.add( '-copied' );
				setTimeout( () => {
					copiedNotification.classList.remove( '-copied' );
				}, 1500 )
			} );
		</script>
	</div>
	<?php
}
add_action( 'admin_init', 'rmn_sn_admin_init' );
function rmn_sn_admin_init() {
	add_action( 'admin_post_save_rmn_sn_options', 'process_rmn_sn_options' );
}
function process_rmn_sn_options() {
	//Check that user has proper rights
	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( 'Not Allowed!' );
	}
	//Check the nonce
	check_admin_referer( 'rmn_sn' );

	if ( isset( $_POST['rmn_sn_posts_per_page'] ) ) {
		$rmn_sn_posts_per_page = sanitize_text_field( $_POST['rmn_sn_posts_per_page'] );
	} else {
		$rmn_sn_posts_per_page = 10;
	}
	if ( isset( $_POST['rmn_sn_display_date'] ) ) {
		$rmn_sn_display_date = true;
	} else {
		$rmn_sn_display_date = false;
	}
	if ( isset( $_POST['rmn_sn_display_title'] ) ) {
		$rmn_sn_display_title = true;
	} else {
		$rmn_sn_display_title = false;
	}
	if ( isset( $_POST['rmn_sn_date_format'] ) ) {
		$rmn_sn_date_format = sanitize_text_field( $_POST['rmn_sn_date_format'] );
	} else {
		$rmn_sn_date_format = 'd.m.Y';
	}

	update_option( 'rmn_sn_posts_per_page', $rmn_sn_posts_per_page );
	update_option( 'rmn_sn_display_date', $rmn_sn_display_date );
	update_option( 'rmn_sn_display_title', $rmn_sn_display_title );
	update_option( 'rmn_sn_date_format', $rmn_sn_date_format );


	wp_redirect(
		add_query_arg(
			'page',
			'rmn-short-news',
			admin_url( 'options-general.php' )
		) 
	);
	exit;
}

/**********************************
 
	Options
 
 **********************************/

 register_activation_hook( __FILE__, 'rmn_sn_set_default_options' );

 function rmn_sn_set_default_options() {
	if ( false === get_option( 'rmn_sn_posts_per_page' ) ) {
		add_option( 'rmn_sn_posts_per_page', 10 );
	}
	if ( false === get_option( 'rmn_sn_display_date' ) ) {
		add_option( 'rmn_sn_display_date', true );
	}
	if ( false === get_option( 'rmn_sn_display_title' ) ) {
		add_option( 'rmn_sn_display_title', false );
	}
	if ( false === get_option( 'rmn_sn_date_format' ) ) {
		add_option( 'rmn_sn_date_format', 'd.m.Y' );
	}

 }

/********************************
 
	Short code
 
 **********************************/

add_shortcode( 'short-news', 'rmn_sn_shortcode' );

function rmn_sn_shortcode( $atts ) {
	$output = '<ul class="rmn-short-news">';
	$output .= rmn_sn_news_list();
	$output .= '</ul>';
	return $output;
}
function rmn_sn_news_list() {
	$news_list = get_posts( array(
		'numberposts' => get_option( 'rmn_sn_posts_per_page' ),
		'post_type' => 'rmn_short_news'
	) );

	foreach ( $news_list as $news ) {
		$output .= '<li>';
		if ( $news->post_date && get_option( 'rmn_sn_display_date' ) ) {
			$output .= '<div class="-date">' . get_the_date( get_option( 'rmn_sn_date_format' ), $news ) . "</div>";
		}
		if ( $news->post_title && get_option( 'rmn_sn_display_title' ) ) {
			$output .= '<div class="-title">' . $news->post_title . "</div>";
		}
		if ( $news->post_content ) {
			$output .= '<div class="-content">' . $news->post_content . "</div>";
		}
		
		$output .= '</li>';
	}

	return $output;	
}