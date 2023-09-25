<?php
function encrypt($s, $p) {
    $method = 'aes256';
    $encrypted = openssl_encrypt($s, $method, $p);
    return $encrypted;
}

function decrypt($s, $p) {
    $method = 'aes256';
    $decrypted = openssl_decrypt($s, $method, $p);
    return $decrypted;
}

# passGen(15, "lud")
function passGen($l, $t) {
    try {
        $lc = strpos($t, "l");
        $uc = strpos($t, "u");
        $di = strpos($t, "d");
        $sy = strpos($t, "s");
    
        $alphabet = "";
        if ($lc !== false) {
            $alphabet .= "abcdefghijklmnopqrstuvwxyz";
        }
        if ($uc !== false) {
            $alphabet .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        }
        if ($di !== false) {
            $alphabet .= "0123456789";
        }
        if ($sy !== false) {
            $alphabet .= "!#%-_.,=?";
        }
        $alen = strlen($alphabet);
        $str = "";
    
        for ($i = 0; $i < $l; $i++) {
            $r = mt_rand(0, $alen);
            str_split($alphabet);
            $str .= $alphabet[$r];
        }
        return $str;
    } catch (Throwable $th) {
        return "Exception in passGen(): $th";
    }
}

function setup_error(string $text, int $setstatuscode = 0) {
    global $status;
    global $error;

    if ($setstatuscode != 0) {
        $status = $setstatuscode;
    }

    array_push($error, $text);
    return $error;
}

function setup_info(string $text, $type = "info") {
    global $info;
    array_push($info, alert($text, $type));
    return $info;
}

function alert($txt, $type = 'info', $icon = '') {
    if ($type == 'info') {
        $icon = 'ℹ️';
    }
    if ($type == 'danger') {
        $icon = '❌';
    }
    if ($type == 'warning') {
        $icon = '⚠️';
    }
    if ($type == 'success') {
        $icon = '✅';
    }

    $txt = $icon.' '.$txt;

    return '
    <div class="alert alert-'.$type.'">'.$txt.'</div>
    ';
}