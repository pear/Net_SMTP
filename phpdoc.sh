#!/bin/sh

phpdoc -f SMTP.php -t docs/api -p -ti "Net_SMTP Package API" -dn Net_SMTP -dc Net_SMTP -ed examples -i CVS/*
