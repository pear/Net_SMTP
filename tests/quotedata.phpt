--TEST--
Net_SMTP: quotedata() \n | \r  => \r\n replacement
--FILE--
<?php

error_reporting(E_ALL);
require_once 'Net/SMTP.php';

$tests = array(
    "\n"               => "\r\n",
    "\r\n"             => "\r\n",
    "\nxx"             => "\r\nxx",
    "xx\n"             => "xx\r\n",
    "xx\nxx"           => "xx\r\nxx",
    "\n\nxx"           => "\r\n\r\nxx",
    "xx\n\nxx"         => "xx\r\n\r\nxx",
    "xx\n\n"           => "xx\r\n\r\n",
    "\r\nxx"           => "\r\nxx",
    "xx\r\n"           => "xx\r\n",
    "xx\r\nxx"         => "xx\r\nxx",
    "\r\n\r\nxx"       => "\r\n\r\nxx",
    "xx\r\n\r\nxx"     => "xx\r\n\r\nxx",
    "xx\r\n\r\n"       => "xx\r\n\r\n",
    "\r\n\nxx"         => "\r\n\r\nxx",
    "\n\r\nxx"         => "\r\n\r\nxx",
    "xx\r\n\nxx"       => "xx\r\n\r\nxx",
    "xx\n\r\nxx"       => "xx\r\n\r\nxx",
    "xx\r\n\n"         => "xx\r\n\r\n",
    "xx\n\r\n"         => "xx\r\n\r\n",
    "\r"               => "\r\n",
    "\rxx"             => "\r\nxx",
    "xx\rxx"           => "xx\r\nxx",
    "xx\r"             => "xx\r\n",
    "\r\r"             => "\r\n\r\n",
    "\r\rxx"           => "\r\n\r\nxx",
    "xx\r\rxx"         => "xx\r\n\r\nxx",
    "xx\r\r"           => "xx\r\n\r\n",
    "xx\rxx\nxx\r\nxx" => "xx\r\nxx\r\nxx\r\nxx",
    "\r\r\n\n"         => "\r\n\r\n\r\n",
);

$hadError = false;
foreach ($tests as $input => $expect) {
    $output = $input;
    Net_SMTP::quotedata($output);
    if ($output != $expect) {
        echo "Error: input " . prettyprint($input) . ", output " . prettyprint($output) . ", expected " . prettyprint($expect) . "\n";
        $hadError = true;
    }
}

if (!$hadError) {
    echo "success\n";
}

function prettyprint($x)
{
    return str_replace(array("\r", "\n"), array('\r', '\n'), $x);
}

--EXPECT--
success
