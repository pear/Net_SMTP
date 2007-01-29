<?php

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$desc = <<<EOT
Provides an implementation of the SMTP protocol using PEAR's Net_Socket class.
EOT;

$version = '1.2.9';
$notes = <<<EOT
We now return the SMTP error code in an invalid response's PEAR_Error object.
EOT;

$package = new PEAR_PackageFileManager2();

$result = $package->setOptions(array(
    'filelistgenerator' => 'cvs',
    'changelogoldtonew' => false,
    'simpleoutput'		=> true,
    'baseinstalldir'    => 'Net',
    'packagefile'       => 'package2.xml',
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
$package->addIgnore(array('package.php', 'phpdoc.sh', 'package.xml', 'package2.xml'));
$package->addPackageDepWithChannel('required', 'Net_Socket', 'pear.php.net');
$package->addPackageDepWithChannel('optional', 'Auth_SASL', 'pear.php.net');

$package->generateContents();
$package1 = &$package->exportCompatiblePackageFile1();

if ($_SERVER['argv'][1] == 'commit') {
    $result = $package->writePackageFile();
    $result = $package1->writePackageFile();
} else {
    $result = $package->debugPackageFile();
    $result = $package1->debugPackageFile();
}

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}
