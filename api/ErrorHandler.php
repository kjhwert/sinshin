<?php


class ErrorHandler
{
    /**
     *  403 Error
     */
    public function forbidden ()
    {

    }

    /**
     *  400 Error
     * @throws Exception
     */
    public function badRequest () : Response
    {
        return new Response(400, [], "올바르지 않은 요청입니다.");
    }

    /**
     *  401 Error
     */
    public function unAuthorized ()
    {

    }

    public function typeNull ($param)
    {
        return new Response(406, [], "{$param} 데이터가 존재하지 않습니다.");
    }

    public function typeError ($param) : Response
    {
        return new Response(406, [], "{$param}의 데이터의 타입이 일치하지 않습니다.");
    }
}