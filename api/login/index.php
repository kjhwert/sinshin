<?php

require_once '../middleware.php';
require_once  'LoginController.php';

echo (new LoginController())->login();


