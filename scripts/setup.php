<?php

/*
 * This file is part of the OAuth2-Server-Storage-Dbal package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Doctrine\DBAL\Schema\Schema;

if (!isset($connection) or (!$connection instanceof \Doctrine\DBAL\Connection)) {
    die('Define $connection variable before including this script!');
}

$schema = new Schema();
$scopesTable = $schema->createTable('oauth_scopes');
$scopesTable->addColumn('id', 'string');
$scopesTable->addColumn('description', 'string', array('Notnull' => true));
$scopesTable->setPrimaryKey(array('id'));

$clientTable = $schema->createTable('oauth_clients');
$clientTable->addColumn('id', 'string');
$clientTable->addColumn('secret', 'string', array('Notnull' => true));
$clientTable->addColumn('name', 'string');
$clientTable->setPrimaryKey(array('id'));

$clientRedirectUrisTable = $schema->createTable('oauth_client_redirect_uris');
$clientRedirectUrisTable->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
$clientRedirectUrisTable->addColumn('client_id', 'string', array('Notnull' => true));
$clientRedirectUrisTable->addColumn('redirect_uri', 'text', array('Notnull' => true));
$clientRedirectUrisTable->setPrimaryKey(array('id'));
$clientRedirectUrisTable->addForeignKeyConstraint(
    $clientTable,
    ['client_id'],
    ['id'],
    ['onDelete' => 'CASCADE']
);

$clientScopesTable = $schema->createTable('oauth_client_scopes');
$clientScopesTable->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
$clientScopesTable->addColumn('client_id', 'string', array('Notnull' => true));
$clientScopesTable->addColumn('scope', 'string', array('Notnull' => true));
$clientScopesTable->setPrimaryKey(array('id'));
$clientScopesTable->addUniqueIndex(array('client_id', 'scope'));
$clientScopesTable->addForeignKeyConstraint(
    $clientTable,
    ['client_id'],
    ['id'],
    ['onDelete' => 'CASCADE']
);
$clientScopesTable->addForeignKeyConstraint(
    $scopesTable,
    ['scope'],
    ['id'],
    ['onDelete' => 'CASCADE']
);

$sessionsTable = $schema->createTable('oauth_sessions');
$sessionsTable->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
$sessionsTable->addColumn('client_id', 'string', array('Notnull' => true));
$sessionsTable->addColumn('owner_type', 'string', array('Notnull' => true));
$sessionsTable->addColumn('owner_id', 'string', array('Notnull' => true));
$sessionsTable->addColumn('client_redirect_uri', 'text', array('Notnull' => false));
$sessionsTable->setPrimaryKey(array('id'));
$sessionsTable->addForeignKeyConstraint(
    $clientTable,
    ['client_id'],
    ['id'],
    ['onDelete' => 'CASCADE']
);

$accessTokensTable = $schema->createTable('oauth_access_tokens');
$accessTokensTable->addColumn('access_token', 'string', array('Notnull' => true));
$accessTokensTable->addColumn('session_id', 'integer', array('unsigned' => true));
$accessTokensTable->addColumn('expire_time', 'integer', array('unsigned' => true));
$accessTokensTable->setPrimaryKey(array('access_token'));
$accessTokensTable->addForeignKeyConstraint(
    $sessionsTable,
    ['session_id'],
    ['id'],
    ['onDelete' => 'CASCADE']
);

$authCodesTable = $schema->createTable('oauth_auth_codes');
$authCodesTable->addColumn('auth_code', 'string', array('Notnull' => true));
$authCodesTable->addColumn('session_id', 'integer', array('unsigned' => true));
$authCodesTable->addColumn('expire_time', 'integer', array('unsigned' => true));
$authCodesTable->addColumn('client_redirect_uri', 'text', array('Notnull' => false));
$authCodesTable->setPrimaryKey(array('auth_code'));
$authCodesTable->addForeignKeyConstraint(
    $sessionsTable,
    ['session_id'],
    ['id'],
    ['onDelete' => 'CASCADE']
);

$sessionScopesTable = $schema->createTable('oauth_session_scopes');
$sessionScopesTable->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
$sessionScopesTable->addColumn('session_id', 'integer', array('unsigned' => true));
$sessionScopesTable->addColumn('scope', 'string', array('Notnull' => true));
$sessionScopesTable->setPrimaryKey(array('id'));
$sessionScopesTable->addUniqueIndex(array('session_id', 'scope'));
$sessionScopesTable->addForeignKeyConstraint(
    $sessionsTable,
    ['session_id'],
    ['id'],
    ['onDelete' => 'CASCADE']
);
$sessionScopesTable->addForeignKeyConstraint(
    $scopesTable,
    ['scope'],
    ['id'],
    ['onDelete' => 'CASCADE']
);

$accessTokenScopesTable = $schema->createTable('oauth_access_token_scopes');
$accessTokenScopesTable->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
$accessTokenScopesTable->addColumn('access_token', 'string', array('Notnull' => true));
$accessTokenScopesTable->addColumn('scope', 'string', array('Notnull' => true));
$accessTokenScopesTable->setPrimaryKey(array('id'));
$accessTokenScopesTable->addUniqueIndex(array('access_token', 'scope'));
$accessTokenScopesTable->addForeignKeyConstraint(
    $accessTokensTable,
    ['access_token'],
    ['access_token'],
    ['onDelete' => 'CASCADE']
);
$accessTokenScopesTable->addForeignKeyConstraint(
    $scopesTable,
    ['scope'],
    ['id'],
    ['onDelete' => 'CASCADE']
);

$authCodeScopesTable = $schema->createTable('oauth_auth_code_scopes');
$authCodeScopesTable->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
$authCodeScopesTable->addColumn('auth_code', 'string', array('Notnull' => true));
$authCodeScopesTable->addColumn('scope', 'string', array('Notnull' => true));
$authCodeScopesTable->setPrimaryKey(array('id'));
$authCodeScopesTable->addUniqueIndex(array('auth_code', 'scope'));
$authCodeScopesTable->addForeignKeyConstraint(
    $authCodesTable,
    ['auth_code'],
    ['auth_code'],
    ['onDelete' => 'CASCADE']
);
$authCodeScopesTable->addForeignKeyConstraint(
    $scopesTable,
    ['scope'],
    ['id'],
    ['onDelete' => 'CASCADE']
);

$refreshTokensTable = $schema->createTable('oauth_refresh_tokens');
$refreshTokensTable->addColumn('refresh_token', 'string');
$refreshTokensTable->addColumn('access_token', 'string', array('Notnull' => true));
$refreshTokensTable->addColumn('expire_time', 'integer', array('unsigned' => true));
$refreshTokensTable->setPrimaryKey(array('refresh_token'));
$refreshTokensTable->addForeignKeyConstraint(
    $accessTokensTable,
    ['access_token'],
    ['access_token'],
    ['onDelete' => 'CASCADE']
);

// Create the database tables as defined above
$queries = $schema->toSql($connection->getDatabasePlatform());
foreach ($queries as $query) {
    $connection->exec($query);
}
