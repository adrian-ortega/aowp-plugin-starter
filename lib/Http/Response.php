<?php

namespace AOD\Plugin\Http;

use ErrorException;
use Exception;
use Illuminate\Support\Collection;

class Response
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var array
     */
    protected $cookies;

    /**
     * @var mixed
     */
    protected $status;

    /**
     * @var string
     */
    protected $reasonPhrase;

    /**
     * Response constructor.
     * @param array $data
     * @param int $status
     * @param array $headers
     * @param array $cookies
     * @throws Exception
     */
    public function __construct( $data = [], $status = StatusCode::HTTP_OK, $headers = [], $cookies = [] )
    {
        $this->setData( $data );
        $this->setStatus( $status );
        $this->setHeaders( $headers );
        $this->setCookies( $cookies );
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param $key
     * @param $value
     * @param bool $replace
     * @return Response
     */
    public function withHeader( $key, $value, $replace = true )
    {
        $clone = clone $this;
        $clone->setHeader( $key, $value, $replace );

        return $clone;
    }

    /**
     * @param array $headers
     */
    public function setHeaders( array $headers )
    {
        $this->headers = $headers;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param bool $replace
     * @return Response
     */
    public function setHeader( $key, $value, $replace = true )
    {
        if ( $replace || ! isset( $this->headers[ $key ] ) ) {
            $this->headers[ $key ] = [ $value ];
        } else {
            $this->headers[ $key ][] = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return Response
     * @throws Exception
     */
    public function withStatus( $code, $reasonPhrase = '' )
    {
        $code = $this->filterStatus( $code );

        if ( !is_string( $reasonPhrase ) && ! method_exists( $reasonPhrase, '__toString' ) ) {
            throw new Exception('ReasonPhrase must be a string');
        }

        $clone = clone $this;
        $clone->setStatus( $code );

        return $clone;
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return $this
     * @throws Exception
     */
    protected function setStatus( $code, $reasonPhrase = '')
    {
        $this->status = $code;

        if ( $reasonPhrase === '' && StatusCode::exists( $code ) ) {
            $reasonPhrase = StatusCode::getMessage( $code );
        }

        if ( $reasonPhrase === '' ) {
            throw new Exception( 'ReasonPhrase must be supplied with this code' );
        }

        $this->reasonPhrase = $reasonPhrase;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return Response
     */
    public function withData( $data )
    {
        $clone = clone $this;
        $clone->setData( $data );

        return $clone;
    }

    /**
     * @param array|mixed $data
     * @return $this
     */
    protected function setData( $data = [] )
    {
        if ( $data instanceof ErrorException ) {
            $this->data = [
                'type'    => get_class( $data ),
                'message' => $data->getMessage(),
                'file'    => $data->getFile(),
                'line'    => $data->getLine(),
                'trace'   => $data->getTrace()
            ];
        } else if ( $data instanceof Exception ) {
            $this->data = [
                'data' => [],
                'errors' => [[
                    'name' => 'exception',
                    'message' => $data->getMessage()
                ]]
            ];
        } else {
            $this->data = $data;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * @param array $cookies
     */
    public function setCookies( array $cookies )
    {
        $this->cookies = $cookies;
    }

    /**
     * @param string $key
     * @param string $value
     * @param bool $replace
     * @return Response
     */
    public function withCookie( $key, $value, $replace = true )
    {
        $clone = clone $this;
        $clone->setCookie($key, $value, $replace);

        return $clone;
    }

    /**
     * @param string $key
     * @param string $value
     * @param bool $replace
     * @return $this
     */
    public function setCookie( $key, $value, $replace = true)
    {
        if($replace || !isset($this->cookies[$key])) {
            $this->cookies[$key] = [$value];

        } else {
            $this->cookies[$key][] = $value;
        }

        return $this;
    }

    /**
     * @return false|string
     */
    public function toJson()
    {
        if ( $this->data instanceof Collection ) {
            return collect( [ 'data' => $this->data ] )->toJson();
        }

        return json_encode( $this->data );
    }

    public function send()
    {
        if ( empty( $this->headers ) ) {
            $charset = get_option( 'blog_charset' );
            $this->setHeader( 'Content-Type', "application/json; charset={$charset}");
        }

        $this->setHeader(
            $this->getVersionProtocol(),
            sprintf('%s %s', $this->getStatusCode(), $this->getReasonPhrase())
        );

        foreach( $this->headers as $header => $headerValue ) {
            $headerValue = implode( ', ', $headerValue );
            header( "{$header}: $headerValue" );
        }

        // @TODO Adrian Ortega - this is overly simplistic on purpose, create a cookie jar if necessary
        if( ! empty( $this->cookies ) ) {
            foreach( $this->cookies as $name => $value ) {
                if( is_array( $value ) ) {
                    $value = json_encode( $value );
                }

                setcookie( $name, $value, time() + 60*60*24*30, '/' );
            }
        }

        echo is_array( $this->getData() ) ? $this->toJson() : $this->data;
        die(1);
    }

    /**
     * @param int $status
     * @return int
     * @throws Exception
     */
    protected function filterStatus( $status )
    {
        if ( StatusCode::isInvalid($status) ) {
            throw new Exception( 'Invalid HTTP status code' );
        }

        return $status;
    }

    /**
     * @return mixed|string
     */
    protected function getVersionProtocol()
    {
        return isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
    }
}
