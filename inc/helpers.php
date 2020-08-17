<?php

if ( ! function_exists( 'aod_config' ) ) {
    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function aod_config ( $key, $default = null) {
        return AOD\Plugin\Plugin::getInstance()->config($key, $default);
    }
}
