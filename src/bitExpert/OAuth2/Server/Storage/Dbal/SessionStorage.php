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
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\SessionInterface;

class SessionStorage extends AbstractStorage implements SessionInterface
{
    /**
     * @var Connection
     */
    protected $db;

    /**
     * Creates a new {@link \bitExpert\\OAuth2\Server\Storage\Dbal\SessionStorage}.
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
    public function getByAccessToken(AccessTokenEntity $accessToken)
    {
        $query = $this->db->createQueryBuilder()
            ->select('s.id', 's.owner_type', 's.owner_id')
            ->from('oauth_sessions', 's')
            ->join('s', 'oauth_access_tokens', 'at', 's.id = at.session_id')
            ->where('at.access_token = :accessToken');
        $query->createNamedParameter($accessToken->getId(), \PDO::PARAM_STR, ':accessToken');

        $stmt = $query->execute();
        $result = $stmt->fetchAll();
        if (count($result) === 1) {
            $session = new SessionEntity($this->server);
            $session->setId($result[0]['id']);
            $session->setOwner($result[0]['owner_type'], $result[0]['owner_id']);

            return $session;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getByAuthCode(AuthCodeEntity $authCode)
    {
        $query = $this->db->createQueryBuilder()
            ->select('s.id', 's.owner_type', 's.owner_id')
            ->from('oauth_sessions', 's')
            ->join('s', 'oauth_auth_codes', 'ac', 's.id = ac.session_id')
            ->where('ac.auth_code = :authCode');
        $query->createNamedParameter($authCode->getId(), \PDO::PARAM_STR, ':authCode');

        $stmt = $query->execute();
        $result = $stmt->fetchAll();
        if (count($result) === 1) {
            $session = new SessionEntity($this->server);
            $session->setId($result[0]['id']);
            $session->setOwner($result[0]['owner_type'], $result[0]['owner_id']);

            return $session;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(SessionEntity $session)
    {
        $query = $this->db->createQueryBuilder()
            ->select('s.id', 's.description')
            ->from('oauth_scopes', 's')
            ->join('s', 'oauth_session_scopes', 'ss', 's.id = ss.scope')
            ->where('s.id = :sessionId');
        $query->createNamedParameter($session->getId(), \PDO::PARAM_STR, ':sessionId');
        $stmt = $query->execute();
        $result = $stmt->fetchAll();

        $scopes = [];
        foreach ($result as $scope) {
            $scopes[] = (new ScopeEntity($this->server))->hydrate(
                [
                    'id' => $scope['id'],
                    'description' => $scope['description'],
                ]
            );
        }

        return $scopes;
    }

    /**
     * {@inheritdoc}
     */
    public function create($ownerType, $ownerId, $clientId, $clientRedirectUri = null)
    {
        $query = $this->db->createQueryBuilder()
            ->insert('oauth_sessions')
            ->values(
                [
                    'owner_type' => ':ownerType',
                    'owner_id' => ':ownerId',
                    'client_id' => ':clientId',
                    'client_redirect_uri' => ':clientRedirectUri'
                ]
            );

        $query->createNamedParameter($ownerType, \PDO::PARAM_STR, ':ownerType');
        $query->createNamedParameter($ownerId, \PDO::PARAM_STR, ':ownerId');
        $query->createNamedParameter($clientId, \PDO::PARAM_STR, ':clientId');
        $query->createNamedParameter($clientRedirectUri, \PDO::PARAM_STR, ':clientRedirectUri');
        $query->execute();

        return $this->db->lastInsertId();
    }

    /**
     * {@inheritdoc}
     */
    public function associateScope(SessionEntity $session, ScopeEntity $scope)
    {
        $query = $this->db->createQueryBuilder()
            ->insert('oauth_session_scopes')
            ->values(['session_id' => ':token', 'scope' => ':scope']);

        $query->createNamedParameter($session->getId(), \PDO::PARAM_STR, ':token');
        $query->createNamedParameter($scope->getId(), \PDO::PARAM_STR, ':scope');
        $query->execute();
    }
}
