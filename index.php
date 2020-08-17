<?php
/**
 * @link              https://github.com/adrian-ortega
 * @since             1.0.0
 * @package           aowp-plugin-starter
 *
 * @wordpress-plugin
 * Plugin Name:       AOWP Plugin Starer
 * Plugin URI:        https://github.com/adrian-ortega
 * Description:       A Wordpress Plugin Starter
 * Version:           2020.8.17
 * Author:            Adrian Ortega
 * Author URI:        https://github.com/adrian-ortega
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       aowp-plugin-starter
 * Domain Path:       /languages
 */

use AOD\Plugin\Plugin as AODPlugin;

include __DIR__ . '/vendor/autoload.php';

do_action('aod/before_plugin_loaded');

AODPlugin::getInstance();

do_action('aod/after_plugin_loaded');

