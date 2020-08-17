<?php

namespace AOD\Plugin\Providers;

use AOD\Plugin\Http\Controllers\AbstractController;
use AOD\Plugin\Http\Rest\Repository as RestControllerRepository;
use AOD\Plugin\Support\Helpers\FileSystemHelper;
use Illuminate\Support\Collection;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

class RestServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var string
     */
    protected $controllersPath;

    /**
     * @var string
     */
    protected $controllersNamespace;

    public function register()
    {
        // Not used but required by the AbstractServiceProvider
    }

    public function boot()
    {
        // We first have to initiate the repository using the container's
        // auto-wiring feature. This will make it easy for us to resolve
        // the dependencies of the repository object class.
        $controllerRepository = $this->getContainer()->get( RestControllerRepository::class );

        // The PHP Reflection class will help us get the relative data about controllers.
        // We will use this to auto load any controller with public methods and api routes
        // saved under the same directory as the Abstract Controller. Controllers can
        // also be nested in order to achive paths like /testimonials/citation/save
        //
        $reflection = new \ReflectionClass( AbstractController::class );

        // Grab the namespace out of the reflection class, we will need it to instantiate the
        // controllers that extend the AbstractController
        //
        $this->controllersNamespace = $reflection->getNamespaceName();

        // To properly store the root controller path, we make sure it has a trailing slash
        // and that all slashes are proper DIRECTORY_SEPARATORs
        //
        $this->controllersPath = str_replace('/', DIRECTORY_SEPARATOR,
            trailingslashit(  dirname( $reflection->getFileName() ) )
        );

        // Loop through each controller file, extract the controller class and add it to the
        // repository
        //
        foreach ($this->getControllers( ) as $controller) {
            $controllerRepository->addController( $controller );
        }

        // All done here, let's boot!
        $controllerRepository->boot();
    }

    /**
     * @return array
     */
    protected function getControllers( )
    {
        $ignore = ['.', '..', 'AbstractController.php'];

        // Extract the list of files that live under the root controller directory. We want to
        // ignore the AbtractController class for this, so we pass along a list of default
        // directory names to ignore as well as the file name.
        //
        $files = FileSystemHelper::getFilesFromDirectoryRecursive( $this->controllersPath, $ignore );

        return Collection::make( $files )

            // Extract each controller's fully qualified class name from the file. This is a recursive
            // function, so we have to pass the root path for it to start from
            //
            ->map( function( $file ) {
                return $this->getControllerNamespacedClassName(
                    $file,
                    $this->controllersPath,
                    $this->controllersNamespace
                );
            })

            // Since we have some files that might be nested (the getFilesFromDirectoryRecursive returns a
            // multi-dimensional array
            //
            ->flatten()

            // No need to house it in a collection anymore, we can loop through it as a flattened array
            //
            ->toArray();
    }

    /**
     * @param string $file
     * @param null $filePath
     * @param null $basePath
     * @return array|string|string[]
     */
    private function getControllerNamespacedClassName($file, $filePath = null, $basePath = null)
    {
        // In case we forget the namespace, we can extract it using the Reflection class
        //
        if ( empty($basePath) ) {
            $reflection = new \ReflectionClass( AbstractController::class );
            $basePath = $reflection->getNamespaceName();
        }

        // We have to ensure that the paths have proper directory separators
        //
        if ( $filePath ) {
            $filePath = str_replace( '/', DIRECTORY_SEPARATOR, $filePath );
        }

        // If the file we pass is an array, it means its a nested list of files under another
        // directory. Here we extract the extra path and append it to the base/root path
        // in order to register the controller path name properly.
        //
        if ( is_array( $file ) ) {
            return array_map( function( $file ) use( $filePath, $basePath ) {
                // We extract the extra path by removing the file name and root path, anything
                // left extra we can assume is the nested file path.
                //
                $extraPath = str_replace(basename($file), '', str_replace($filePath, '', $file));

                // Now we can recurse into extracting the class name for each
                //
                return $this->getControllerNamespacedClassName(
                    $file,
                    $filePath . $extraPath,
                    $basePath . $extraPath
                );
            }, $file);
        }

        return $basePath . str_replace($filePath, '', basename($file, '.php'));
    }
}
