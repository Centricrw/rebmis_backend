<?php
$err = ["msg" => "Route not found, please try again?"];
http_response_code(404);
echo json_encode($err);
