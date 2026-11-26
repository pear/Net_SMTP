--TEST--
Net_SMTP: ehlo()
--FILE--
<?php

require_once 'Net/SMTP.php';
require_once dirname(__FILE__) . '/smtp_server.inc';

$names = array('X-FIRST', 'X-SECOND', 'PIPELINING', 'AUTH', 'Reset');
$first = array('EHLO localhost', "250-localhost\r\n250-PIPELINING\r\n250 X-FIRST");
$second = array('EHLO localhost', "250-localhost\r\n250 X-SECOND");
$refused = array(
    array('EHLO localhost', '502 Command not implemented'),
    array('HELO localhost', '502 Command not implemented'),
);

echo "EHLO accepted\n";
$smtp = smtp_server_start(array($first, $second));
$smtp->connect();
echo smtp_result($smtp->ehlo()), "\n";
echo smtp_extensions($smtp, $names), "\n";
$smtp->disconnect();

echo "Only HELO accepted\n";
$smtp = smtp_server_start(array(
    $first,
    array('EHLO localhost', '502 Command not implemented'),
    array('HELO localhost', '250 localhost'),
    array('MAIL FROM:<from@example.com>', '550 Sender rejected'),
), true);
$smtp->connect();
echo smtp_result($smtp->ehlo()), "\n";
echo count($smtp->getServiceExtensions()), "\n";
echo smtp_result($smtp->mailFrom('from@example.com')), "\n";
$smtp->disconnect();

echo "EHLO and HELO refused: the session stays as it was\n";
$smtp = smtp_server_start(array_merge(array($first), $refused));
$smtp->connect();
echo smtp_result($smtp->ehlo()), "\n";
echo smtp_extensions($smtp, $names), "\n";
$smtp->disconnect();

echo "EHLO and HELO refused after the server has reset the session\n";
$smtp = smtp_server_start(array_merge(array(
    $first,
    array('XCLIENT ADDR=192.0.2.1', '220 localhost ESMTP'),
), $refused, array(
    array('MAIL FROM:<from@example.com>', '503 Send HELO first'),
)), true);
$smtp->connect();
$smtp->command('XCLIENT ADDR=192.0.2.1', 220);
echo smtp_result($smtp->ehlo(true)), "\n";
echo count($smtp->getServiceExtensions()), "\n";
echo smtp_result($smtp->mailFrom('from@example.com')), "\n";
$smtp->disconnect();

echo "EHLO cannot be sent after the server has reset the session\n";
$smtp = smtp_server_start(array(
    $first,
    array('XCLIENT ADDR=192.0.2.1', '220 localhost ESMTP'),
    array('MAIL FROM:<from@example.com>', '503 Send HELO first'),
), true);
$smtp->connect();
$smtp->command('XCLIENT ADDR=192.0.2.1', 220);
$smtp->localhost = "local\nhost";
var_dump(PEAR::isError($smtp->ehlo(true)));
echo count($smtp->getServiceExtensions()), "\n";
echo smtp_result($smtp->mailFrom('from@example.com')), "\n";
$smtp->disconnect();

echo "PIPELINING offered again after a HELO session\n";
$smtp = smtp_server_start(array(
    $first,
    array('EHLO localhost', '502 Command not implemented'),
    array('HELO localhost', '250 localhost'),
    $first,
    array('MAIL FROM:<from@example.com>', '550 Sender rejected'),
    array('NOOP', '250 OK'),
), true);
$smtp->connect();
$smtp->ehlo();
echo smtp_extensions($smtp, $names), "\n";
$smtp->ehlo();
echo smtp_extensions($smtp, $names), "\n";
echo smtp_result($smtp->mailFrom('from@example.com')), "\n";
$smtp->command('NOOP', 250);
list(, $text) = $smtp->getResponse();
echo str_replace("\n", ' / ', $text), "\n";
$smtp->disconnect();

echo "EHLO ends a pipelined group\n";
$smtp = smtp_server_start(array($first, array('RSET', '250 Reset OK'), $second), true);
$smtp->connect();
$smtp->rset();
echo smtp_result($smtp->ehlo()), "\n";
echo smtp_extensions($smtp, $names), "\n";
$smtp->disconnect();

echo "Connection closed before the reply to EHLO\n";
$smtp = smtp_server_start(array($first, array('RSET', '250 Reset OK'), array('EHLO localhost', null)), true);
$smtp->connect();
$smtp->rset();
var_dump(PEAR::isError($smtp->ehlo()));

echo "Connection closed in the middle of the reply to EHLO\n";
$smtp = smtp_server_start(array(
    $first,
    array('EHLO localhost', '250-X-SECOND'),
    array(null, null),
));
$smtp->connect();
echo smtp_result($smtp->ehlo()), "\n";
echo smtp_extensions($smtp, $names), "\n";

echo "Connection closed in the middle of the last line of the reply to EHLO\n";
$smtp = smtp_server_start(array(
    $first,
    array('EHLO localhost', '250-localhost'),
    array(null, array('250 AUTH PLA')),
));
$smtp->connect();
echo smtp_result($smtp->ehlo()), "\n";
echo smtp_extensions($smtp, $names), "\n";

--EXPECT--
EHLO accepted
true
X-SECOND
Only HELO accepted
true
0
Invalid response code received from server (550)
EHLO and HELO refused: the session stays as it was
HELO was not accepted (502)
X-FIRST PIPELINING
EHLO and HELO refused after the server has reset the session
HELO was not accepted (502)
0
Invalid response code received from server (503)
EHLO cannot be sent after the server has reset the session
bool(true)
0
Invalid response code received from server (503)
PIPELINING offered again after a HELO session

X-FIRST PIPELINING
true
Sender rejected / OK
EHLO ends a pipelined group
true
X-SECOND
Connection closed before the reply to EHLO
bool(true)
Connection closed in the middle of the reply to EHLO
Invalid response code received from server (-1)
X-FIRST PIPELINING
Connection closed in the middle of the last line of the reply to EHLO
Invalid response code received from server (-1)
X-FIRST PIPELINING
