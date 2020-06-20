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
        if ($this->method === 'GET') {
            $array = [];
            parse_str($this->query, $array);

            return $array;
        }

        if ($this->method === 'POST' || $this->method === 'PUT' || $this->method === 'DELETE') {
            return (array) json_decode(file_get_contents('php://input'));
        }

        return [];
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
