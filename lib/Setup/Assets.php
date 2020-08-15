<?php

namespace AOD\Plugin\Setup;

use AOD\Plugin\Config\ConfigRepository;
use AOD\Plugin\Setup\Assets\Asset;
use AOD\Plugin\Support\Contracts\BootableInterface;
use Illuminate\Support\Collection;

class Assets implements BootableInterface
{
    /**
     * @var Collection
     */
    protected $scripts;

    /**
     * @var Collection
     */
    protected $styles;

    /**
     * @var Collection
     */
    protected $localized;

    /**
     * @var ConfigRepository
     */
    protected $config;

    /**
     * @var bool|mixed
     */
    protected $is_admin = false;

    public function __construct( ConfigRepository $config, $admin = false)
    {
        $this->config = $config;
        $this->scripts = new Collection;
        $this->styles = new Collection;
        $this->localized = new Collection;
        $this->is_admin = $admin;
    }

    /**
     * @param bool $admin
     * @return $this
     */
    public function isAdmin( $admin = false )
    {
        $this->is_admin = $admin;
        return $this;
    }

    public function boot()
    {
        $prefix = $this->is_admin ? 'admin_' : 'wp_';
        add_action( "{$prefix}enqueue_scripts", [ $this, 'actionEnqueueScripts' ] );
    }

    /**
     * @param array $item
     *      $item = [
     *        'handle'       => (string) The name of the item, must be unique
     *        'source'       => (string) the URL location or path of the item.
     *        'dependencies' => (array) an array of string handles this item is dependant on
     *        'version'      => (string) the version of the script
     *        'footer'       => (bool) Show this in the footer?
     *        'conditional'  => (string) Conditional statements meant for IE or others
     *        'blocker'      => (callable) a function that must return a boolean to determine if the item is blocked
     *      ]
     */
    public function script( $item = [] )
    {
        $this->pushItem( $item, 'script' );
    }

    /**
     * @param array $item
     *      $item = [
     *        'handle'       => (string) The name of the item, must be unique
     *        'source'       => (string) the URL location or path of the item.
     *        'dependencies' => (array) an array of string handles this item is dependant on
     *        'version'      => (string) the version of the script
     *        'screen'       => (string) Where this is meant to be shown, mostly for styles, scrips don't use it
     *        'conditional'  => (string) Conditional statements meant for IE or others
     *        'blocker'      => (callable) a function that must return a boolean to determine if the item is blocked
     *      ]
     */
    public function style($item = [])
    {
        $this->pushItem($item, 'style');
    }

    /**
     * @param $handle
     * @param string $name
     * @param array $object
     */
    public function localize( $handle, $name = 'AODPlugin', $object = [] )
    {
        $this->localized->push( compact( 'handle', 'name', 'object' ) );
    }

    /**
     * Goes through the collections of assets, registers and enqueues them
     */
    public function actionEnqueueScripts()
    {
        // We first have to pull all the scripts that don't have a blocker. Blockers can be added to script or
        // style through the settings for each. It must be a callable
        //
        $this->scripts = $this->scripts->filter(function(Asset $item) {
            return $item->isBlocked() !== false;
        });

        // All scripts must be registered first to allow the ability to localize them using the WP api
        //
        $this->scripts->map(function(Asset $item) {
            wp_register_script(
                $item->handle,
                $item->source(),
                $item->dependencies,
                $item->version(),
                $item->footer
            );
        });

        // Now we go through each localized object and add it to the queue
        //
        $this->localized->map(function($object) {
            if(is_callable($object['object'])) {
                $object['object'] = call_user_func_array($object['object'], []);
            }

            wp_localize_script(
                $object['handle'],
                $object['name'],
                $object['object']
            );
        });

        // Now we enqueue the script
        //
        $this->scripts->map(function(Asset $item) {
            wp_enqueue_script($item->handle);

            if($item->conditional) {
                wp_script_add_data($item->handle, 'conditional', $item->conditional);
            }
        });

        // The same as scripts, we pull styles that aren't blocked. Each blocker, just like scripts, can
        // be added to the style settings when registered
        //
        $this->styles = $this->styles->filter(function(Asset $item) {
            return !$item->isBlocked();
        });

        // Styles don't use localized items, so we just enqueue them
        //
        $this->styles->map(function(Asset $item) {
            wp_register_style(
                $item->handle,
                $item->source(),
                $item->dependencies,
                $item->version(),
                $item->screen
            );
            wp_enqueue_style($item->handle);
        });
    }

    /**
     * Adds an item to either the script or style collections
     * @param array $item
     * @param string $type
     */
    protected function pushItem($item = [], $type = 'script')
    {
        if ( ! $item instanceof Asset ) {
            $item = new Asset(
                $item,
                $this->config->get('paths.assets_url'),
                $this->config->get('paths.assets')
            );
        }

        switch($type) {
            case 'script':
                $this->scripts->push($item);
                break;
            case 'style':
                $this->styles->push($item);
                break;
        }
    }
}
