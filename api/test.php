<?php

require_once 'middleware.php';


$sql = "select * from time_dimension order by id desc limit 10000";
$db = Database::getInstance()->getDatabase();

try {
    $query = $db->prepare($sql);
    $query->execute();
    return new Response(200, $query->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    return new Response(403, [], $e.message);
}

//return new Response(200, $this->fetch($sql));

