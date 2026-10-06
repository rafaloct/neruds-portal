<?php

/**
 * @file
 * Environment-only settings for the isolated NERUDS development copy.
 *
 * NERUDS_PORTAL_ISOLATED_SETTINGS_V1
 *
 * This file never includes a production/local settings file and has no fallback
 * database, credential or production hostname. Missing inputs stop bootstrap.
 */

$nerudsRequired = static function (string $name): string {
  $value = getenv($name);
  if ($value === FALSE || trim($value) === '') {
    throw new \RuntimeException('Required development environment variable is missing: ' . $name);
  }
  return $value;
};

foreach ((getenv() ?: []) as $nerudsName => $nerudsValue) {
  if (is_string($nerudsValue) && stripos($nerudsValue, 'neruds.org') !== FALSE) {
    throw new \RuntimeException('Production destination is forbidden in environment variable: ' . $nerudsName);
  }
}

if ($nerudsRequired('NERUDS_ENVIRONMENT') !== 'development') {
  throw new \RuntimeException('Only the isolated development environment is accepted.');
}

$nerudsDbHost = $nerudsRequired('NERUDS_DB_HOST');
$nerudsDbPort = $nerudsRequired('NERUDS_DB_PORT');
if ($nerudsDbHost !== 'db' || $nerudsDbPort !== '3306') {
  throw new \RuntimeException('Database must be the isolated Compose service db on port 3306.');
}

$nerudsDbName = $nerudsRequired('NERUDS_DB_NAME');
$nerudsDbUser = $nerudsRequired('NERUDS_DB_USER');
foreach ([$nerudsDbName, $nerudsDbUser] as $nerudsIdentifier) {
  if (!preg_match('/^[A-Za-z0-9_]+$/D', $nerudsIdentifier)) {
    throw new \RuntimeException('Database identifiers must contain only letters, numbers and underscore.');
  }
}
if (strtolower($nerudsDbUser) === 'root') {
  throw new \RuntimeException('Drupal must use a dedicated non-root database account.');
}

$nerudsDbPassword = $nerudsRequired('NERUDS_DB_PASSWORD');
$nerudsHashSalt = $nerudsRequired('NERUDS_HASH_SALT');
if (strlen($nerudsHashSalt) < 32) {
  throw new \RuntimeException('NERUDS_HASH_SALT must be generated locally and contain at least 32 characters.');
}

$databases['default']['default'] = [
  'database' => $nerudsDbName,
  'username' => $nerudsDbUser,
  'password' => $nerudsDbPassword,
  'prefix' => '',
  'host' => 'db',
  'port' => '3306',
  'namespace' => 'Drupal\\mysql\\Driver\\Database\\mysql',
  'driver' => 'mysql',
  'autoload' => 'core/modules/mysql/src/Driver/Database/mysql/',
];

$settings['hash_salt'] = $nerudsHashSalt;
$settings['trusted_host_patterns'] = [
  '^localhost$',
  '^127\\.0\\.0\\.1$',
  '^neruds-portal\\.test$',
];
$settings['file_private_path'] = '/var/www/private';
$settings['file_temp_path'] = '/tmp';
$settings['config_sync_directory'] = dirname(__DIR__, 3) . '/config/sync';
$settings['update_free_access'] = FALSE;
$settings['rebuild_access'] = FALSE;
$settings['reverse_proxy'] = FALSE;

// Keep native cache contexts and access checks. Do not install a shared
// authenticated-response cache. Apache also applies no-store to Cookie requests
// and user/admin/session/JSON:API routes in this development recipe.
$config['system.performance']['cache']['page']['max_age'] = 0;

// Defaults are enforced at runtime rather than copied from an old database.
$config['system.mail']['interface']['default'] = 'test_mail_collector';
$config['automated_cron.settings']['interval'] = 0;
$config['jsonapi.settings']['read_only'] = TRUE;

// Compose denies external network access; PHP sendmail is disabled in the image.
// Per-module mail transports, custom jobs and external integrations still need
// review in the isolated copy before functional tests are allowed.
unset(
  $nerudsRequired,
  $nerudsName,
  $nerudsValue,
  $nerudsDbHost,
  $nerudsDbPort,
  $nerudsDbName,
  $nerudsDbUser,
  $nerudsIdentifier,
  $nerudsDbPassword,
  $nerudsHashSalt,
);
