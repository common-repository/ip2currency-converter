<?php
/*
Plugin Name: IP2CurrencyConverter
Plugin URI: http://www.ip2currency.com
Description: IP2Currency Converter provides you a nice and ready-to-use widget to easily add the currency exchange rate function into your website. For details, please visit http://www.ip2currency.com
Version: 1.0.10
Author: IP2Currency
Author URI: http://www.ip2currency.com
*/

add_action('widgets_init', ['IP2CurrencyConverter', 'register']);
add_action('admin_menu', ['IP2CurrencyConverter', 'menu']);
add_action('admin_head', ['IP2CurrencyConverter', 'farbtastic']);
add_action('admin_enqueue_scripts', ['IP2CurrencyConverter', 'plugin_enqueues']);
add_action('wp_ajax_ip2currency_converter_submit_feedback', ['IP2CurrencyConverter', 'submit_feedback']);
add_action('admin_footer_text', ['IP2CurrencyConverter', 'admin_footer_text']);

class IP2CurrencyConverter
{
	public function activate()
	{
		if (!function_exists('register_sidebar_widget')) {
			return;
		}

		$options = ['title' => 'IP2CurrencyConverter'];

		if (!get_option('IP2CurrencyConverter')) {
			add_option('IP2CurrencyConverter', $options);
		} else {
			update_option('IP2CurrencyConverter', $options);
		}
	}

	public function deactivate()
	{
		delete_option('IP2CurrencyConverter');
	}

	public function control()
	{
		echo '<a href="options-general.php?page=' . basename(__FILE__) . '">Go to Settings</a>';
	}

