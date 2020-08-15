<?php

namespace AOD\Plugin\Providers;

use AOD\Plugin\Setup\Assets;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

class AssetsServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    public function boot()
    {
        // Example:
        //
        //
        // $config = $this->container->get('config');
        //
        // $frontAssets = new Assets( $config );
        //
        // $frontAssets->style([
        //     'handle' => 'aod-front',
        //     'source' => 'styles/app.css'
        // ]);
        //
        // $frontAssets->script([
        //     'handle' => 'aod-front',
        //     'source' => 'scripts/app.js'
        // ]);
        //
        // $frontAssets->localize('aod-front', 'AODPlugin', [
        //     'assetUrl' => $config->get('paths.assets_url')
        // ]);
        //
        // $frontAssets->boot();
    }

    public function register()
    {
        // Registers nothing, only used to boot up the Assets objects
    }
}
