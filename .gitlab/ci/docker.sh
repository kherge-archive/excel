#!/bin/bash

# Do not run if not in Docker.
[[ ! -e /.dockerenv ]]  && \
[[ ! -e /.dockerinit ]] && \
    exit 0

###
# Buffers the output until an error occurs.
##
function buffer
{
    local LOG=$(mktemp)

    "$@" 2>&1 > "$LOG"

    local STATUS=$?

    if [ ${STATUS} != 0 ]; then
        cat "$LOG"
    fi

    return ${STATUS}
}

# Verbose command and quit on error.
set -xe

# Update package repository information.
buffer apt-get update -yqq

# Install dependencies.
buffer apt-get install git zlib1g-dev -yqq

# Install PHP extensions.
buffer docker-php-ext-install zip

# Install Composer.
curl -LSs https://getcomposer.org/installer | php

# Add Composer executables to the path.
export PATH="$HOME/.composer/vendor/bin:$PATH"

# Install PHPUnit.
buffer ./composer.phar global require phpunit/phpunit
