<?php

namespace AOD\Plugin\Http\Rest;

use AOD\Plugin\Config\ConfigRepository;
use AOD\Plugin\Http\Controllers\AbstractController;
use AOD\Plugin\Http\Request;
use AOD\Plugin\Http\Response;
use AOD\Plugin\Http\StatusCode;
use AOD\Plugin\Support\Contracts\BootableInterface;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use ReflectionException;

class Repository implements BootableInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var Collection
     */
    protected $actions;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var array|\ArrayAccess|mixed|void
     */
    protected $apiNamespace;

    /**
     * @var string
     */
    protected $apiVersion = 'v1';

    /**
     * @var array|\ArrayAccess|mixed|void
     */
    protected $pluginName;

    /**
     * Repository constructor.
     * @param Request $request
     * @param ConfigRepository $config
     * @param Factory $factory
     */
    public function __construct(Request $request, ConfigRepository $config, Factory $factory)
    {
        $this->actions      = new Collection();
        $this->request      = $request;
        $this->factory      = $factory;
        $this->apiNamespace = $config->get('rest.endpoint_name', 'aod');
        $this->pluginName   = $config->get('plugin_name');
    }

    public function boot()
    {
        if ( defined('DOING_AJAX') && DOING_AJAX ) {
            add_action('wp_ajax_' . $this->apiNamespace, [ $this, 'action' ]);
            add_action('wp_ajax_nopriv_' . $this->apiNamespace, [ $this, 'action' ]);
        }

        add_action( 'rest_api_init', [ $this, 'registerApiEndpoints' ] );
    }

    /**
     * @throws Exception
     */
    public function action()
    {
        $controller = $this->request->input('controller');

        // No longer needed
        $this->request->forget('controller');
        $this->request->forget('action');

        // Split up the controller to search for a corresponding class object
        $controllerAndMethod = preg_split( "/(@|\.|::)/", $controller );
        $controllerAndMethod = array_replace(['none', 'notFound'], $controllerAndMethod);

        list ( $controller, $method ) = $controllerAndMethod;

        try {
            $controller = $this->actions->get( $controller );
            if ( ! $controller || ! $this->factory->hasPublicMethod( $controller, $method ) ) {
                throw new Exception( !$controller ? 'Controller not found' : 'Method not found');
            }

            $response = $this->factory->callMethodFromController( $controller, $method );

            if ( ! $response instanceof Response ) {
                $response = new Response( $response );
            }

            $response->send();
        } catch (Exception $exception ) {
            error_log($exception);

            $response = new Response([
                'message' => 'Something went wrong',
                'errors' => [[
                    'name' => 'exception',
                    'message' => $exception->getMessage()
                ]]
            ]);

            $response->withStatus( StatusCode::HTTP_FORBIDDEN )->send();
        }
    }

    /**
     * @throws ReflectionException
     */
    public function registerApiEndpoints()
    {
        // In case we have a NotFoundController, we ignore it as it's used later.
        //
        $actions = $this->actions->filter ( function( $action, $key ) {
            return $key !== 'not-found';
        });

        $actions->each( function ( AbstractController $controller ) {
            foreach($controller->getApiRoutes() as $route => $routeArgs) {
                $requestMethod = Arr::get($routeArgs, 'methods', ['GET']);
                $requestArgs = Arr::get($routeArgs, 'args', []);

                $routePath = "{$controller->getName()}/{$route}";

                if($route === 'index') {
                    $routePath = $controller->getName();
                }

                register_rest_route($this->getApiNamespace(), $routePath, [
                    'methods' => $requestMethod,
                    'args' => $requestArgs,
                    'callback' => $this->factory->callback($controller, $route),
                ]);
            }
        });
    }

    /**
     * @param string $controller
     *
     * @return $this
     * @throws ReflectionException
     */
    public function addController($controller = null)
    {
        if(is_string($controller) && class_exists($controller)) {
            $controller = $this->getContainer()->get($controller);
        }

        if($controller instanceof AbstractController) {
            $this->actions->put($controller->getName(), $controller);
        }

        return $this;
    }

    /**
     * Returns the WP REST namespace with version number
     * @return string
     */
    protected function getApiNamespace()
    {
        return "{$this->apiNamespace}/{$this->apiVersion}";
    }
}