	public function widget($args)
	{
		$options = get_option('IP2CurrencyConverter');

		echo $args['before_widget'] . $args['before_title'] . $options['title'] . $args['after_title'];
		echo '
		<script type="text/javascript">
		<!--
			document.write(\'<iframe id="ip2currencyconverter-frame" src="http://www.ip2currency.com/widget?size=' . $options['size'] . '&skin=' . $options['skin'] . '&key=' . $options['key'] . '&bgColor=' . $options['bgColor'] . '&borderColor=' . $options['borderColor'] . '&fontColor=' . $options['fontColor'] . '" frameborder="0" scrolling="no" style="' . (($options['size'] == 1) ? 'width:210px;height:338px' : 'width:406px;height:232px') . ';"></iframe>\');
		//-->
		</script>';

		echo $args['after_widget'];
	}

	public function menu()
	{
		add_submenu_page('options-general.php', 'IP2CurrencyConverter', 'IP2CurrencyConverter', 'administrator', basename(__FILE__), ['IP2CurrencyConverter', 'setting']);
	}

	public function setting()
	{
		$skins = [0 => 'No Skin', 1 => 'Blue', 2 => 'Light Blue', 3 => 'Light Orange', 4 => 'Light Yellow', 5 => 'Light Green', 6 => 'Lime', 7 => 'Blood Red', 8 => 'Pink', 9 => 'Milk Grey'];

		$options = get_option('IP2CurrencyConverter');

		if ($_POST['ip2currencyconverter-title']) {
			if (!in_array($_POST['ip2currencyconverter-size'], [1, 2])) {
				$_POST['ip2currencyconverter-size'] = 1;
			}
			if (!in_array($_POST['ip2currencyconverter-skin'], range(0, 9))) {
				$_POST['ip2currencyconverter-skin'] = 0;
			}

			if (empty($_POST['ip2currencyconverter-background-color'])) {
				$options['bgColor'] = 'FFFFFF';
			}
			if (empty($_POST['ip2currencyconverter-border-color'])) {
				$options['borderColor'] = '000000';
			}
			if (empty($_POST['ip2currencyconverter-font-color'])) {
				$options['borderColor'] = '000000';
			}

			if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $_POST['ip2currencyconverter-background-color'])) {
				$_POST['ip2currencyconverter-background-color'] = $options['bgColor'];
			}
			if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $_POST['ip2currencyconverter-border-color'])) {
				$_POST['ip2currencyconverter-border-color'] = $options['borderColor'];
			}
			if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $_POST['ip2currencyconverter-font-color'])) {
				$_POST['ip2currencyconverter-font-color'] = $options['fontColor'];
			}

			$data['title'] = strip_tags(stripslashes($_POST['ip2currencyconverter-title']));
			$data['key'] = strip_tags(stripslashes($_POST['ip2currencyconverter-key']));
			$data['size'] = strip_tags(stripslashes($_POST['ip2currencyconverter-size']));
			$data['skin'] = strip_tags(stripslashes($_POST['ip2currencyconverter-skin']));
			$data['bgColor'] = ltrim(strip_tags(stripslashes($_POST['ip2currencyconverter-background-color'])), '#');
			$data['borderColor'] = ltrim(strip_tags(stripslashes($_POST['ip2currencyconverter-border-color'])), '#');
			$data['fontColor'] = ltrim(strip_tags(stripslashes($_POST['ip2currencyconverter-font-color'])), '#');

			update_option('IP2CurrencyConverter', $data);
			$options = get_option('IP2CurrencyConverter');

			echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Settings saved.</strong></p></div> ';
		}

		if (!is_array($options)) {
			$options = [
			'title'       => 'Currency Converter',
			'key'         => '',
			'size'        => '1',
			'skin'        => '0',
			'bgColor'     => 'FFFFFF',
			'borderColor' => '000000',
			'fontColor'   => '000000',
		];
		}

		$skinOptions = '';
		foreach ($skins as $key => $value) {
			$skinOptions .= '<option value="' . $key . '"' . (($options['skin'] == $key) ? ' selected' : '') . '> ' . $value . '</option>';
		}

		echo '
		<div class="wrap">
			<div id="icon-options-general" class="icon32"></div>
			<h2>IP2CurrencyConverter Settings</h2>
			<p>&nbsp;</p>
			<form id="form-ip2currency" method="post">
			<table>
			<tr>
				<td valign="top">
					<table>
					<tr>
						<td width="150"><b>Title</b></td>
						<td><input style="width:240px;" name="ip2currencyconverter-title" type="text" value="' . htmlspecialchars($options['title'], ENT_QUOTES) . '" /></td>
					</tr>
					<tr>
						<td width="150"><b>API Key</b> (<a href="http://www.fraudlabs.com/freelicense.aspx?PackageID=10" target="_blank">Get key</a>)</td>
						<td><input style="width:240px;" name="ip2currencyconverter-key" id="ip2currencyconverter-key" type="text" value="' . htmlspecialchars($options['key'], ENT_QUOTES) . '" /></td>
					</tr>
					<tr>
						<td width="150"><b>Size</b></td>
						<td>
							<input type="radio" name="ip2currencyconverter-size" id="ip2currencyconverter-size1" value="1"' . (($options['size'] == 1) ? ' checked' : '') . ' /> <label for="ip2currencyconverter-size1">200px x 328px</label>
							&nbsp; &nbsp; &nbsp;
							<input type="radio" name="ip2currencyconverter-size" id="ip2currencyconverter-size2" value="2"' . (($options['size'] == 2) ? ' checked' : '') . ' /> <label for="ip2currencyconverter-size2">396px x 222px</label>
						</td>
					</tr>
					<tr>
						<td width="150"><b>Skin</b></td>
						<td>
						<select name="ip2currencyconverter-skin" id="ip2currencyconverter-skin" style="width:240px;">
							' . $skinOptions . '
						</select>
						</td>
					</tr>
					<tr>
						<td width="150"><b>Background Color</b></td>
						<td><input onblur="refreshPreview();" style="width:240px;" name="ip2currencyconverter-background-color" id="ip2currencyconverter-background-color" type="text" value="' . htmlspecialchars('#' . $options['bgColor'], ENT_QUOTES) . '" maxlength="7" class="color-picker" /></td>
					</tr>
					<tr>
						<td></td>
						<td><div id="farbtastic-ip2currencyconverter-background-color"></div></td>
					</tr>
					<tr>
						<td width="150"><b>Border Color</b></td>
						<td><input onblur="refreshPreview();" style="width:240px;" name="ip2currencyconverter-border-color" id="ip2currencyconverter-border-color" type="text" value="' . htmlspecialchars('#' . $options['borderColor'], ENT_QUOTES) . '" maxlength="7" class="color-picker" /></td>
					</tr>
					<tr>
						<td></td>
						<td><div id="farbtastic-ip2currencyconverter-border-color"></div></td>
					</tr>
					<tr>
						<td width="150"><b>Font Color</b></td>
						<td><input onblur="refreshPreview();" style="width:240px;" name="ip2currencyconverter-font-color" id="ip2currencyconverter-font-color" type="text" value="' . htmlspecialchars('#' . $options['fontColor'], ENT_QUOTES) . '" maxlength="7" class="color-picker" /></td>
					</tr>
					<tr>
						<td></td>
						<td><div id="farbtastic-ip2currencyconverter-font-color"></div></td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2">
						<b>JavaScript:</b><br />
						<div style="width:400px;">You can insert this widget into non Wordpress website as well. Just copy and paste the following codes into your website.</div>
						<textarea id="jsCode" style="width:400px;height:150px;" onfocus="this.select();"><script type="text/javascript">
