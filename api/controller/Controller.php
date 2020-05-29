<?php


class Controller
{
    protected $model;
    protected $types = [];

    /**
     * @param null $id
     * @param array $params
     * @param null $method
     */
    public function __construct($id = null, array $params = [], $method = null)
    {
        $this->getModel();
        $this->tokenValidation();
        //TODO user 정보를 static하게 담아놓을 지는 프로젝트 진행하면서 판단하자.

        switch ($method) {
            case 'GET' :
                    if (!$id) {
                        $this->index($params);
                    } else {
                        $this->show($id);
                    }
                break;
            case 'POST' : $this->create($params);
                break;
            case 'PUT' : $this->update($id, $params);
                break;
            case 'DELETE' : $this->destroy($id);
                break;
        }
    }

    public function index (array $params = [])
    {
        $this->model->index($params);
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
    public function create (array $data = [])
    {
        $this->model->create($data);
    }

    /**
     *  @param null $id
     *  @param array $data
     */
    public function update ($id = null, array $data = [])
    {
        $this->model->update($id, $data);
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
            return (new ErrorHandler())->typeNull('HTTP_AUTHORIZATION');
        }

        $token = $_SERVER['HTTP_AUTHORIZATION'];

        if (!$token) {
            return new Response("login", [], '올바르지 않은 토큰입니다. 다시 로그인해주세요.');
        }

        try {
            $payload = JWT::decode($token, JWT::$tokenKey, array('HS256'));
            $returnArray = array('id' => $payload->id);
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