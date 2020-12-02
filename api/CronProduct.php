<?php

require_once 'middleware.php';

$db = Database::getInstance()->getDatabase();

$sql = "insert into cronjob_log set
            type = 'PRODUCT', 
            created_at = SYSDATE()";

$query = $db->prepare($sql);
$query->execute();

(new ProductMaster())->create();
(new ProductMaster())->destroy();