<!--
	document.write(\'<iframe id="ip2currencyconverter-frame" src="http://www.ip2currency.com/widget?size=' . $options['size'] . '&skin=' . $options['skin'] . '&key=' . $options['key'] . '&bgColor=' . $options['bgColor'] . '&borderColor=' . $options['borderColor'] . '&fontColor=' . $options['fontColor'] . '" frameborder="0" scrolling="no" style="' . (($options['size'] == 1) ? 'width:210px;height:338px' : 'width:406px;height:232px') . ';"></iframe>\');
//-->
</script></textarea>
						</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>
							<input type="submit" name="submit" class="button-primary" value="Save Changes" />
						</td>
					</tr>
					</table>
				</td>
				<td width="100">&nbsp;</td>
				<td valign="top">
				<b>Preview:</b><br />
				<p>
					<iframe id="ip2currencyconverter-frame" src="http://www.ip2currency.com/widget?size=' . $options['size'] . '&skin=' . $options['skin'] . '&key=' . $options['key'] . '&bgColor=' . $options['bgColor'] . '&borderColor=' . $options['borderColor'] . '" frameborder="0" scrolling="no" style="width:406px;height:338px;"></iframe>
				</p>
				</td>
			</tr>
			</table>
			</form>

			<p>&nbsp;</p>
		</div>

		<script type="text/javascript">
			jQuery(function(){
				jQuery(document).ready(function() {
				    jQuery(\'.color-picker\').each(function() {
				    	jQuery(\'#farbtastic-\'+this.id).hide();
				    	jQuery(\'#farbtastic-\'+this.id).farbtastic(this);
				    	jQuery(this).click(function(){jQuery(\'#farbtastic-\'+this.id).fadeIn()});
						jQuery(this).blur(function(){jQuery(\'#farbtastic-\'+this.id).hide()});
					});

					jQuery(\'input[type=radio],select\').each(function() {
				    	jQuery(this).change(function(){ refreshPreview(); });
					});

					jQuery(\'inupt[type=text]\').each(function() {
				    	jQuery(this).blur(function(){ refreshPreview(); });
					});
				});
			});

			jQuery(document).mousedown(function() {
				jQuery(\'.color-picker\').each(function() {
					var display = jQuery(\'#\'+this.id).css(\'display\');
					if(display == \'block\') jQuery(\'#\'+this.id).fadeOut();
				});
			});

			function refreshPreview(){
				var key = jQuery("#ip2currencyconverter-key").val();
				var size = (jQuery("#ip2currencyconverter-size1").is(":checked")) ? 1 : 2;
				var skin = jQuery("#ip2currencyconverter-skin").val();
				var bgColor = jQuery("#ip2currencyconverter-background-color").val();
				var borderColor = jQuery("#ip2currencyconverter-border-color").val();
				var fontColor = jQuery("#ip2currencyconverter-font-color").val();

				bgColor = bgColor.replace("#", "");
				borderColor = borderColor.replace("#", "");
				fontColor = fontColor.replace("#", "");

				var out = [\'<script type="text/javascript">\',
				\'<!--\',
				\'	document.write(\\\'<iframe id="ip2currencyconverter-frame" src="http://www.ip2currency.com/widget?size=\' + size + \'&skin=\' + skin + \'&key=\' + key + \'&bgColor=\' + bgColor + \'&borderColor=\' + borderColor + \'&fontColor=\' + fontColor + \'" frameborder="0" scrolling="no" style="width:\' + ((size == 1) ? 210 : 406) + \'px;height:\' + ((size == 1) ? 338 : 232) + \'px;"></iframe>\\\');\',
				\'//-->\',
				\'<\/script>\'];

				jQuery("#jsCode").val(out.join("\n"));

				jQuery("#ip2currencyconverter-frame").attr("src", "http://www.ip2currency.com/widget?key=" + key + "&size=" + size + "&skin=" + skin + "&bgColor=" + bgColor + "&borderColor=" + borderColor + "&fontColor=" + fontColor);
			}
		</script>
		';
	}

	public function farbtastic()
	{
		global $current_screen;

		if ($current_screen->id == 'IP2CurrencyConverter.php') {
			wp_enqueue_style('farbtastic');
			wp_enqueue_script('farbtastic');
		}
	}

	public function register()
	{
		register_sidebar_widget('IP2CurrencyConverter', ['IP2CurrencyConverter', 'widget']);
		register_widget_control('IP2CurrencyConverter', ['IP2CurrencyConverter', 'control']);

		wp_enqueue_style('farbtastic');
		wp_enqueue_script('farbtastic');
	}

	public function admin_footer_text($footer_text)
	{
		$plugin_name = substr(basename(__FILE__), 0, strpos(basename(__FILE__), '.'));
		$current_screen = get_current_screen();

		if (($current_screen && strpos($current_screen->id, $plugin_name) !== false)) {
			$footer_text .= sprintf(
				__('Enjoyed %1$s? Please leave us a %2$s rating. A huge thanks in advance!', $plugin_name),
				'<strong>' . __('IP2Currency Converter', $plugin_name) . '</strong>',
				'<a href="https://wordpress.org/support/plugin/' . $plugin_name . '/reviews/?filter=5/#new-post" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}

		if ($current_screen->id == 'plugins') {
			return $footer_text . '
			<div id="ip2currency-converter-feedback-modal" class="hidden" style="max-width:800px">
				<span id="ip2currency-converter-feedback-response"></span>
				<p>
					<strong>Would you mind sharing with us the reason to deactivate the plugin?</strong>
				</p>
				<p>
					<label>
						<input type="radio" name="ip2currency-converter-feedback" value="1"> I no longer need the plugin
					</label>
				</p>
				<p>
					<label>
						<input type="radio" name="ip2currency-converter-feedback" value="2"> I couldn\'t get the plugin to work
					</label>
				</p>
				<p>
					<label>
						<input type="radio" name="ip2currency-converter-feedback" value="3"> The plugin doesn\'t meet my requirements
					</label>
				</p>
				<p>
					<label>
						<input type="radio" name="ip2currency-converter-feedback" value="4"> Other concerns
						<br><br>
						<textarea id="ip2currency-converter-feedback-other" style="display:none;width:100%"></textarea>
					</label>
				</p>
				<p>
					<div style="float:left">
						<input type="button" id="ip2currency-converter-submit-feedback-button" class="button button-danger" value="Submit & Deactivate" />
					</div>
					<div style="float:right">
						<a href="#">Skip & Deactivate</a>
					</div>
				</p>
			</div>';
		}

		return $footer_text;
	}

	public function submit_feedback()
	{
		$feedback = (isset($_POST['feedback'])) ? $_POST['feedback'] : '';
		$others = (isset($_POST['others'])) ? $_POST['others'] : '';

		$options = [
			1 => 'I no longer need the plugin',
			2 => 'I couldn\'t get the plugin to work',
			3 => 'The plugin doesn\'t meet my requirements',
			4 => 'Other concerns' . (($others) ? (' - ' . $others) : ''),
		];

		if (isset($options[$feedback])) {
			if (!class_exists('WP_Http')) {
				include_once ABSPATH . WPINC . '/class-http.php';
			}

			$request = new WP_Http();
			$response = $request->request('https://www.ip2location.com/wp-plugin-feedback?' . http_build_query([
				'name'    => 'ip2currency-converter',
				'message' => $options[$feedback],
			]), ['timeout' => 5]);
		}
	}

	public function plugin_enqueues($hook)
	{
		if ($hook == 'plugins.php') {
			// Add in required libraries for feedback modal
			wp_enqueue_script('jquery-ui-dialog');
			wp_enqueue_style('wp-jquery-ui-dialog');

			wp_enqueue_script('ip2currency_converter_admin_script', plugins_url('/assets/js/feedback.js', __FILE__), ['jquery'], null, true);
		}
	}
}
