<?php
    session_start();
    require_once("includes/config.php");
    require_once("includes/functions.php");

    echo "\xEF\xBB\xBF"; // Add BOM to fix Norwegian characters in Excel
    
    $export = "SELECT * FROM accounts ORDER BY id DESC";
    $export = mysqli_query($sqlcon, $export);
    $filename = "php-passwordmanager-".date("Y-m-d").".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename='.$filename);
    $output = fopen('php://output', 'w');
    fputcsv($output, array('id', 'name', 'username', 'password', 'salt', 'iv', 'description', 'url', '2fa'));
    while ($row = mysqli_fetch_assoc($export)) {
        $row['password'] = decrypt($row['password'], $row['salt'].ENCRYPTION_KEY, $row['iv']);
        fputcsv($output, $row);
    }
    fclose($output);
?>