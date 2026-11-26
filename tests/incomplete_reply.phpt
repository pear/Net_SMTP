--TEST--
Net_SMTP: incomplete replies
--FILE--
<?php

require_once 'Net/SMTP.php';
require_once dirname(__FILE__) . '/smtp_server.inc';

echo "Connection closed before the last reply of a group\n";
$smtp = smtp_server_start(array(
    array('EHLO localhost', "250-localhost\r\n250 PIPELINING"),
    array('RSET', '250 Reset OK'),
    array('NOOP', null),
), true);
$smtp->connect();
echo smtp_result($smtp->rset()), "\n";
echo smtp_result($smtp->command('NOOP', 250)), "\n";

echo "Connection closed in the middle of a reply\n";
$smtp = smtp_server_start(array(
    array('EHLO localhost', "250-localhost\r\n250 X-FIRST"),
    array('NOOP', '250-First line'),
    array(null, null),
));
$smtp->connect();
echo smtp_result($smtp->command('NOOP', 250)), "\n";

echo "Connection closed in the middle of a line\n";
$smtp = smtp_server_start(array(
    array('EHLO localhost', "250-localhost\r\n250 X-FIRST"),
    array('NOOP', '250-First line'),
    array(null, array('250 Second li')),
));
$smtp->connect();
echo smtp_result($smtp->command('NOOP', 250)), "\n";

echo "Line of several reads that does not end within the timeout\n";
$piece = str_repeat('x', 1023);
$smtp = smtp_server_start(array(
    array('EHLO localhost', "250-localhost\r\n250 X-FIRST"),
    array('NOOP', '250-First line'),
    array(null, array('250 ' . substr($piece, 4), $piece, $piece, $piece, $piece, "\r\n")),
), false, 2);
$smtp->connect();
echo smtp_result($smtp->command('NOOP', 250)), "\n";

echo "Reply that comes later, non-blocking socket\n";
class Net_SMTP_Nonblocking extends Net_SMTP
{
    public function setBlocking($mode)
    {
        return $this->socket->setBlocking($mode);
    }
}
$smtp = smtp_server_start(array(
    array('EHLO localhost', "250-localhost\r\n250 X-FIRST"),
    array('NOOP', array('', "250 OK\r\n")),
), false, 3, 'Net_SMTP_Nonblocking');
$smtp->connect();
$smtp->setBlocking(false);
echo smtp_result($smtp->command('NOOP', 250)), "\n";
$smtp->setBlocking(true);
$smtp->disconnect();

--EXPECT--
Connection closed before the last reply of a group
true
Invalid response code received from server (-1)
Connection closed in the middle of a reply
Invalid response code received from server (-1)
Connection closed in the middle of a line
Invalid response code received from server (-1)
Line of several reads that does not end within the timeout
Invalid response code received from server (-1)
Reply that comes later, non-blocking socket
true
