<head>
<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<!-- JavaScript Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<!-- CSS only -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<link rel="icon" href="img/lock.png" type="image/x-icon">

<!-- BS icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<script src="includes/php-passwordmanager.js"></script>

<meta name="viewport" content="width=device-width, initial-scale=1">


<?php
$bgcolor = (defined('BACKGROUND_COLOR') ? BACKGROUND_COLOR : "#222222");
$color   = (defined('COLOR')            ? COLOR            : "#FFA500");
?>

<style>
    body, .modal-content, td, .card, .form-control, h1,h2,h3,h4,h5,h6 {
        background-color:<?= $bgcolor ?>;
        color:<?= $color ?>;
    }
</style>
</head>