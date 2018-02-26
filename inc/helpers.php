<?php

namespace AOD\Plugin;

function dd() {
	foreach ( func_get_args() as $arg ) {
		echo '<pre>';
		print_r( $arg );
		echo '</pre>';
	}
	die();
}