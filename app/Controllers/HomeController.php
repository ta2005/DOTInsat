<?php

class HomeController
{
    public function index()
    {
        $config = require '../config/etudiant.php';

        require '../views/pages/home.php';
    }
}