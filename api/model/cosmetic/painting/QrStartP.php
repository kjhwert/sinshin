<?php

class QrStartP extends QrStart
{
    protected function getDeptId ()
    {
        return Dept::$PAINTING;
    }
}
