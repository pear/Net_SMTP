<?
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Chuck Hagenbuch <chuck@horde.org>                           |
// |          Jon Parise <jon@php.net>                                    |
// |          Damian Alejandro Fernandez Sosa <damlists@cnba.uba.ar>      |
// +----------------------------------------------------------------------+


/*
   This example shows you how to use the Net_SMTP class
   debug the SMTP dialog

   On error the script halts
*/

require_once('Net/SMTP.php');



// The SMTP server 
$host="localhost";

// The default SMTP port
$port="25";
// The name we send to initiate the SMTP dialog
$localhost="localhost";

// The email as we send the email in the SMTP dialog
$from="user1@domain.net";
// The email to send the email in the SMTP dialog
$to="user2@domain.net";

// The email text (RFC822 format)
$email="Subject: testing\r\n\r\nthis is a test email\r\n";


// We create the Net_SMTP instance
$smtp_conn= new Net_SMTP( $host ,  $port , $localhost);


// Set the debug mode on
$smtp_conn->setDebug(true);

// Connect to the SMTP server
if (PEAR::isError( $error = $smtp_conn->connect())) {
    echo "ERROR:" . $error->getMessage() . "\n";
    exit();
}

// Send the MAIL FROM: SMTP command
if (PEAR::isError( $error = $smtp_conn->mailFrom($from))) {
    echo "ERROR:" . $error->getMessage() . "\n";
    exit();
}

// Send the RCPT TO: SMTP command
if (PEAR::isError( $error = $smtp_conn->rcptTo($to))) {
    echo "ERROR:" . $error->getMessage() . "\n";
    exit();
}

// Send the DATA: SMTP command (we send the email RFC822 encoded)
if (PEAR::isError( $error = $smtp_conn->data($email))) {
    echo "ERROR:" . $error->getMessage() . "\n";
    exit();
}
// now the email was accepted by the SMTP server, so we close 
// the connection


// Send the QUIT SMTP command and disconnect from the SMTP server
if (PEAR::isError( $error = $smtp_conn->disconnect())) {
    echo "ERROR:" . $error->getMessage() . "\n";
    exit();
}


?>
