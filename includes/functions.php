<?php

session_start();

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

function formatPrice($price)
{
    return '৳' . number_format($price, 2);
}

