<?php

class MachineVision extends Model
{
    protected $table = 'machine_vision';

    protected $createRequired = [
      'ori' => 'string',
      'det' => 'string'
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getDatabase();
    }

    public function index(array $params = [])
    {

    }

    public function show($id = null)
    {

    }

    public function create(array $data = [])
    {
        $this->validate($data, $this->createRequired);

        try {
            $this->db->beginTransaction();

            $sql = "insert into {$this->table} set
                    origin_img = '{$data['ori']}',
                    detect_img = '{$data['det']}',
                    created_id = 1,
                    created_at = SYSDATE()
                ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $mv_id = $this->db->lastInsertId();

            $errors = $data['errors'];
            if (!$errors || count($errors) === 0) {
                return (new ErrorHandler())->typeError('errors');
            }

            foreach ($errors as $error) {
                $tmp = (array)$error;

                $sql = "insert into machine_vision_defect_log set
                        code = '{$tmp['type']}',
                        mv_id = {$mv_id},
                        created_id = 1,
                        created_at = SYSDATE()
                    ";

                $stmt = $this->db->prepare($sql);
                $stmt->execute();
            }

            $this->db->commit();

        } catch (Exception $e) {
            $this->db->rollBack();
            return new Response(403, [],'데이터 입력 중 오류가 발생하였습니다.');
        }

        return new Response(200, [], '등록 되었습니다.');
    }

    public function update($id = null, array $data = [])
    {

    }
}
