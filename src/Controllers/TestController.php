<?php

namespace AssessKen\Controllers;

use AssessKen\Models\Database;

class TestController
{
    public function database(): void
    {
        try {
            Database::connect();
            echo 'Connected';
        } catch (\Throwable $e) {
            error_log($e);
            echo 'Database connection failed';
        }
    }
}
