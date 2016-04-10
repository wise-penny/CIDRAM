<?php
/**
 * This file is a part of the CIDRAM package, and can be downloaded for free
 * from {@link https://github.com/Maikuolan/CIDRAM/ GitHub}.
 *
 * CIDRAM COPYRIGHT 2016 and beyond by Caleb Mazalevskis (Maikuolan).
 *
 * License: GNU/GPLv2
 * @see LICENSE.txt
 *
 * This file: CLI handler (last modified: 2016.04.11).
 */

/** Fallback for missing $_SERVER superglobal. */
if (!isset($_SERVER)) {
    $_SERVER = array();
}

$CIDRAM['argv'] = array(
    isset($argv[0]) ? $argv[0] : '',
    isset($argv[1]) ? $argv[1] : '',
    isset($argv[2]) ? $argv[2] : '',
);

if ($CIDRAM['argv'][1] === '-h') {
    /** CIDRAM CLI-mode help. */
    echo $CIDRAM['lang']['CLI_H'];
} elseif ($CIDRAM['argv'][1] === '-c') {
    /** Check if an IP address is blocked by the CIDRAM signature files. */
    echo "\n";
    /** Prepare variables to simulate the normal IP checking process. */
    $CIDRAM['BlockInfo'] = array(
        'IPAddr' => $CIDRAM['argv'][2],
        'Query' => $CIDRAM['Query'],
        'Referrer' => '',
        'UA' => '',
        'UALC' => '',
        'ReasonMessage' => '',
        'SignatureCount' => 0,
        'Signatures' => '',
        'WhyReason' => '',
        'xmlLang' => $CIDRAM['Config']['general']['lang'],
        'rURI' => 'CLI'
    );
    $CIDRAM['TestIPv4'] = $CIDRAM['IPv4Test']($CIDRAM['argv'][2]);
    $CIDRAM['TestIPv6'] = $CIDRAM['IPv6Test']($CIDRAM['argv'][2]);
    if (!$CIDRAM['TestIPv4'] && !$CIDRAM['TestIPv6']) {
        echo wordwrap($CIDRAM['ParseVars'](array('IP' => $CIDRAM['argv'][2]), $CIDRAM['lang']['CLI_Bad_IP']), 78, "\n ");
    } else {
        echo
            ($CIDRAM['BlockInfo']['SignatureCount']) ?
            wordwrap($CIDRAM['ParseVars'](array('IP' => $CIDRAM['argv'][2]), $CIDRAM['lang']['CLI_IP_Blocked']), 78, "\n ") :
            wordwrap($CIDRAM['ParseVars'](array('IP' => $CIDRAM['argv'][2]), $CIDRAM['lang']['CLI_IP_Not_Blocked']), 78, "\n ");
    }
    echo "\n";
} elseif ($CIDRAM['argv'][1] === '-g') {
    /** Generate CIDRs from an IP address. */
    echo "\n";
    $CIDRAM['TestIPv4'] = $CIDRAM['IPv4Test']($CIDRAM['argv'][2], true);
    $CIDRAM['TestIPv6'] = $CIDRAM['IPv6Test']($CIDRAM['argv'][2], true);
    if (!empty($CIDRAM['TestIPv4'])) {
        echo ' ' . implode("\n ", $CIDRAM['TestIPv4']);
    } elseif (!empty($CIDRAM['TestIPv6'])) {
        echo ' ' . implode("\n ", $CIDRAM['TestIPv6']);
    } else {
        echo wordwrap($CIDRAM['ParseVars'](array('IP' => $CIDRAM['argv'][2]), $CIDRAM['lang']['CLI_Bad_IP']), 78, "\n ");
    }
    echo "\n";
} elseif ($CIDRAM['argv'][1] === '-v') {
    /** Validates signature files (WARNING: BETA AND UNSTABLE!). */
    echo "\n";
    $FileToValidate = $CIDRAM['ReadFile']($CIDRAM['Vault'] . $CIDRAM['argv'][2]);
    echo $CIDRAM['ValidatorMsg']('Notice', 'Signature validator has started (' . date('r'). ').');
    if (empty($FileToValidate)) {
        echo $CIDRAM['ValidatorMsg']('Fatal Error', 'Specified signature file is empty or doesn\'t exist.') . "\n";
        die;
    }
    if (strpos($FileToValidate, "\r")) {
        echo $CIDRAM['ValidatorMsg']('Warning', 'Detected CR/CRLF in signature file; These are permissible and won\'t cause problems, but LF is preferable.');
        $FileToValidate = (strpos($FileToValidate, "\r\n")) ? str_replace("\r", '', $FileToValidate) : str_replace("\r", "\n", $FileToValidate);
    }
    if (substr($FileToValidate, -1) !== "\n") {
        echo $CIDRAM['ValidatorMsg']('Warning', 'Signature files should terminate with an LF linebreak.');
        $FileToValidate .= "\n";
    }
    echo $CIDRAM['ValidatorMsg']('Notice', 'Line-by-line validation has started.');
    $ArrayToValidate = explode("\n", $FileToValidate);
    $c = count($ArrayToValidate);
    for ($i = 0; $i < $c; $i++) {
        $len = strlen($ArrayToValidate[$i]);
        if ($len > 120) {
            echo $CIDRAM['ValidatorMsg']('Warning', 'L' . $i . ': Line length is greater than 120 bytes; Line length should be limited to 120 bytes for optimal readability.');
        } elseif (!$len) {
            continue;
        }
        if (isset($ArrayToValidate[$i + 1]) && $ArrayToValidate[$i + 1] === $ArrayToValidate[$i]) {
            echo $CIDRAM['ValidatorMsg']('Notice', 'L' . $i . ' and L' . ($i + 1) . ' are identical, and thus, mergeable.');
        }
        if (preg_match('/\s+$/', $ArrayToValidate[$i])) {
            echo $CIDRAM['ValidatorMsg']('Warning', 'L' . $i . ': Excess trailing whitespace detected on this line.');
        }
        if (substr($ArrayToValidate[$i], 0, 1) === '#') {
            continue;
        }
        if (substr_count($ArrayToValidate[$i], "\t")) {
            echo $CIDRAM['ValidatorMsg']('Warning', 'L' . $i . ': Tabs detected; Spaces are preferred over tabs for optimal readability.');
        } elseif (preg_match('/[^\x20-\xff]/', $ArrayToValidate[$i])) {
            echo $CIDRAM['ValidatorMsg']('Warning', 'L' . $i . ': Control characters detected; This could indicate corruption and should be investigated.');
        }
        if (substr($ArrayToValidate[$i], 0, 5) === 'Tag: ') {
            if ($len > 25) {
                echo $CIDRAM['ValidatorMsg']('Warning', 'L' . $i . ': Section tag is greater than 20 bytes; Section tags should be clear and concise.');
            }
            continue;
        }
        if (substr($ArrayToValidate[$i], 0, 9) === 'Expires: ') {
            $Expires = substr($ArrayToValidate[$i], 9);
            if (
                preg_match(
                    '/^([12][0-9]{3})(\xe2\x88\x92|[\x2d-\x2f\x5c])?(0[1-9]|1[0-2])(\xe2\x88' .
                    '\x92|[\x2d-\x2f\x5c])?(0[1-9]|[1-2][0-9]|3[01])\x20?T?([01][0-9]|2[0-3]' .
                    ')[\x2d\x2e\x3a]?([01][0-9]|2[0-3])[\x2d\x2e\x3a]?([01][0-9]|2[0-3])$/i',
                $Expires, $ExpiresArr) ||
                preg_match(
                    '/^([12][0-9]{3})(\xe2\x88\x92|[\x2d-\x2f\x5c])?(0[1-9]|1[0-2])(\xe2\x88' .
                    '\x92|[\x2d-\x2f\x5c])?(0[1-9]|[1-2][0-9]|3[01])\x20?T?([01][0-9]|2[0-3]' .
                    ')[\x2d\x2e\x3a]?([01][0-9]|2[0-3])$/i',
                $Expires, $ExpiresArr) ||
                preg_match(
                    '/^([12][0-9]{3})(\xe2\x88\x92|[\x2d-\x2f\x5c])?(0[1-9]|1[0-2])(\xe2\x88' .
                    '\x92|[\x2d-\x2f\x5c])?(0[1-9]|[1-2][0-9]|3[01])\x20?T?([01][0-9]|2[0-3]' .
                    ')$/i',
                $Expires, $ExpiresArr) ||
                preg_match(
                    '/^([12][0-9]{3})(\xe2\x88\x92|[\x2d-\x2f\x5c])?(0[1-9]|1[0-2])(\xe2\x88' .
                    '\x92|[\x2d-\x2f\x5c])?(0[1-9]|[1-2][0-9]|3[01])$/i',
                $Expires, $ExpiresArr) ||
                preg_match('/^([12][0-9]{3})(\xe2\x88\x92|[\x2d-\x2f\x5c])?(0[1-9]|1[0-2])$/i', $Expires, $ExpiresArr) ||
                preg_match('/^([12][0-9]{3})$/i', $Expires, $ExpiresArr)
            ) {
                $ExpiresArr = array(
                    $ExpiresArr[1],
                        (isset($ExpiresArr[2])) ? $ExpiresArr[2] : '01',
                        (isset($ExpiresArr[3])) ? $ExpiresArr[3] : '01',
                        (isset($ExpiresArr[4])) ? $ExpiresArr[4] : '00',
                        (isset($ExpiresArr[5])) ? $ExpiresArr[5] : '00',
                        (isset($ExpiresArr[6])) ? $ExpiresArr[6] : '00'
                );
                if (!mktime($ExpiresArr[3], $ExpiresArr[4], $ExpiresArr[5], $ExpiresArr[1], $ExpiresArr[2], $ExpiresArr[0])) {
                    echo $CIDRAM['ValidatorMsg']('Error', 'L' . $i . ': Expiry tag doesn\'t contain a valid ISO 8601 date/time!');
                }
            } else {
                echo $CIDRAM['ValidatorMsg']('Error', 'L' . $i . ': Expiry tag doesn\'t contain a valid ISO 8601 date/time!');
            }
            continue;
        }
        $Sig = array('Base' => (strpos($ArrayToValidate[$i], ' ')) ? substr($ArrayToValidate[$i], 0, strpos($ArrayToValidate[$i], ' ')) : $ArrayToValidate[$i]);
        if ($Sig['x'] = strpos($Sig['Base'], '/')) {
            $Sig['Initial'] = substr($Sig['Base'], 0, $Sig['x']);
            $Sig['Prefix'] = (integer)substr($Sig['Base'], $Sig['x'] + 1);
            $Sig['Key'] = $Sig['Prefix'] - 1;
        } else {
            echo $CIDRAM['ValidatorMsg']('Warning', 'L' . $i . ': Not syntactically precise.');
            continue;
        }
        $Sig['IPv4'] = $CIDRAM['IPv4Test']($Sig['Initial'], true);
        $Sig['IPv6'] = $CIDRAM['IPv6Test']($Sig['Initial'], true);
        if (!$Sig['IPv4'] && !$Sig['IPv6']) {
            echo $CIDRAM['ValidatorMsg']('Error', 'L' . $i . ': "' . $Sig['Initial'] . '" is *NOT* a valid IPv4 or IPv6 address!');
            continue;
        }
        if ($Sig['Base'] !== $ArrayToValidate[$i]) {
            $Sig['Function'] = substr($ArrayToValidate[$i], strlen($Sig['Base']) + 1);
            if ($Sig['x'] = strpos($Sig['Function'], ' ')) {
                $Sig['Param'] = substr($Sig['Function'], $Sig['x'] + 1);
                $Sig['Function'] = substr($Sig['Function'], 0, $Sig['x']);
            } else {
                $Sig['Param'] = '';
            }
            if ($Sig['Function'] !== 'Deny' && $Sig['Function'] !== 'Whitelist' && $Sig['Function'] !== 'Run') {
                echo $CIDRAM['ValidatorMsg']('Error', 'L' . $i . ': Unrecognised %Function%; Signature could be broken.');
            }
        } else {
            $Sig['Param'] = $Sig['Function'] = '';
            echo $CIDRAM['ValidatorMsg']('Error', 'L' . $i . ': Missing %Function%; Signature appears to be incomplete.');
        }
        if ($Sig['Function'] === 'Deny' && ($Sig['n'] = substr_count($FileToValidate, "\n" . $Sig['Base'] . ' Deny ')) && $Sig['n'] > 1) {
            echo $CIDRAM['ValidatorMsg']('Warning', 'L' . $i . ': Signature "' . $Sig['Base'] . '" is duplicated (' . $Sig['n'] . ' counts)!');
        } elseif ($Sig['Function'] === 'Whitelist' && ($Sig['n'] = substr_count($FileToValidate, "\n" . $Sig['Base'] . ' Whitelist')) && $Sig['n'] > 1) {
            echo $CIDRAM['ValidatorMsg']('Warning', 'L' . $i . ': Signature "' . $Sig['Base'] . '" is duplicated (' . $Sig['n'] . ' counts)!');
        } elseif ($Sig['Function'] === 'Run' && ($Sig['n'] = substr_count($FileToValidate, "\n" . $Sig['Base'] . ' Run ')) && $Sig['n'] > 1) {
            echo $CIDRAM['ValidatorMsg']('Warning', 'L' . $i . ': Signature "' . $Sig['Base'] . '" is duplicated (' . $Sig['n'] . ' counts)!');
        }
        if ($Sig['IPv4']) {
            if ($Sig['Key'] < 0 || $Sig['IPv4'][$Sig['Key']] !== $Sig['Base']) {
                echo $CIDRAM['ValidatorMsg']('Error', 'L' . $i . ': "' . $Sig['Base'] . '" is non-triggerable! Its base doesn\'t match the beginning of its range! Try replacing it with "' . $Sig['IPv4'][$Sig['Key']] . '".');
            }
            for ($Sig['Iterator'] = 0; $Sig['Iterator'] < $Sig['Key']; $Sig['Iterator']++) {
                if (
                    ($Sig['Function'] === 'Deny' && substr_count($FileToValidate, "\n" . $Sig['IPv4'][$Sig['Iterator']] . ' Deny')) ||
                    ($Sig['Function'] === 'Whitelist' && substr_count($FileToValidate, "\n" . $Sig['IPv4'][$Sig['Iterator']] . ' Whitelist')) ||
                    ($Sig['Function'] === 'Run' && substr_count($FileToValidate, "\n" . $Sig['IPv4'][$Sig['Iterator']] . ' Run'))
                ) {
                    echo $CIDRAM['ValidatorMsg']('Warning', 'L' . $i . ': "' . $Sig['Base'] . '" is subordinate to the already existing "' . $Sig['IPv4'][$Sig['Iterator']] . '" signature.');
                }
            }
            for ($Sig['Iterator'] = $Sig['Prefix']; $Sig['Iterator'] < 32; $Sig['Iterator']++) {
                if (substr_count($FileToValidate, "\n" . $Sig['IPv4'][$Sig['Iterator']] . ' ')) {
                    echo $CIDRAM['ValidatorMsg']('Warning', 'L' . $i . ': "' . $Sig['Base'] . '" is a superset to the already existing "' . $Sig['IPv4'][$Sig['Iterator']] . '" signature.');
                }
            }
        } elseif ($Sig['IPv6']) {
            if ($Sig['Key'] < 0 || $Sig['IPv6'][$Sig['Key']] !== $Sig['Base']) {
                echo $CIDRAM['ValidatorMsg']('Error', 'L' . $i . ': "' . $Sig['Base'] . '" is non-triggerable! Its base doesn\'t match the beginning of its range! Try replacing it with "' . $Sig['IPv6'][$Sig['Key']] . '".');
            }
            for ($Sig['Iterator'] = 0; $Sig['Iterator'] < $Sig['Key']; $Sig['Iterator']++) {
                if (
                    ($Sig['Function'] === 'Deny' && substr_count($FileToValidate, "\n" . $Sig['IPv6'][$Sig['Iterator']] . ' Deny')) ||
                    ($Sig['Function'] === 'Whitelist' && substr_count($FileToValidate, "\n" . $Sig['IPv6'][$Sig['Iterator']] . ' Whitelist')) ||
                    ($Sig['Function'] === 'Run' && substr_count($FileToValidate, "\n" . $Sig['IPv6'][$Sig['Iterator']] . ' Run'))
                ) {
                    echo $CIDRAM['ValidatorMsg']('Warning', 'L' . $i . ': "' . $Sig['Base'] . '" is subordinate to the already existing "' . $Sig['IPv6'][$Sig['Iterator']] . '" signature.');
                }
            }
            for ($Sig['Iterator'] = $Sig['Prefix']; $Sig['Iterator'] < 128; $Sig['Iterator']++) {
                if (substr_count($FileToValidate, "\n" . $Sig['IPv6'][$Sig['Iterator']] . ' ')) {
                    echo $CIDRAM['ValidatorMsg']('Warning', 'L' . $i . ': "' . $Sig['Base'] . '" is a superset to the already existing "' . $Sig['IPv6'][$Sig['Iterator']] . '" signature.');
                }
            }
        }
    }
    echo $CIDRAM['ValidatorMsg']('Notice', 'Signature validator has finished (' . date('r'). '). If no warnings or errors have appeared, your signature file is *probably* okay. :-)') . "\n";
} elseif ($CIDRAM['argv'][1] === '-f') {
    /** Attempts to automatically fix signature files (WARNING: BETA AND UNSTABLE!). */
    echo "\n";
    $FileToValidate = $CIDRAM['ReadFile']($CIDRAM['Vault'] . $CIDRAM['argv'][2]);
    echo $CIDRAM['ValidatorMsg']('Notice', 'Signature fixer has started (' . date('r'). ').');
    if (empty($FileToValidate)) {
        echo $CIDRAM['ValidatorMsg']('Fatal Error', 'Specified signature file is empty or doesn\'t exist.') . "\n";
        die;
    }
    $ModCheckBefore = '[' . md5($FileToValidate) . ':' . strlen($FileToValidate) . ']';
    $Operations = $Changes = 0;
    if ($LNs = substr_count($FileToValidate, "\r")) {
        $FileToValidate = (strpos($FileToValidate, "\r\n")) ? str_replace("\r", '', $FileToValidate) : str_replace("\r", "\n", $FileToValidate);
        $Changes += $LNs;
        $Operations++;
    }
    if ($LNs = substr_count($FileToValidate, "\t")) {
        $FileToValidate = str_replace("\t", '    ', $FileToValidate);
        $Changes += $LNs;
        $Operations++;
    }
    if (preg_match('/[^\x0a\x20-\xff]/', $FileToValidate)) {
        $LenBefore = strlen($FileToValidate);
        $FileToValidate = preg_replace('/[^\x0a\x20-\xff]/', '', $FileToValidate);
        $Changes += $LenBefore - strlen($FileToValidate);
        $Operations++;
    }
    if (substr($FileToValidate, -1) !== "\n") {
        $FileToValidate .= "\n";
        $Changes++;
        $Operations++;
    }
    $FileToValidate = "\n" . $FileToValidate;
    $ArrayToValidate = explode("\n", $FileToValidate);
    $c = count($ArrayToValidate);
    for ($i = 0; $i < $c; $i++) {
        if (!$len = strlen($ArrayToValidate[$i])) {
            continue;
        }
        if (isset($ArrayToValidate[$i + 1]) && $ArrayToValidate[$i + 1] === $ArrayToValidate[$i]) {
            $FileToValidate = str_replace("\n" . $ArrayToValidate[$i] . "\n" . $ArrayToValidate[$i] . "\n", "\n" . $ArrayToValidate[$i] . "\n", $FileToValidate);
            $Changes++;
            $Operations++;
            continue;
        }
        if (preg_match('/\s+$/', $ArrayToValidate[$i])) {
            $FileToValidate = str_replace($ArrayToValidate[$i] . "\n", preg_replace('/\s+$/', '', $ArrayToValidate[$i]) . "\n", $FileToValidate);
            $Changes++;
            $Operations++;
        }
        if (substr($ArrayToValidate[$i], 0, 1) === '#' || substr($ArrayToValidate[$i], 0, 5) === 'Tag: ') {
            continue;
        }
        $Sig = array('Base' => (strpos($ArrayToValidate[$i], ' ')) ? substr($ArrayToValidate[$i], 0, strpos($ArrayToValidate[$i], ' ')) : $ArrayToValidate[$i]);
        if ($Sig['x'] = strpos($Sig['Base'], '/')) {
            $Sig['Initial'] = substr($Sig['Base'], 0, $Sig['x']);
            $Sig['Prefix'] = (integer)substr($Sig['Base'], $Sig['x'] + 1);
            $Sig['Key'] = $Sig['Prefix'] - 1;
        } else {
            $FileToValidate = str_replace("\n" . $ArrayToValidate[$i] . "\n", "\n# " . $ArrayToValidate[$i] . "\n", $FileToValidate);
            $Changes++;
            $Operations++;
            continue;
        }
        $Sig['IPv4'] = $CIDRAM['IPv4Test']($Sig['Initial'], true);
        $Sig['IPv6'] = $CIDRAM['IPv6Test']($Sig['Initial'], true);
        if (!$Sig['IPv4'] && !$Sig['IPv6']) {
            $FileToValidate = str_replace("\n" . $ArrayToValidate[$i] . "\n", "\n# " . $ArrayToValidate[$i] . "\n", $FileToValidate);
            $Changes++;
            $Operations++;
            continue;
        }
        if ($Sig['Base'] !== $ArrayToValidate[$i]) {
            $Sig['Function'] = substr($ArrayToValidate[$i], strlen($Sig['Base']) + 1);
            if ($Sig['x'] = strpos($Sig['Function'], ' ')) {
                $Sig['Param'] = substr($Sig['Function'], $Sig['x'] + 1);
                $Sig['Function'] = substr($Sig['Function'], 0, $Sig['x']);
            } else {
                $Sig['Param'] = '';
            }
        } else {
            $Sig['Param'] = $Sig['Function'] = '';
            $FileToValidate = str_replace("\n" . $ArrayToValidate[$i] . "\n", "\n# " . $ArrayToValidate[$i] . "\n", $FileToValidate);
            $Changes++;
            $Operations++;
            continue;
        }
        if ($Sig['Function'] === 'Deny' && ($Sig['n'] = substr_count($FileToValidate, "\n" . $Sig['Base'] . ' Deny ')) && $Sig['n'] > 1) {
            $Sig['x'] = strpos($FileToValidate, "\n" . $Sig['Base'] . ' Deny ') + strlen("\n" . $Sig['Base'] . ' Deny ');
            $Sig['FilePartial'] = array(substr($FileToValidate, 0, $Sig['x']), substr($FileToValidate, $Sig['x']));
            $Sig['FilePartial'][1] = preg_replace("\x1a\n" . addslashes($Sig['Base']) . " Deny[^\n]*\n\x1ai", "\n", $Sig['FilePartial'][1]);
            $FileToValidate = $Sig['FilePartial'][0] . $Sig['FilePartial'][1];
            $Sig['FilePartial'] = '';
            $Changes += $Sig['n'] - 1;
            $Operations++;
        } elseif ($Sig['Function'] === 'Whitelist' && ($Sig['n'] = substr_count($FileToValidate, "\n" . $Sig['Base'] . ' Whitelist')) && $Sig['n'] > 1) {
            $Sig['x'] = strpos($FileToValidate, "\n" . $Sig['Base'] . ' Whitelist') + strlen("\n" . $Sig['Base'] . ' Whitelist');
            $Sig['FilePartial'] = array(substr($FileToValidate, 0, $Sig['x']), substr($FileToValidate, $Sig['x']));
            $Sig['FilePartial'][1] = preg_replace("\x1a\n" . addslashes($Sig['Base']) . " Whitelist[^\n]*\n\x1ai", "\n", $Sig['FilePartial'][1]);
            $FileToValidate = $Sig['FilePartial'][0] . $Sig['FilePartial'][1];
            $Sig['FilePartial'] = '';
            $Changes += $Sig['n'] - 1;
            $Operations++;
        }
        if ($Sig['IPv4']) {
            if ($Sig['Key'] >= 0 && $Sig['IPv4'][$Sig['Key']] !== $Sig['Base'] && $LNs = substr_count($FileToValidate, "\n" . $Sig['Base'] . ' ')) {
                $FileToValidate = str_replace("\n" . $Sig['Base'] . ' ', "\n" . $Sig['IPv4'][$Sig['Key']] . ' ', $FileToValidate);
                $Changes += $LNs;
                $Operations++;
                $Sig['Base'] = $Sig['IPv4'][$Sig['Key']];
                $Sig['Initial'] = substr($Sig['Base'], 0, strpos($Sig['Base'], '/'));
            }
            for ($Sig['Iterator'] = 0; $Sig['Iterator'] < $Sig['Key']; $Sig['Iterator']++) {
                if ($Sig['Function'] === 'Deny' && substr_count($FileToValidate, "\n" . $Sig['IPv4'][$Sig['Iterator']] . ' Deny')) {
                    $FileToValidate = preg_replace("\x1a\n" . addslashes($Sig['Base']) . " Deny[^\n]*\n\x1ai", "\n", $FileToValidate);
                    $Changes++;
                    $Operations++;
                }
                if ($Sig['Function'] === 'Whitelist' && substr_count($FileToValidate, "\n" . $Sig['IPv4'][$Sig['Iterator']] . ' Whitelist')) {
                    $FileToValidate = preg_replace("\x1a\n" . addslashes($Sig['Base']) . " Whitelist[^\n]*\n\x1ai", "\n", $FileToValidate);
                    $Changes++;
                    $Operations++;
                }
            }
        } elseif ($Sig['IPv6']) {
            if ($Sig['Key'] >= 0 && $Sig['IPv6'][$Sig['Key']] !== $Sig['Base'] && $LNs = substr_count($FileToValidate, "\n" . $Sig['Base'] . ' ')) {
                $FileToValidate = str_replace("\n" . $Sig['Base'] . ' ', "\n" . $Sig['IPv6'][$Sig['Key']] . ' ', $FileToValidate);
                $Changes += $LNs;
                $Operations++;
                $Sig['Base'] = $Sig['IPv6'][$Sig['Key']];
                $Sig['Initial'] = substr($Sig['Base'], 0, strpos($Sig['Base'], '/'));
            }
            for ($Sig['Iterator'] = 0; $Sig['Iterator'] < $Sig['Key']; $Sig['Iterator']++) {
                if ($Sig['Function'] === 'Deny' && substr_count($FileToValidate, "\n" . $Sig['IPv6'][$Sig['Iterator']] . ' Deny')) {
                    $FileToValidate = preg_replace("\x1a\n" . addslashes($Sig['Base']) . " Deny[^\n]*\n\x1ai", "\n", $FileToValidate);
                    $Changes++;
                    $Operations++;
                }
                if ($Sig['Function'] === 'Whitelist' && substr_count($FileToValidate, "\n" . $Sig['IPv6'][$Sig['Iterator']] . ' Whitelist')) {
                    $FileToValidate = preg_replace("\x1a\n" . addslashes($Sig['Base']) . " Whitelist[^\n]*\n\x1ai", "\n", $FileToValidate);
                    $Changes++;
                    $Operations++;
                }
            }
        }
    }
    $FileToValidate = substr($FileToValidate, 1);
    if ($ModCheckBefore !== '[' . md5($FileToValidate) . ':' . strlen($FileToValidate) . ']') {
        $Handle = fopen($CIDRAM['Vault'] . $CIDRAM['argv'][2] . '.fixed', 'w');
        fwrite($Handle, $FileToValidate);
        fclose($Handle);
    }
    echo $CIDRAM['ValidatorMsg']('Notice', 'Signature fixer has finished, with ' . $Changes . ' changes made over ' . $Operations . ' operations (' . date('r'). ').') . "\n";
}