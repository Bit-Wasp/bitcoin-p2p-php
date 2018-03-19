#!/bin/bash

function runDirPHP()
{
    echo "Checking PHP files in ${1}"
    for i in $1/*.php; do
        php $i > /dev/null
        if [ $? -ne 0 ]; then
            echo "Error running example code: $i";
            exit -1
        fi;
    done
}

runDirPHP examples/;
