<?php
/*
Plugin Name: Mobile Redirect Each Post
Description: Select a URL at each post to point mobile users to. Avoid mobile SEO penalty.
Author: Cymait
Version: 1.0
Author URI: http://cymait.com/
*/

/*	Copyright 2013 Cymait (email : info@cymait.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

*/

$mobile_redirect_per_post = new Mobile_Redirect_Per_Post();

register_uninstall_hook( __FILE__, 'uninstall_mobile_redirect_per_post' );
function uninstall_mobile_redirect() {
	delete_option( 'mobileredirecttoggle' );
	delete_option( 'mobileredirectmode' );
	delete_option( 'mobileredirecttablet' );
	delete_option( 'mobileredirectonce' );
	delete_option( 'mobileredirectoncedays' );
}

class Mobile_Redirect_Per_Post {

	function __construct() { //init function
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'template_redirect', array( &$this, 'template_redirect' ) );
		add_action( 'send_headers', array( &$this, 'add_header_vary' ) );
		add_action('wp_head', array( &$this, 'head_meta_data') );
		// upgrade option from 1.1 to 1.2
		if ( get_option( 'mobileredirecttoggle' ) == 'true' )
			update_option( 'mobileredirecttoggle', true );
	}

	function admin_menu() {
		add_submenu_page( 'options-general.php', __( 'Mobile Redirect Each Post', 'mobile-redirect-per-post' ), __( 'Mobile Redirect Each Post', 'mobile-redirect-per-post' ), 'administrator', __FILE__, array( &$this, 'page' ) );
	}

	function page() { //admin options page
 
		//do stuff if form is submitted
		if ( isset( $_POST['mobilemode'] ) ) {
			//update_option( 'mobileredirecturl', esc_url_raw( $_POST['mobileurl'] ) );
			update_option( 'mobileredirecttoggle', isset( $_POST['mobiletoggle'] ) ? true : false );

			update_option( 'mobileredirectmode', intval( $_POST['mobilemode'] ) );
			update_option( 'mobileredirecttablet', isset( $_POST['mobileredirecttablet'] ) );

			update_option( 'mobileredirectonce', isset( $_POST['mobileredirectonce'] ) ? true : false );
			update_option( 'mobileredirectoncedays', intval( $_POST['mobileredirectoncedays'] ) );

			echo '<div class="updated"><p>' . __( 'Updated', 'mobile-redirect-per-post' ) . '</p></div>';
		}

		?>
		<div class="wrap"><h2><?php _e( 'Mobile Redirect Each Post', 'mobile-redirect-per-post' ); ?></h2>
		<p>
			<?php _e( 'If the checkbox is checked, and a valid URL is inputted, this site will redirect to the specified URL when visited by a mobile device.', 'mobile-redirect-per-post' ); ?>
		</p>

		<form method="post">
		<p>
			<label for="mobiletoggle"><?php _e( 'Enable Redirect:', 'mobile-redirect-per-post' ); ?>
			<input type="checkbox" value="1" name="mobiletoggle" id="mobiletoggle" <?php checked( get_option('mobileredirecttoggle', ''), 1 ); ?> /></label>
		</p>
		<p>
			<label for="mobilemode"><?php _e( 'Redirect Mode:', 'mobile-redirect-per-post' ); ?>
			<select id="mobilemode" name="mobilemode">
				<option value="301" <?php selected( get_option('mobileredirectmode', 301 ), 301 ); ?>>301</option>
				<option value="302" <?php selected( get_option('mobileredirectmode'), 302 ); ?>>302</option>
			</select>
			</label>
		</p>
		<p>
			<label for="mobileredirecttablet"><?php _e( 'Redirect Tablets:', 'mobile-redirect-per-post' ); ?>
			<input type="checkbox" value="1" name="mobileredirecttablet" id="mobileredirecttablet" <?php checked( get_option('mobileredirecttablet', ''), 1 ); ?> /></label>
		</p>
		<p>
			<label for="mobileredirectonce"><?php _e( 'Redirect Once:', 'mobile-redirect-per-post' ); ?>
			<input type="checkbox" value="1" name="mobileredirectonce" id="mobileredirectonce" <?php checked( get_option('mobileredirectonce', ''), 1 ); ?> /></label>
		</p>
		<p>
			<label for="mobileredirectoncedays"><?php _e( 'Redirect Once Cookie Expiry:', 'mobile-redirect-per-post' ); ?>
			<input type="text" name="mobileredirectoncedays" id="mobileredirectoncedays" value="<?php echo esc_attr( get_option('mobileredirectoncedays', 7 ) ); ?>" /> days.</label>
			<span class="description">If <em>Redirect Once</em> is checked, a cookie will be set for the user to prevent them from being continually redirected to the same page. This cookie will expire by default after 7 days. Setting to zero or less is effectively the same as unchecking Redirect Once</span>
		</p>
			<?php submit_button(); ?>
		</form>
		</div>

		<div class="copyFooter">Plugin written by <a href="http://cymait.com/?utm_source=wpadmin&utm_medium=link&utm_campaign=opensource">Cymait</a>, modified from <a href="http://ozette.com/">Ozette Plugins</a>.</div>
		<?php
	}

	function is_mobile() {
		$mobile_browser = '0';
		if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
			$mobile_browser++;
		}
		if((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
			$mobile_browser++;
		}    
		$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
		$mobile_agents = array(
			'w3c ','acs-','alav','alca','amoi','audi','avan','andr','benq','bird','blac',
			'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
			'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
			'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
			'newt','noki','palm','pana','pant','phil','play','port','prox',
			'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
			'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
			'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
			'wapr','webc','winw','winw','xda','xda-');
		if(in_array($mobile_ua,$mobile_agents)) {
			$mobile_browser++;
		}
		if (isset($_SERVER['ALL_HTTP']) && strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini')>0) {
			$mobile_browser++;
		}	
		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'mobile safari')>0) {
			$mobile_browser++;
		}
		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows')>0) {
			$mobile_browser=0;
		}
		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'android')>0) {
			$mobile_browser++;
		}
		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'Googlebot-Mobile')>0) {
			$mobile_browser++;
		}
		
		if($mobile_browser>0) { return true; }
		else { return false; }
	}
	
	function get_mobile_redirect_url() {
		global $wp_query; 
		$post_id = $wp_query->post->ID;
		return esc_url( get_post_meta($post_id, 'mobile_redirect_url', true) );
	}
	
	function head_meta_data() {
		$mr_url = $this->get_mobile_redirect_url();
		// empty url
		if ( empty( $mr_url ) )
			return;
			
		echo '<link rel="alternate" media="only screen and (max-width: 640px), only screen and (min-device-width: 560px) and (max-device-width: 1136px) and (-webkit-min-device-pixel-ratio: 2)" href="'.$mr_url.'" >';
	}
	
	function add_header_vary() {
		//TODO check if User-agent header exists in Vary header, and if it doesn't, then add it.
	}

	function template_redirect() {
		//check if tablet box is checked
		if( get_option('mobileredirecttablet', false) == 0) {
			//redirect non-tablets
			if(!self::is_mobile() )
				return;
		} else {
			// not mobile
			if ( ! wp_is_mobile() )
				return;
		}
		

		// not enabled
		if ( ! get_option('mobileredirecttoggle') )
			return;
			

		$mr_url = $this->get_mobile_redirect_url();
		// empty url
		if ( empty( $mr_url ) )
			return;

				
		$cur_url = esc_url("http://". $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] );
		$cookiedays = intval( get_option( 'mobileredirectoncedays', 7 ) );
		// cookie can be expired by setting to a negative number
		// but it's better just to uncheck the redirect once option
		if ( $cookiedays <= 0 || ! get_option( 'mobileredirectonce', false ) ) {
			setcookie( 'mobile_single_redirect', true, time()-(60*60), '/' );
			unset($_COOKIE['mobile_single_redirect']);
		}

		// make sure we don't redirect to ourself
		if ( $mr_url != $cur_url ) {
			if ( isset( $_COOKIE['mobile_single_redirect'] ) ) return;

			if ( get_option( 'mobileredirectonce', '' ) )
				setcookie( 'mobile_single_redirect', true, time()+(60*60*24*$cookiedays ), '/' );

			wp_redirect( $mr_url, get_option('mobileredirectmode', '301' ) );
			exit;
		}

	}

}
