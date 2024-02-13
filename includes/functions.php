<?php

/* ───────────────────────────────────────────────────────────────────── */
/*                                  icon                                 */
/* ───────────────────────────────────────────────────────────────────── */
function icon(string $icon, float $rem = 1.5, string $color = 'cornflowerblue') {
    return '<i class="bi bi-'.$icon.'" style="font-size: '.$rem.'rem; color: '.$color.';"></i>';
}

/* ───────────────────────────────────────────────────────────────────── */
/*                               cipherLen                               */
/* ───────────────────────────────────────────────────────────────────── */
function cipherLen(string $method = ENC_METHOD) {
    return openssl_cipher_iv_length($method);
}

/* ───────────────────────────────────────────────────────────────────── */
/*                                 genIV                                 */
/* ───────────────────────────────────────────────────────────────────── */
function genIV($method = ENC_METHOD) {
    $len   = cipherLen($method);
    $bytes = openssl_random_pseudo_bytes($len);
    return bin2hex($bytes);
}

/* ───────────────────────────────────────────────────────────────────── */
/*                                encrypt                                */
/* ───────────────────────────────────────────────────────────────────── */
function encrypt($s, $p, $iv = "") {
    if (USE_IV !== True || empty($iv)) {
        $iv = hex2bin(str_repeat("00", cipherLen()));
    }
    elseif (!empty($iv) && ctype_xdigit($iv)) {
        $iv = hex2bin($iv);
    }

    $ivlen = strlen($iv);

    if ($ivlen !== 0 && $ivlen !== cipherLen()) {
        die("Invalid IV length (".strlen($iv)."). Expected ".cipherLen());
    }

    $encrypted = openssl_encrypt($s, ENC_METHOD, $p, iv: $iv);

    if (empty($encrypted) && !empty($s)) {
        die(alert("
            Failed to encrypt non-empty password (empty response).<br>
            <b>String:</b> $s<br>
            <b>Key: (hidden)</p><br>
            <b>IV:</b> $iv<br>
        ", "danger"));
    }

    return $encrypted;
}

/* ───────────────────────────────────────────────────────────────────── */
/*                                decrypt                                */
/* ───────────────────────────────────────────────────────────────────── */
function decrypt($s, $p, $iv = "") {
    if (USE_IV !== True || empty($iv)) {
        $iv = hex2bin(str_repeat("00", cipherLen()));
    }
    elseif (!empty($iv) && ctype_xdigit($iv)) {
        $iv = hex2bin($iv);
    }

    $ivlen = strlen($iv);

    if ($ivlen !== 0 && $ivlen !== cipherLen()) {
        die("Invalid IV length (".strlen($iv)."). Expected ".cipherLen());
    }

    $decrypted = openssl_decrypt($s, ENC_METHOD, $p, iv: $iv);
    $iv = (!empty($iv) ? bin2hex($iv) : "(none)");

    if (empty($decrypted) && !empty($s)) {
        die(alert("
            Failed to decrypt non-empty password (empty response).<br>
            <b>String:</b> $s<br>
            <b>Key:</b> (hidden)<br>
            <b>IV:</b> $iv<br>
        ", "danger"));
    }

    return $decrypted;
}

/* ───────────────────────────────────────────────────────────────────── */
/*                                passGen                                */
/* ───────────────────────────────────────────────────────────────────── */
function passGen($l = 15, $t = 'lud') {
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
            $r = mt_rand(0, $alen-1);
            str_split($alphabet);
            $str .= $alphabet[$r];
        }
        return $str;
    } catch (Throwable $th) {
        return "Exception in passGen(): $th";
    }
}

/* ───────────────────────────────────────────────────────────────────── */
/*                              setup_error                              */
/* ───────────────────────────────────────────────────────────────────── */
function setup_error(string $text, int $setstatuscode = 0) {
    global $status;
    global $error;

    if ($setstatuscode != 0) {
        $status = $setstatuscode;
    }

    array_push($error, $text);
    return $error;
}

/* ───────────────────────────────────────────────────────────────────── */
/*                               setup_info                              */
/* ───────────────────────────────────────────────────────────────────── */
function setup_info(string $text, $type = "info") {
    global $info;
    array_push($info, alert($text, $type));
    return $info;
}

/* ───────────────────────────────────────────────────────────────────── */
/*                                 alert                                 */
/* ───────────────────────────────────────────────────────────────────── */
function alert($txt, $type = 'info', $icon = '') {
    if ($type == 'success') {
        $icon = icon("check-circle", color: "green");
    }
    if ($type == 'info') {
        $icon = icon("info-circle-fill", color: "blue");
    }
    if ($type == 'warning') {
        $icon = icon("exclamation-circle-fill", color: "orange");
    }
    if ($type == 'danger') {
        $icon = icon("exclamation-triangle-fill", color: "red");
    }

    $txt = $icon.' '.$txt;

    return '
    <div class="container">
        <div class="alert alert-'.$type.'">'.$txt.'</div>
    </div>
    ';
}

/* ───────────────────────────────────────────────────────────────────── */
/*                                isSecure                               */
/* ───────────────────────────────────────────────────────────────────── */
function isSecure() {
    if (defined("IGNORE_SSL_WARNING")) {
        return True;
    }
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return True;
    }
    if (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
        return True;
    }
    // if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    //     return True;
    // }
    // if (!empty($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443) {
    //     return True;
    // }
    // if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    //     return True;
    // }
    // if (!empty($_SERVER['HTTP_X_FORWARDED_SCHEME']) && $_SERVER['HTTP_X_FORWARDED_SCHEME'] == 'https') {
    //     return True;
    // }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return True;
    }
    return False;
}

/* ───────────────────────────────────────────────────────────────────── */
/*                                 getTFA_accounts                       */
/* ───────────────────────────────────────────────────────────────────── */
function getTFA_accounts($access_token = TFA_APIKEY, $id = Null) {
    if (defined("ENABLE_TFA") && ENABLE_TFA === True) {
        $url = (empty($id) ? TFA_URL."/api/v1/accounts" : TFA_URL."/api/v1/accounts/$id");
        $headers = array(
            "Authorization: Bearer
            $access_token"
        );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, True);
    }
    return False;
}

/* ───────────────────────────────────────────────────────────────────── */
/*                               getTFA_otp                              */
/* ───────────────────────────────────────────────────────────────────── */
function getTFA_otp($access_token = TFA_APIKEY, $id = Null) {
    if (empty($id)) {
        return False;
    }
    if (defined("ENABLE_TFA") && ENABLE_TFA === True) {
        $url = TFA_URL."/api/v1/otp/$id";
        $headers = array(
            "Authorization: Bearer
            $access_token"
        );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, True);
    }
    return False;
}