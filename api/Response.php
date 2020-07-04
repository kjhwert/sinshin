<?php


class Response
{
    public function __construct($status, $data, $message, $total = null)
    {
        echo json_encode([
            'status' => $status,
            'data' => $this->hasData($data),
            'message' => $message,
            'total' => $total
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
