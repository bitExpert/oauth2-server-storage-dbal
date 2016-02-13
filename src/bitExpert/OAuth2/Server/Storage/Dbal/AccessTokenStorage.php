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
use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\AccessTokenInterface;

class AccessTokenStorage extends AbstractStorage implements AccessTokenInterface
{
    /**
     * @var Connection
     */
    protected $db;

    /**
     * Creates a new {@link \bitExpert\\OAuth2\Server\Storage\Dbal\AccessTokenStorage}.
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
    public function get($token)
    {
        $query = $this->db->createQueryBuilder()
            ->select('t.access_token', 't.expire_time')
            ->from('oauth_access_tokens', 't')
            ->where('t.access_token = :token');
        $query->createNamedParameter($token, \PDO::PARAM_STR, ':token');
        $stmt = $query->execute();
        $result = $stmt->fetchAll();

        if (count($result) === 1) {
            $token = (new AccessTokenEntity($this->server))
                ->setId($result[0]['access_token'])
                ->setExpireTime($result[0]['expire_time']);

            return $token;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(AccessTokenEntity $token)
    {
        $query = $this->db->createQueryBuilder()
            ->select('s.id', 's.description')
            ->from('oauth_scopes', 's')
            ->leftJoin('s', 'oauth_access_token_scopes', 'ts', 's.id = ts.scope')
            ->where('ts.access_token = :token');
        $query->createNamedParameter($token->getId(), \PDO::PARAM_STR, ':token');
        $stmt = $query->execute();
        $result = $stmt->fetchAll();

        $response = [];
        if (count($result) > 0) {
            foreach ($result as $row) {
                $scope = (new ScopeEntity($this->server))->hydrate(
                    [
                        'id' => $row['id'],
                        'description' => $row['description'],
                    ]
                );
                $response[] = $scope;
            }
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function create($token, $expireTime, $sessionId)
    {
        $query = $this->db->createQueryBuilder()
            ->insert('oauth_access_tokens')
            ->values(['access_token' => ':token', 'expire_time' => ':expireTime', 'session_id' => ':sessionId']);

        $query->createNamedParameter($token, \PDO::PARAM_STR, ':token');
        $query->createNamedParameter($expireTime, \PDO::PARAM_STR, ':expireTime');
        $query->createNamedParameter($sessionId, \PDO::PARAM_STR, ':sessionId');
        $query->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function associateScope(AccessTokenEntity $token, ScopeEntity $scope)
    {
        $query = $this->db->createQueryBuilder()
            ->insert('oauth_access_token_scopes')
            ->values(['access_token' => ':token', 'scope' => ':scope']);

        $query->createNamedParameter($token->getId(), \PDO::PARAM_STR, ':token');
        $query->createNamedParameter($scope->getId(), \PDO::PARAM_STR, ':scope');
        $query->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AccessTokenEntity $token)
    {
        $query = $this->db->createQueryBuilder()
            ->delete('oauth_access_tokens')
            ->where('access_token = :token');
        $query->createNamedParameter($token->getId(), \PDO::PARAM_STR, ':token');
        $query->execute();
    }
}
