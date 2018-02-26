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
 * Version:           2017.0.1
 * Author:            Adrian Ortega
 * Author URI:        https://github.com/adrian-ortega
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       aowp-plugin-starter
 * Domain Path:       /languages
 */

include 'vendor/autoload.php';

$plugin = new \AOD\Plugin\Plugin( __FILE__, 'AOWP Plugin Starer' );

$plugin->load( 'admin_scripts', new \AOD\Plugin\Admin\Scripts() );
$plugin->load( 'front_scripts', new \AOD\Plugin\Frontend\Scripts() );

$plugin->run();