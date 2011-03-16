<?php

$base = array(Pearfarm_PackageSpec::OPT_BASEDIR => dirname(__FILE__));

$spec = Pearfarm_PackageSpec::create($base);

#------------------------
# Application Information
#------------------------

$spec->setName('Getopti');

$spec->setSummary('A command-line parsing tool.');

$spec->setDescription("A command-line parsing tool inspired by Ruby's Optparser library.");

$spec->setDependsOnPHPVersion('5.3.0');

$spec->setLicense('MIT');

#-------------------------
# Distribution Information
#-------------------------

$spec->setChannel('bschaeffer.pearfarm.org');

#------------
# Maintainers
#------------

$spec->addMaintainer('lead', 'Braden Schaeffer', 'bschaeffer', 'hello@manasto.info');

#--------------------
# Release Information
#--------------------

$spec->setReleaseVersion('0.1.1');
$spec->setReleaseStability('alpha');

$spec->setApiVersion('0.1.1');
$spec->setApiStability('alpha');

$spec->setNotes('See http://github.com/bschaeffer/getopti for more information.');

#---------------------------
# File Inclusion Information
#---------------------------

$spec->addFilesSimple('Getopti.php');
$spec->addFilesRegex('/^Getopti\/.*/');

$spec->addFilesSimple('README.md', 'doc');
$spec->addFilesSimple('LICENSE', 'doc');
