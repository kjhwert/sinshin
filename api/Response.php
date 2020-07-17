<?php


class Response
{
    public function __construct($status, $data, $message = '', $paging = [])
    {
        echo json_encode([
            'status' => $status,
            'data' => $this->hasData($data),
            'message' => $message,
            'paging' => $paging
        ]);
        exit;
    }

    protected function hasData (array $data = [])
    {
        if (!$data) {
            return [];
        }

        return $data;
    }
}
