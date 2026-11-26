<?php
/**
 * Start tests/smtp_server.php with the given script and return a Net_SMTP
 * object for it.
 */
function smtp_server_start($script, $pipelining = false)
{
    $GLOBALS['smtp_servers'][] = proc_open(
        escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(dirname(__FILE__) . '/smtp_server.php'),
        array(0 => array('pipe', 'r'), 1 => array('pipe', 'w')), $pipes);
    fwrite($pipes[0], serialize($script));
    fclose($pipes[0]);
    $port = (int) fgets($pipes[1]);

    return new Net_SMTP('127.0.0.1', $port, 'localhost', $pipelining, 10,
        array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false)));
}

/**
 * "true", "false", or the message and the code of an error.
 */
function smtp_result($result)
{
    if (PEAR::isError($result)) {
        return $result->getMessage() . ' (' . $result->getCode() . ')';
    }

    return var_export($result, true);
}

/**
 * The service extensions the server lists, out of those named.
 */
function smtp_extensions($smtp, $names)
{
    return implode(' ', array_intersect($names, array_keys($smtp->getServiceExtensions())));
}
