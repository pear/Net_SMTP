--TEST--
Net_SMTP: greeting after STARTTLS
--SKIPIF--
<?php if (!extension_loaded('openssl')) die("skip openssl extension not available\n");
--FILE--
<?php

require_once 'Net/SMTP.php';
require_once dirname(__FILE__) . '/smtp_server.inc';

$before_tls = array(
    array('EHLO localhost', "250-localhost\r\n250-STARTTLS\r\n250-PIPELINING\r\n250 X-BEFORE-TLS"),
    array('STARTTLS', '220 Ready to start TLS'),
);
$names = array('STARTTLS', 'PIPELINING', 'X-BEFORE-TLS', 'X-AFTER-TLS');

echo "EHLO accepted\n";
$smtp = smtp_server_start(array_merge($before_tls, array(
    array('EHLO localhost', "250-localhost\r\n250 X-AFTER-TLS"),
)));
$smtp->connect();
echo smtp_result($smtp->starttls()), "\n";
echo smtp_extensions($smtp, $names), "\n";
$smtp->disconnect();

echo "EHLO and HELO refused\n";
$smtp = smtp_server_start(array_merge($before_tls, array(
    array('EHLO localhost', '502 Command not implemented'),
    array('HELO localhost', '502 Command not implemented'),
    array('MAIL FROM:<from@example.com>', '503 Send HELO first'),
)), true);
$smtp->connect();
echo smtp_result($smtp->starttls()), "\n";
echo count($smtp->getServiceExtensions()), "\n";
echo smtp_result($smtp->mailFrom('from@example.com')), "\n";
$smtp->disconnect();

echo "Only HELO accepted\n";
$smtp = smtp_server_start(array_merge($before_tls, array(
    array('EHLO localhost', '502 Command not implemented'),
    array('HELO localhost', '250 localhost'),
    array('MAIL FROM:<from@example.com>', '550 Sender rejected'),
)), true);
$smtp->connect();
echo smtp_result($smtp->starttls()), "\n";
echo count($smtp->getServiceExtensions()), "\n";
echo smtp_result($smtp->mailFrom('from@example.com')), "\n";
$smtp->disconnect();

echo "PIPELINING offered only after TLS\n";
$smtp = smtp_server_start(array(
    array('EHLO localhost', "250-localhost\r\n250 STARTTLS"),
    array('STARTTLS', '220 Ready to start TLS'),
    array('EHLO localhost', "250-localhost\r\n250 PIPELINING"),
    array('MAIL FROM:<from@example.com>', '550 Sender rejected'),
    array('NOOP', '250 OK'),
), true);
$smtp->connect();
echo smtp_result($smtp->starttls()), "\n";
echo smtp_result($smtp->mailFrom('from@example.com')), "\n";
$smtp->command('NOOP', 250);
list(, $text) = $smtp->getResponse();
echo str_replace("\n", ' / ', $text), "\n";
$smtp->disconnect();

--EXPECT--
EHLO accepted
true
X-AFTER-TLS
EHLO and HELO refused
HELO was not accepted (502)
0
Invalid response code received from server (503)
Only HELO accepted
true
0
Invalid response code received from server (550)
PIPELINING offered only after TLS
true
true
Sender rejected / OK
