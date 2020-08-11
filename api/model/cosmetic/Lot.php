<?php

class Lot extends Model
{
    protected $table = 'lot';
    protected $createRequired = [
        'order_id' => 'integer',
        'process_order_id' => 'integer',

    ];

    /**
     * @param array $data
     * 1. qr코드를 생성합니다.
     * 2. lot을 생성합니다.
     */
    public function create(array $data = [])
    {
        /**
         * product, material, color 셋 중에 하나여야만 한다.
         */
        print_r($this->token['dept_id']);
    }

    protected function hasUniqueValue (array $data = [])
    {
        $i = 0;
        foreach ($data as $key=>$value) {
            if ($i > 1) {
                return new Response(403, [], '제품 유형이 중복됩니다.');
            }

            if (!empty($value)) {
                $resultKey = $key;
                $resultValue = $value;
                $i++;
            }
        }


    }

}
