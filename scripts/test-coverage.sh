#!/bin/bash
set -o nounset -o pipefail -o errexit
cd "$(dirname "$0")/.."

################################################################################
# Run PHPUnit and generate an HTML code coverage report (requires Xdebug or PCOV).
################################################################################

if [ ! -d vendor ]; then
    composer install
fi

exec php vendor/bin/phpunit --coverage-html test-coverage/ "$@"
