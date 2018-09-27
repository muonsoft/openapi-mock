<?php

// The check is to ensure we don't use .env in production
if (!isset($_SERVER['APP_ENV'])) {
    throw new \RuntimeException('APP_ENV environment variable is not defined.');
}
