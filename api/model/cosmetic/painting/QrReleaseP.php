<?php

class QrReleaseP extends QrRelease
{
    protected function getDeptId ()
    {
        return Dept::$PAINTING;
    }
}
