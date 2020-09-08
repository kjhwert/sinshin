<?php

class QrDefectP extends QrDefect
{
    protected function getDeptId ()
    {
        return Dept::$PAINTING;
    }
}
