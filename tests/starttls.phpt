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
)));
$smtp->connect();
echo smtp_result($smtp->starttls()), "\n";
echo count($smtp->getServiceExtensions()), "\n";
$smtp->disconnect();

--EXPECT--
EHLO accepted
true
X-AFTER-TLS
EHLO and HELO refused
HELO was not accepted (502)
0
