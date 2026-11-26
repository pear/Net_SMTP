--TEST--
Net_SMTP: server that accepts only HELO
--FILE--
<?php

require_once 'Net/SMTP.php';
require_once dirname(__FILE__) . '/smtp_server.inc';

/* A session with service extensions first */
$smtp = smtp_server_start(array(
    array('EHLO localhost', "250-localhost\r\n250-PIPELINING\r\n250 X-FIRST"),
), true);
$smtp->connect();
$smtp->disconnect();

$next = smtp_server_start(array(
    array('EHLO localhost', '502 Command not implemented'),
    array('HELO localhost', '250 localhost'),
    array('MAIL FROM:<from@example.com>', '550 Sender rejected'),
));
$smtp->port = $next->port;
echo smtp_result($smtp->connect()), "\n";
echo count($smtp->getServiceExtensions()), "\n";
echo smtp_result($smtp->mailFrom('from@example.com')), "\n";
$smtp->disconnect();

--EXPECT--
true
0
Invalid response code received from server (550)
