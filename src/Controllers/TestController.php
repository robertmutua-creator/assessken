<?php

namespace AssessKen\Controllers;

use AssessKen\Models\Database;
use AssessKen\Models\Schools;
use Throwable;

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

    public function tables(): void
    {
        try {
            Database::migrations();
            echo 'Tables created!';
        } catch (Throwable $e) {
            error_log($e);
            echo 'Failed to create tables';
        }
    }

    public function seedSchool(): void
    {
        // $school1 = (new Schools()->create([
        //     'name' => 'Test School 1',
        //     'address' => 'School  1 Address',
        //     'phone' => '07123456790',
        //     'email' => 'school1@mail.com',
        //     'logo_path' => '/logopath/',
        //     'principal_signature_path' => 'signature_path',
        //     'code' => 'sch1'
        // ]));

        // $school2 = (new Schools()->create([
        //     'name' => 'Test School 2',
        //     'address' => 'School  2 Address',
        //     'phone' => '0734543234',
        //     'email' => 'school2@mail.com',
        //     'logo_path' => '/logopath/',
        //     'principal_signature_path' => 'signature_path',
        //     'code' => 'sch2'
        // ]));
    }
}
