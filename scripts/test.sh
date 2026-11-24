#!/bin/bash
set -o nounset -o pipefail -o errexit
cd "$(dirname "$0")/.."

################################################################################
# Run PHPUnit.
################################################################################

if [ ! -d vendor ]; then
    composer install
fi

php vendor/bin/phpunit "$@"
