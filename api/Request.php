<?php

class Request
{
    protected $requestUri;
    protected $query;
    protected $method;
    protected $page;
    protected $id;

    public function __construct()
    {
        $this->requestUri = explode('/',trim($_SERVER['REQUEST_URI'],'/'));
        $this->query = $_SERVER['QUERY_STRING'];
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    public function routePath ()
    {
        if ($this->requestUri[0]) {
            $path = explode("?", $this->requestUri[0]);
            return $this->page = $path[0];
        }

        return $this->page = null;
    }

    public function getParams ()
    {
        if ($this->method === 'GET') {
            $array = [];
            parse_str($this->query, $array);

            return $array;
        }

        if ($this->method === 'POST' || $this->method === 'PUT') {
            return (array) json_decode(file_get_contents('php://input'));
        }

        return [];
    }

    public function method ()
    {
        return $this->method;
    }

    public function showId ()
    {
        if ($this->hasId()) {
            return $this->id = $this->requestUri[1];
        }

        return $this->id = null;
    }

    protected function hasId ()
    {
        return count($this->requestUri) >= 2 && $this->requestUri[1];
    }
}