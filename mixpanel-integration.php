<?php
/**
 * Plugin Name: Mixpanel Integration
 * Plugin URI: https://www.example.com/mixpanel-integration
 * Description: A WordPress plugin to integrate Mixpanel functionalities, including tracking button clicks, page views, and search terms.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://www.example.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: mixpanel-integration
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

class Mixpanel_Integration {

	private $mixpanel_token;

	public function __construct() {
		add_action('admin_menu', array($this, 'register_admin_menu'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('wp_footer', array($this, 'track_page_view'));
		add_action('wp_ajax_track_search', array($this, 'track_search'));
		add_action('wp_ajax_nopriv_track_search', array($this, 'track_search'));
	}

	public function register_admin_menu() {
		add_options_page('Mixpanel Integration', 'Mixpanel Integration', 'manage_options', 'mixpanel-integration', array($this, 'admin_menu_callback'));
	}

	public function admin_menu_callback() {
		$this->mixpanel_token = get_option('mixpanel_token');
		?>
		<div class="wrap">
			<h1>Mixpanel Integration</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields('mixpanel_integration_group');
				do_settings_sections('mixpanel_integration_group');
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Mixpanel Token</th>
						<td><input type="text" name="mixpanel_token" value="<?php echo esc_attr($this->mixpanel_token); ?>" /></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	public function register_settings() {
		register_setting('mixpanel_integration_group', 'mixpanel_token');
	}

	public function enqueue_scripts() {
		$this->mixpanel_token = get_option('mixpanel_token');
		if ($this->mixpanel_token) {
			wp_enqueue_script('mixpanel', 'https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js', array(), null, true);
			wp_enqueue_script('mixpanel-integration', plugins_url('mixpanel-integration.js', __FILE__), array('jquery', 'mixpanel'), '1.0.0', true);

			$localize_data = array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'mixpanel_token' => $this->mixpanel_token
			);
			wp_localize_script('mixpanel-integration', 'mixpanel_integration_data', $localize_data);
		}
	}

	public function track_page_view() {
		if ($this->mixpanel_token) {
			?>
			<script type="text/javascript">
                mixpanel.init("<?php echo esc_js($this->mixpanel_token); ?>");
                mixpanel.track("Page View", {
                    "Page URL": window.location.href,
                    "Page Title": document.title
                });
			</script>
			<?php
		}
	}

	public function track_search() {
		$search_term = sanitize_text_field($_POST['search_term']);
		$this->mixpanel_token = get_option('mixpanel_token');

		if ($this->mixpanel_token) {
			?>
			<script type="text/javascript">
                mixpanel.init("<?php echo esc_js($this->mixpanel_token); ?>");
                mixpanel.track("Search", {
                    "Search Term": "<?php echo esc_js($search_term); ?>"
                });
			</script>
			<?php
		}
		wp_die();
	}

}

new Mixpanel_Integration();

