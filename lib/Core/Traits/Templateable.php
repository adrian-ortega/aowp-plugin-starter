<?php

namespace AOD\Plugin\Core\Traits;

use AOD\Plugin\Core\Exceptions\RuntimeException;

trait Templateable {

	protected $useAdminTemplate = false;

	/**
	 * Prepares the template file path by checking if it's using dot notation
	 * and removing unnecessary paths or symbols
	 *
	 * @param string $file
	 *
	 * @return mixed|string
	 */
	private function _prepareTemplateFile( $file = '' ) {
		$file = str_replace( 'templates', '', $file );
		$file = ltrim( $file, '/' );
		$file = ltrim( $file, '.' );
		$file = str_replace( '.php', '', $file );

		if ( strpos( $file, '.' ) !== false ) {
			$tmpFile = '';
			$parts   = explode( '.', $file );
			while ( $part = array_shift( $parts ) ) {
				$tmpFile .= $part . ( count( $parts ) ? '/' : '' );
			}

			return $tmpFile;
		}

		return $file;
	}

	/**
	 * Returns HTML from a template file
	 *
	 * @param string $file
	 * @param array $data
	 *
	 * @return string
	 * @throws RuntimeException
	 * @internal param bool $admin
	 */
	protected function template( $file, $data = [] ) {
		if ( empty( $file ) ) {
			throw new RuntimeException( 'Cannot include a file with an empty path or file name.' );
		}

		$path = '';

		// Try to get the plugin path by checking for the container
		if ( method_exists( $this, 'get' ) ) {
			$path = $this->get( 'plugin_path' );
		} else if ( property_exists( $this, 'container' ) ) {
			$path = $this->container->get( 'plugin_path' );
		}

		// If the path is empty, it means we don't have a container to use
		if ( empty( $path ) ) {
			throw new RuntimeException( 'Please include a container in your parent class. Preferably use the Runable trait' );
		}

		// Prepare the file path
		$path .= ( $this->useAdminTemplate ? 'admin' : 'frontend' ) . '/templates/';
		$file = $path . $this->_prepareTemplateFile( $file ) . '.php';

		if ( ! file_exists( $file ) ) {
			throw new RuntimeException( sprintf( 'The template file: `%s` does not exist.', $file ) );
		}

		extract( $data );
		ob_start();
		include $file;

		return ob_get_clean();
	}

	/**
	 * Forwards the user to the 404 page
	 */
	public function pageNotFound() {
		global $wp_query;

		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
		include( get_query_template( '404' ) );
		die();
	}
}