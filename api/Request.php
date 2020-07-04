<?php

class Request
{
    protected $query;
    protected $method;
    protected $page;
    protected $id;

    public function __construct()
    {
        $this->query = $_SERVER['QUERY_STRING'];
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    public function getParams ()
    {
        $array = [];

        if ($this->method === 'GET') {
            parse_str($this->query, $array);
        }

        if ($this->method === 'POST' || $this->method === 'PUT' || $this->method === 'DELETE') {
            $array = (array) json_decode(file_get_contents('php://input'));
        }

        return $array;
    }

    public function getMethod ()
    {
        return $this->method;
    }

    public function hasId ($id = null)
    {
        return array_key_exists($id, $this->getParams());
    }

    public function getParamsValue ($value)
    {
        if ($this->hasId($value)) {
            return $this->getParams()[$value];
        }
    }

}
