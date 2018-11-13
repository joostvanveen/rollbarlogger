# Joostvanveen_RollbarLogger
Logs errors, exceptions and log messages to Rollbar.com 

## Installation
1. Install through Composer `composer require joostvanveen/rollbarlogger`
1. Enable the extension: `php bin/magento module:enable Joostvanveen_RollbarLogger`
1. Run the setup script: `php bin/magento setup:upgrade`
1. Configure settings in Magento admin panel: Stores › Config › Advanced › Rollbar Logger
1. There is no step 5.

## Setup
Available settings in Magento admin panel: Stores › Config › Advanced › Rollbar Logger
* **Enable logging to Rollbar** : Switch logging on or off.
* **Rollbar environment** : The environment to send to Rollbar. Typically: local, staging or production
* **Rollbar post_server_item token** : The Rollbar token for your project. You can find this in Rollbar, under Settings › Project Access Tokens › post_server_item
* **Log to Rollbar in development mode** : If no, only log to Rollbar when Magento deploy mode is `developer`. You can check the deploy mode using `php bin/magento deploy:mode:show`
* **Log Level** : Pick the log level threshold to log to Rollbar.
* **Include packages** : A list of Composer packages for which you want the version to be logged to Rollbar. Use the Composer name, like `onestepcheckout/iosc`. Each package name should be on its own line.
* **Exclude strings** : A list of (partial) strings that should **not** be logged, like all strings containing `Add of item`. Each string should be on its own line.

## Changelog

### [0.2.0] - 2018-11-12
#### Added
* Created working Joostvanveen_RollbarLogger.
* Exceptions are logged by using a custom plugin to replace Magento\Framework\App\Http\ExceptionCatcher.
* Anything logged to Magento is logged by overwriting Magento\Framework\Logger\Monolog.
* Includes settings in admin.
* Logs message, customer (if logged in), Magento version and versions for specific modules (set in config)

Thanks to the inspirational work of justbetter/magento2-sentry.
