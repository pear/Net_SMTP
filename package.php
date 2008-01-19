<?php

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$desc = <<<EOT
Provides an implementation of the SMTP protocol using PEAR's Net_Socket class.
EOT;

$version = '1.2.11';
$notes = <<<EOT
- package.xml version 2 is now used exclusively.
- Skip unit tests when the configuration file is not available.
EOT;

$package = new PEAR_PackageFileManager2();

$result = $package->setOptions(array(
    'filelistgenerator' => 'cvs',
    'changelogoldtonew' => false,
    'simpleoutput'      => true,
    'baseinstalldir'    => 'Net',
    'packagefile'       => 'package.xml',
    'packagedirectory'  => '.'));

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}

$package->setPackage('Net_SMTP');
$package->setPackageType('php');
$package->setSummary('An implementation of the SMTP protocol');
$package->setDescription($desc);
$package->setChannel('pear.php.net');
$package->setLicense('PHP License', 'http://www.php.net/license/3_01.txt');
$package->setAPIVersion('1.0.0');
$package->setAPIStability('stable');
$package->setReleaseVersion($version);
$package->setReleaseStability('stable');
$package->setNotes($notes);
$package->setPhpDep('4.0.5');
$package->setPearinstallerDep('1.4.3');
$package->addMaintainer('lead', 'jon', 'Jon Parise', 'jon@php.net');
$package->addMaintainer('lead', 'chagenbu', 'Chuck Hagenbuch', 'chuck@horde.org');
$package->addIgnore(array('package.php', 'phpdoc.sh', 'package.xml'));
$package->addPackageDepWithChannel('required', 'Net_Socket', 'pear.php.net', '1.0.7');
$package->addPackageDepWithChannel('optional', 'Auth_SASL', 'pear.php.net');

$package->generateContents();

if ($_SERVER['argv'][1] == 'commit') {
    $result = $package->writePackageFile();
} else {
    $result = $package->debugPackageFile();
}

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}
