<?php

class QrCompleteP extends QrComplete
{
    protected function getDeptId ()
    {
        return Dept::$PAINTING;
    }
}
