<?php

require_once 'PEAR/PackageFileManager.php';
require_once 'Console/Getopt.php';

$version = '1.2.4';
$notes = <<<EOT
It's now possible to request a persistent socket connection when calling the connect() method.
EOT;

$changelog = <<<EOT
The connect() method has grown an optional $persistent parameter that will be passed to the Net_Socket::connect() method.  Previously, persistent connections will always disabled.  $persistent defaults to false.
EOT;

$package = new PEAR_PackageFileManager();

$result = $package->setOptions(array(
    'package'           => 'Net_SMTP',
    'summary'           => 'Provides an implementation of the SMTP protocol',
    'version'           => $version,
    'state'             => 'stable',
    'license'           => 'PHP License',
    'filelistgenerator' => 'cvs',
    'ignore'            => array('package.php', 'phpdoc.sh'),
    'notes'             => $notes,
    'changelognotes'    => $changelog,
    'changelogoldtonew' => false,
	'simpleoutput'		=> true,
    'baseinstalldir'    => '/',
    'packagedirectory'  => ''));

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}

$package->addMaintainer('jon', 'lead', 'Jon Parise', 'jon@php.net');

$package->addDependency('Net_Socket', false, 'has', 'pkg');
$package->addDependency('Auth_SASL', false, 'has', 'pkg', true);

if ($_SERVER['argv'][1] == 'commit') {
    $result = $package->writePackageFile();
} else {
    $result = $package->debugPackageFile();
}

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}
