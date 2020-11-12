<?php

class MaterialLot extends Model
{
    protected $table = 'material_lot';
    protected $searchableText = 'ml.lot_no';

    public function index(array $params = [])
    {

    }

    /**
     * @param null $id material_id
     * @param array $params
     * @return Response
     */
    public function show($id = null, array $params = [])
    {
        $sql = "select ml.id, ml.material_id, ml.lot_no, u.name as manager, ml.created_at
                    from {$this->table} ml
                    inner join user u
                    on ml.created_id = u.id
                    where ml.material_id = {$id} {$this->searchText($params)}
                ";

        return new Response(200, $this->fetch($sql), '');
    }
}
