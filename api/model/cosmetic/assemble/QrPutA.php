<?php

class QrPutA extends QrPutP
{
    protected function getDeptId ()
    {
        return Dept::$ASSEMBLE;
    }
}
