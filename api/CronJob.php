<?php

require_once 'middleware.php';

(new Order())->create();
(new ProcessOrder())->create();
(new ProcessOrder())->update();
