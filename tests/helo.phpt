--TEST--
Net_SMTP: helo()
--FILE--
<?php

require_once 'Net/SMTP.php';
require_once dirname(__FILE__) . '/smtp_server.inc';

$first = array('EHLO localhost', "250-localhost\r\n250-PIPELINING\r\n250 AUTH PLAIN");

echo "HELO accepted\n";
$smtp = smtp_server_start(array(
    $first,
    array('HELO localhost', '250 localhost'),
    array('MAIL FROM:<from@example.com>', '550 Sender rejected'),
), true);
$smtp->connect();
echo smtp_result($smtp->helo('localhost')), "\n";
echo count($smtp->getServiceExtensions()), "\n";
echo smtp_result($smtp->mailFrom('from@example.com')), "\n";
$smtp->disconnect();

echo "HELO refused\n";
$smtp = smtp_server_start(array(
    $first,
    array('HELO localhost', '502 Command not implemented'),
));
$smtp->connect();
echo smtp_result($smtp->helo('localhost')), "\n";
echo smtp_extensions($smtp, array('PIPELINING', 'AUTH')), "\n";
$smtp->disconnect();

--EXPECT--
HELO accepted
true
0
Invalid response code received from server (550)
HELO refused
Invalid response code received from server (502)
PIPELINING AUTH
