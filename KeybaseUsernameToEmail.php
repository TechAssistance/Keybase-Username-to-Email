<?php
header('Content-Type: application/json');

if (!isset($_GET['username'])) {
    http_response_code(400);
    echo json_encode(array("error" => "Username parameter is required"));
    exit();
}

$username = $_GET['username'];

$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, "https://keybase.io/" . $username . "/key.asc");
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($handle);
$status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
curl_close($handle);

if ($status === 404) {
    http_response_code(404);
    echo json_encode(array("error" => "Unable To Retrieve Data"));
    exit();
}

if (preg_match("/SELF-SIGNED PUBLIC KEY NOT FOUND/i", $output)) {
    http_response_code(404);
    echo json_encode(array("error" => "Unable To Retrieve Data"));
    exit();
}

$body = explode("\n\n", $output);
$key = explode("-----", $body[1]);

preg_match_all("/ <(.*?)>/", base64_decode($key[0]), $matches);

$emails = array();
foreach ($matches[1] as $email) {
    $emails[] = $email;
}

$result = array(
    "username" => $username,
    "emails" => $emails
);

echo json_encode($result, JSON_PRETTY_PRINT);
?>
