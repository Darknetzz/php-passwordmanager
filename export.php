<?php
    session_start();
    require_once("includes/config.php");
    require_once("includes/functions.php");

    $exportType = isset($_GET['type']) ? $_GET['type'] : 'csv';

    $export = "SELECT * FROM accounts ORDER BY id DESC";
    $export = mysqli_query($sqlcon, $export);

    if ($exportType === 'csv') {
        echo "\xEF\xBB\xBF"; // Add BOM to fix Norwegian characters in Excel
        $filename = "php-passwordmanager-" . date("Y-m-d") . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        $output = fopen('php://output', 'w');
        fputcsv($output, array('id', 'name', 'username', 'password', 'salt', 'iv', 'description', 'url', '2fa'));
        while ($row = mysqli_fetch_assoc($export)) {
            $row['password'] = decrypt($row['password'], $row['salt'] . ENCRYPTION_KEY, $row['iv']);
            fputcsv($output, $row);
        }
        fclose($output);
    } elseif ($exportType === 'json') {
        $filename = "php-passwordmanager-" . date("Y-m-d") . ".json";
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        $data = array();
        while ($row = mysqli_fetch_assoc($export)) {
            $row['password'] = decrypt($row['password'], $row['salt'] . ENCRYPTION_KEY, $row['iv']);
            $data[] = $row;
        }
        echo json_encode($data);
    } elseif ($exportType === 'sql') {
        $filename = "php-passwordmanager-" . date("Y-m-d") . ".sql";
        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        $output = fopen('php://output', 'w');
        while ($row = mysqli_fetch_assoc($export)) {
            $row['password'] = decrypt($row['password'], $row['salt'] . ENCRYPTION_KEY, $row['iv']);
            $sql = "INSERT INTO accounts (id, name, username, password, salt, iv, description, url, 2fa) VALUES ('" . $row['id'] . "', '" . $row['name'] . "', '" . $row['username'] . "', '" . $row['password'] . "', '" . $row['salt'] . "', '" . $row['iv'] . "', '" . $row['description'] . "', '" . $row['url'] . "', '" . $row['2fa'] . "');";
            fwrite($output, $sql . "\n");
        }
        fclose($output);
    }
    
?>