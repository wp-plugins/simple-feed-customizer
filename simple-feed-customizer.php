<?php

// =============================================================================
// Simple Feed Customizer
// 
// Released under the GNU General Public Licence v2
// http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
// 
// Please refer all questions/requests to: admin@tech4sky.com
//
// This is a plugin for WordPress
// http://wordpress.org/
// =============================================================================

// =============================================================================
// This piece of software is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY, without even the implied warranty of MERCHANTABILITY or
// FITNESS FOR A PARTICULAR PURPOSE.
// =============================================================================


/*
Plugin Name: Simple Feed Customizer
Plugin URI: http://tech4sky.com/
Description: With this plugin, you can add copyright text at the end of each feed item, display feature image in feed and set the cache duration of feed.
Author: Agbonghama Collins
Version: 1.2.1
Author URI: http://tech4sky.com/
*/

// Initialize setting options on activation
register_activation_hook( __FILE__, 'sfc_install_activate_default_values' );
function sfc_install_activate_default_values() {
	$sfc_plugin_options = array(
		'read_more' => 'Read more...',
		'copyright' => "This article is copyright &copy; &nbsp;" . get_bloginfo( 'name' ),
		'feedImage' => '',
		'cachetime' => 7200,
		'size'      => 'medium',
	);
	update_option( 'sfc_simple_feed_customizer', $sfc_plugin_options );
}


// get option value from the database
$options             = get_option( 'sfc_simple_feed_customizer' );
$read_more           = $options['read_more'];
$copyright           = $options['copyright'];
$feed_image          = $options['feedImage'];
$feed_cache_duration = (int) $options['cachetime'];
$size                = $options['size'];

add_action( 'admin_menu', 'sfc_simple_feed_customizer_menu' );

// Adding Submenu to settings
function sfc_simple_feed_customizer_menu() {
	add_options_page( 'Simple Feed Customizer', 'Simple Feed Customizer',
		'manage_options', 'simple-feed-customizer-sfc', 'sfc_simple_feed_customizer' );
}

// plugin settings form
function sfc_simple_feed_customizer() {
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>Simple Feed Customizer</h2>

		<form action="options.php" method="post">
			<table class="form-table">
				<?php settings_fields( 'sfc_simple_feed_customizer' ); ?>
				<?php do_settings_sections( 'simple-feed-customizer-sfc' ); ?>
				<div>
					<input name="Submit" class="button-primary" type="submit" value="Save Changes"/>
				</div>

		</form>
		</table>
		<div style="width: 600px; text-align: center; margin: 10px auto; padding: 2px; background-color: #e3e3e3; border: 1px solid #DDDDDD">
			<p>

			<h3>See Also</h3>
			<strong><a target="_blank" href="https://wordpress.org/plugins/ppress/">ProfilePress</a></strong>: A shortcode based WordPress form builder that makes building custom login, registration and password reset forms stupidly simple.
			</p>
		</div>
	</div>

<?php }

// Register and define the settings
add_action( 'admin_init', 'sfc_simple_feed_customizer_init' );
function sfc_simple_feed_customizer_init() {
	register_setting(
		'sfc_simple_feed_customizer',
		'sfc_simple_feed_customizer'
	);
	add_settings_section(
		'simple-feed-customizer-readmore',
		'',
		'sfc_simple_feed_customizer_text',
		'simple-feed-customizer-sfc'
	);
	add_settings_field(
		'simple-feed-customizer-sfc',
		'',
		'sfc_simple_feed_customizer_setting_input',
		'simple-feed-customizer-sfc',
		'simple-feed-customizer-readmore'
	);
}

// Draw the section header
function sfc_simple_feed_customizer_text() {

}

// Display and fill the form field
function sfc_simple_feed_customizer_setting_input() {
	// Retrieve the settings values form DB and make them global
	global $read_more, $copyright, $feed_image, $feed_cache_duration, $size;
	echo "
	<tr valign='top'>
	<th scope='row'><label for='read_more'> <strong>Read more text</strong> &nbsp; ( Leave this field empty if your feed is not Summary )</label></th>";
	echo "<td><input id='read_more' name='sfc_simple_feed_customizer[read_more]' type='text' value='$read_more' /><td>
	</tr>";

	echo "
	<tr valign='top'>
	<th scope='row'><label for='copyright'><strong> Copyright Text </strong></label></th>";
	echo "<td>	<textarea name='sfc_simple_feed_customizer[copyright]' cols='50' rows='3'>$copyright</textarea><td>
	</tr>";

	echo "
	<tr valign='top'>
	<th scope='row'><label for='fname'><strong> Add Feature Image to feed </strong></label></th>";
	?> <td><input type="checkbox" name="sfc_simple_feed_customizer[feedImage]" value="1" <?php checked( $feed_image, 1 ); ?> /></td>
</tr>
<?php
	echo "
	<tr valign='top'>
	<th scope='row'><label for='copyright'><strong>Feature Image Size </strong></label></th>"; ?>
	<td>	
	<select name='sfc_simple_feed_customizer[size]'>
	<option value='thumbnail' <?php selected( $size, 'thumbnail' ) ?> >Thumbnail</option>
	<option value='medium'<?php selected( $size, 'medium' ); ?> >Medium resolution</option>
	<option value='large' <?php selected( $size, 'large' ); ?> >Large resolution</option>
	<option value='full' <?php
	selected( $size, 'full' ); ?> >Original resolution</option>
	</select></td>
	</tr>
	
<?php echo "
<tr valign='top'>
	<th scope='row'><label for='cachetime'><strong> Feed Cache Duration </strong> (in Seconds)</label></th>";
	echo "<td><input id='read_more' name='sfc_simple_feed_customizer[cachetime]' type='text' value='$feed_cache_duration' /><td>
</tr>";
}

// Feature image function
function add_featured_image_to_feed( $content ) {
	global $post, $size;
	if ( has_post_thumbnail( $post->ID ) ) {
		$content = '
<div>
' . get_the_post_thumbnail( $post->ID, $size ) . '
</div>' . $content;
	}

	return $content;
}

// if feature image settings is checked, display it
if ( $feed_image == 1 ) {
	add_filter( 'the_excerpt_rss', 'add_featured_image_to_feed', 1000, 1 );
	add_filter( 'the_content_feed', 'add_featured_image_to_feed', 1000, 1 );
}

// Add readmore copyright text to each feed item
function add_feed_content( $content ) {
	global $read_more, $copyright, $post;
	if ( is_feed() ) {
		$content .= '<strong> &nbsp; <a href="' . get_permalink( $post->ID ) . '">' . $read_more . '</a></strong>';
		$content .= "
<div style='margin:2px'>
<p>
<strong>$copyright</strong>
</p>
</div>";
	}

	return $content;
}

add_filter( 'the_excerpt_rss', 'add_feed_content' );
add_filter( 'the_content_feed', 'add_feed_content' );

// function to set the feed cache duration is seconds
function return_cache_time( $seconds ) {
	global $feed_cache_duration;

// change the default feed cache recreation period to 2 hours
	return (int) $feed_cache_duration;
}

if ( isset( $feed_cache_duration ) ) {
//set feed cache duration
	add_filter( 'wp_feed_cache_transient_lifetime', 'return_cache_time' );
}