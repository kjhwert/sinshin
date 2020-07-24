<?php

class Domain extends Model
{
    protected $table = 'domain';

    public function __construct()
    {
        $this->db = Database::getInstance()->getDatabase();
    }

    public function index(array $params = [])
    {
        $sql = "select url from {$this->table} limit 1";
        return new Response(200, $this->fetch($sql)[0]);
    }
}
