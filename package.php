<?php

require_once 'PEAR/PackageFileManager.php';
require_once 'Console/Getopt.php';

$version = '1.2.6';
$notes = <<<EOT
Renaming some methods to be compatible with the PEAR coding standards.  Backwards-compatible wrappers have been provided.
EOT;

$changelog = <<<EOT
The following methods have been renamed for compliance with the PEAR coding standards: send_from() -> sendFrom(), soml_from() -> somlFrom(), saml_from() -> samlFrom().  Backwards-compatible wrappers have been provided.
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
    'simpleoutput'      => true,
    'baseinstalldir'    => 'Net',
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
