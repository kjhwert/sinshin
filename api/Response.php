<?php


class Response
{
    protected $status;
    protected $message;

    public function __construct($status, $data, $message)
    {
        $this->status = $status;
        $this->message = $message;

        echo json_encode([
            'status' => $this->status,
            'data' => $this->hasData($data),
            'message' => $this->message
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