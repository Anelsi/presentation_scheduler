<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Aws\S3\S3Client;

$s3 = new S3Client([
    "version" => "latest",
    "region"  => "us-east-1",
    "credentials" => [
        "key"    => "ASIAUC2IQOLF77FT3BRH", 
        "secret" => "VhDxV0OeVxeUVnVP7XBJsjPm//SfjptOex/yCRFG",
        "token" => "IQoJb3JpZ2luX2VjEMz//////////wEaCXVzLXdlc3QtMiJHMEUCIQCHgkNYXpbUEDXYBNv2ycSDs2n63BsspNDIVfTgURbPxAIgOZEYSKI968II4mT2NgRVrC4dbgLwbL3OSufsafk4TuIqwAIIlf//////////ARABGgwyODA5MzU1NTk4ODMiDKJ2XB1a1mumqGdBiCqUAg87taYxT+EJ5FS4XD9Zh2CHG/RY7hkWZfFu8W2PkkNcyxzi+7lEZln7GsLsM/F0KRG8UrykmNL2iLg5bkO692nLyV4msiDkrqKQk82a2h4Tf46+TrvpMMZpyoG6cBgRUJH1tWrg8gM9bNQPJv5BdHHYkOWSXp7N6u46l1D7ZCwliPcvQH1EM6JhqhtvKKeWsFxer1WH6fqtc4zTzoEJJLRlt5P10SD6lC5uj4QJaVKZqmXsi0n0i023bMsGLWfk/lcHjaHzAkLBb9FX6LkeXWxJDyUa7sdeMWWcH2bcQptmXFqJxTY1J9Pvz/BEOsImaCQfwmrvXyRn5n88orOV2/EWBzY+8u2vG/fpiahoqf7p5W4jKTCPqtvQBjqdAZtYgS+7Iu9hbch2KaxEg2iaqO3vt7KgZLDWy4pMMyUfzY/sarFV+kR1myZIfdJGsFM/ZRsZ2TqaufTn23bhsDia57vhgKdtPAkbFEBhJByNLlYfJdsXmL1XLHxKhhrzaKhFc5LWioHMlvPIrGoXOqNmiCiTt0WinTitmp9akdCP7TyDCPXbdClgM/K2at+kGhOf9aEQk1ni9tEhTgU="

    ],
]);

$bucketName = "presentation-scheduler-anelia-2026-2";
