<?php
$err = ["msg"=>"Not found"];
http_response_code(404);
echo json_encode($err);
?>