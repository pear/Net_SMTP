<?php
/**
 * Scripted SMTP server for the tests, started by smtp_server_start().
 *
 * The script is a list of array(command, reply). The server expects that
 * command line next and answers with the reply (a multiline reply has its
 * lines joined by "\r\n"). A reply given as an array is sent as it is,
 * piece by piece 0.8 s apart, with no line end added. The server closes
 * the connection instead of replying if the reply is null, and after
 * replying, without reading a command, if the command is null.
 * After a positive reply to STARTTLS it switches to TLS. Any other command
 * is reported on stderr, which makes the test fail.
 */

$script = unserialize(stream_get_contents(STDIN));
$script[] = array('QUIT', '221 localhost closing connection');

$context = stream_context_create(array('ssl' => array(
    'local_cert' => dirname(__FILE__) . '/smtp_server.pem',
)));
$server = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr,
    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);
echo substr(strrchr(stream_socket_get_name($server, false), ':'), 1), "\n";
fflush(STDOUT);

/* Before PHP 7.2 TLS_SERVER means TLS 1.0 only */
$tls = STREAM_CRYPTO_METHOD_TLS_SERVER;
foreach (array('1_1', '1_2', '1_3') as $version) {
    if (defined("STREAM_CRYPTO_METHOD_TLSv{$version}_SERVER")) {
        $tls |= constant("STREAM_CRYPTO_METHOD_TLSv{$version}_SERVER");
    }
}

$conn = stream_socket_accept($server, 10);
stream_set_timeout($conn, 10);
fwrite($conn, "220 localhost ESMTP\r\n");

foreach ($script as $step) {
    list($command, $reply) = $step;

    if ($command !== null) {
        $line = fgets($conn);
        if ($line === false || rtrim($line, "\r\n") !== $command) {
            fwrite(STDERR, sprintf("smtp_server: expected \"%s\", got %s\n", $command,
                $line === false ? 'no command' : '"' . rtrim($line, "\r\n") . '"'));
            break;
        }
    }

    if (is_array($reply)) {
        foreach ($reply as $i => $piece) {
            if ($i > 0) {
                usleep(800000);
            }
            @fwrite($conn, $piece);
        }
    } elseif ($reply !== null) {
        fwrite($conn, $reply . "\r\n");
    }
    if ($command === null || $reply === null) {
        break;
    }

    if ($command === 'STARTTLS' && $reply[0] === '2') {
        stream_socket_enable_crypto($conn, true, $tls);
    }
}

fclose($conn);
