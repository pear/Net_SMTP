--TEST--
Net_SMTP: connecting again with the same object
--FILE--
<?php

require_once 'Net/SMTP.php';
require_once dirname(__FILE__) . '/smtp_server.inc';

$names = array('X-FIRST', 'X-SECOND');

$smtp = smtp_server_start(array(
    array('EHLO localhost', "250-localhost\r\n250 X-FIRST"),
));
$smtp->connect();
echo smtp_extensions($smtp, $names), "\n";
$smtp->disconnect();

echo "EHLO accepted\n";
$next = smtp_server_start(array(
    array('EHLO localhost', "250-localhost\r\n250 X-SECOND"),
));
$smtp->port = $next->port;
$smtp->connect();
echo smtp_extensions($smtp, $names), "\n";
$smtp->disconnect();

echo "EHLO and HELO refused\n";
$next = smtp_server_start(array(
    array('EHLO localhost', '502 Command not implemented'),
    array('HELO localhost', '502 Command not implemented'),
));
$smtp->port = $next->port;
echo smtp_result($smtp->connect()), "\n";
echo count($smtp->getServiceExtensions()), "\n";
$smtp->disconnect();

--EXPECT--
X-FIRST
EHLO accepted
X-SECOND
EHLO and HELO refused
HELO was not accepted (502)
0
