<?php

/*
 * This file is part of the OAuth2-Server-Storage-Dbal package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\OAuth2\Server\Storage\Dbal;

use Doctrine\DBAL\Connection;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\ScopeInterface;

class ScopeStorage extends AbstractStorage implements ScopeInterface
{
    /**
     * @var Connection
     */
    protected $db;

    /**
     * Creates a new {@link \bitExpert\\OAuth2\Server\Storage\Dbal\ScopeStorage}.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritdoc}
     */
    public function get($scope, $grantType = null, $clientId = null)
    {
        $query = $this->db->createQueryBuilder()
            ->select('s.id', 's.description')
            ->from('oauth_scopes', 's')
            ->where('s.id = :token');
        $query->createNamedParameter($scope, \PDO::PARAM_STR, ':token');

        if ($clientId) {
            $query->leftJoin('s', 'oauth_client_scopes', 'cs', 's.id = cs.scope');
            $query->andWhere('cs.client_id = :clientId');
            $query->createNamedParameter($clientId, \PDO::PARAM_STR, ':clientId');
        }

        $stmt = $query->execute();
        $result = $stmt->fetchAll();
        if (count($result) === 1) {
            return (new ScopeEntity($this->server))->hydrate(
                [
                    'id' => $result[0]['id'],
                    'description' => $result[0]['description'],
                ]
            );
        }
    }
}
