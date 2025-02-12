<?php

namespace App\Clients;

use Illuminate\Support\Facades\Http;

abstract class Client
{

    protected $url;

    protected $token;

    protected $resource;


    private $abort = false;

    public function getUrl()
    {
        return $this->base_url;
    }

    public function setUrl($base_url)
    {
        $this->base_url = $base_url;

        return $this;
    }

    public function get($url)
    {
        $base = $this->getUrl();

        $this->response = Http::withHeaders(
            [
            'Authorization' => request()->header('authorization'),
            'Accept' => 'application/json'
            ]
        )->get($base . $url);

        return $this;
    }

    public function post($url, $resource)
    {
        $base = $this->getUrl();
        $this->response = Http::withHeaders(
            [
            'Authorization' => request()->header('authorization'),
            'Accept' => 'application/json'
            ]
        )->post($base . $url, $resource);

        return $this;
    }

    public function delete($url)
    {
        $base = $this->getUrl();
        $this->response = Http::withHeaders(
            [
            'Authorization' => request()->header('authorization'),
            'Accept' => 'application/json'
            ]
        )->delete($base . $url);

        return $this;
    }



    public function logErrors()
    {
        $this->response->throw(
            function ($response, $e) {
                $this->abort = true;
                if ($response->clientError()) {
                    logger()->error($response->getStatusCode() . ' : Please make sure the route that you are calling exists in Service');
                    $this->error_code = 400;
                }

                logger()->error($response->getStatusCode() . ' : Error getting a connection for the service, Please check that it is up and running');
                $this->error_code = 500;
            }
        );

        return $this;
    }


    public function abort()
    {
        $this->logErrors();
        if ($this->abort == true) {
            abort($this->error_code);
        }

        return $this;
    }

    public function json()
    {
        return $this->response->json()['data'];
    }
}
