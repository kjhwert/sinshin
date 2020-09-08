<?php

class QrStockP extends QrStock
{
    protected function getDeptId ()
    {
        return Dept::$PAINTING;
    }
}
