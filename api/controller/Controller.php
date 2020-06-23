<?php


class Controller
{
    protected $model;
    protected $types = [];
    protected $params = [];

    /**
     * @param null $id
     * @param array $params
     * @param null $method
     */
    public function __construct()
    {
        $this->getModel();
        $this->model->token = $this->tokenValidation();

        $req = new Request();
        $this->params = $req->getParams();
        $method = $req->getMethod();
        $id = $req->getParamsValue($this->model->primaryKey);

        switch ($method) {
            case 'GET' :
                    if (!$req->hasId($this->model->primaryKey)) {
                        $this->index();
                    } else {
                        $this->show($id);
                    }
                break;
            case 'POST' : $this->create();
                break;
            case 'PUT' : $this->update($id);
                break;
            case 'DELETE' : $this->destroy($id);
                break;
        }
    }

    public function index ()
    {
        $this->model->index($this->params);
    }

    /**
     * @param null $id
     */
    public function show ($id = null)
    {
        $this->model->show($id);
    }

    /**
     *  @param array $data
     */
    public function create ()
    {
        $this->model->create($this->params);
    }

    /**
     *  @param null $id
     *  @param array $data
     */
    public function update ($id = null)
    {
        $this->model->update($id, $this->params);
    }

    /**
     *  @param null $id
     */
    public function destroy ($id = null)
    {
        $this->model->destroy($id);
    }

    protected function tokenValidation ()
    {
        if (!array_key_exists('HTTP_AUTHORIZATION', $_SERVER)) {
            return (new ErrorHandler())->unAuthorized();
        }

        $token = $_SERVER['HTTP_AUTHORIZATION'];

        if (!$token) {
            return (new ErrorHandler())->unAuthorized();
        }

        try {
            $payload = JWT::decode($token, JWT::$tokenKey, array('HS256'));
            $returnArray = array('id' => $payload->userId);
            if (isset($payload->exp)) {
                $returnArray['exp'] = date(DateTime::ISO8601, $payload->exp);;
            }

            return $returnArray;
        }
        catch(Exception $e) {
            return new Response(400, [], $e->getMessage());
        }
    }

    protected function validate ()
    {

    }

    protected function getModel ()
    {

    }
}
