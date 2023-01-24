#!/usr/bin/env bash
## Test this package!

APP_NAME=BaseCommand
source "/usr/local/turbolab.it/webstackup/script/php/test-runner-package.sh"

fxTitle "🧹 Cleaning up..."
rm -rf /tmp/BaseCommandTestInstance

fxEndFooter
