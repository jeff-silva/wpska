<?php

include __DIR__ . '/../../wpska.php';

$result = Wpska_Rest::address($_REQUEST['search']);
echo json_encode($result);