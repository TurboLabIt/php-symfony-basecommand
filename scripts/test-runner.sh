#!/usr/bin/env bash

## https://github.com/TurboLabIt/webstackup/blob/master/script/base.sh
source "/usr/local/turbolab.it/webstackup/script/base.sh"
fxHeader "🧪 BaseCommand Tester"
EXPECTED_USER=$(logname)

cd $PROJECT_DIR

wsuComposer install

phpunit \
  --bootstrap vendor/autoload.php \
  --cache-result-file=/tmp/.phpunit.result.cache \
  --stop-on-failure \
  tests

PHPUNIT_RESULT=$?
echo ""

if [ "${PHPUNIT_RESULT}" = 0 ]; then

  fxMessage "🎉 TEST RAN SUCCESSFULLY!"

  fxTitle "Running the test command..."
  php tests/RunTestCommand.php
  wsuPlayOKSound

else

  fxMessage "🛑 TEST FAILED | phpunit returned ${PHPUNIT_RESULT}"
  wsuPlayKOSound
fi

fxTitle "Cleaning up..."
rm -rf /tmp/BaseCommandTestInstance

fxEndFooter
