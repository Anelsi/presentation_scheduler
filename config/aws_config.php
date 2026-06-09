<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Aws\S3\S3Client;

$config = require __DIR__ . "/app.php";
$aws = $config["aws"];

$bucketName = $aws["bucket"];
$s3 = null;

if ($bucketName !== "") {
    $options = [
        "version" => "latest",
        "region" => $aws["region"],
    ];

    if ($aws["access_key_id"] !== "" && $aws["secret_access_key"] !== "") {
        $options["credentials"] = [
            "key" => $aws["access_key_id"],
            "secret" => $aws["secret_access_key"],
        ];

        if ($aws["session_token"] !== "") {
            $options["credentials"]["token"] = $aws["session_token"];
        }
    }

    $s3 = new S3Client($options);
}
