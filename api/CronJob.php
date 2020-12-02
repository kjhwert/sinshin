<?php

require_once 'middleware.php';

$db = Database::getInstance()->getDatabase();

$sql = "insert into cronjob_log set
            type = 'ORDER', 
            created_at = SYSDATE()";

$query = $db->prepare($sql);
$query->execute();

(new Order())->create();
(new Order())->update();
(new ProcessOrder())->create();
(new ProcessOrder())->update();
