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
use League\OAuth2\Server\Entity\ClientEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\ClientInterface;

class ClientStorage extends AbstractStorage implements ClientInterface
{
    /**
     * @var Connection
     */
    protected $db;

    /**
     * Creates a new {@link \bitExpert\\OAuth2\Server\Storage\Dbal\ClientStorage}.
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
    public function get($clientId, $clientSecret = null, $redirectUri = null, $grantType = null)
    {
        $query = $this->db->createQueryBuilder()
            ->select('c.id', 'c.name')
            ->from('oauth_clients', 'c')
            ->where('c.id = :clientId');
        $query->createNamedParameter($clientId, \PDO::PARAM_STR, ':clientId');

        if ($clientSecret !== null) {
            $query->andWhere('c.secret = :clientSecret');
            $query->createNamedParameter($clientSecret, \PDO::PARAM_STR, ':clientSecret');
        }

        if ($redirectUri) {
            $query->leftJoin('c', 'oauth_client_redirect_uris', 'u', 'c.id = u.client_id');
            $query->andWhere('u.redirect_uri = :redirectUri');
            $query->createNamedParameter($redirectUri, \PDO::PARAM_STR, ':redirectUri');
        }

        $stmt = $query->execute();
        $result = $stmt->fetchAll();
        if (count($result) === 1) {
            $client = new ClientEntity($this->server);
            $client->hydrate(
                [
                    'id' => $result[0]['id'],
                    'name' => $result[0]['name'],
                ]
            );

            return $client;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBySession(SessionEntity $session)
    {
        $query = $this->db->createQueryBuilder()
            ->select('c.id', 'c.name')
            ->from('oauth_clients', 'c')
            ->leftJoin('c', 'oauth_sessions', 's', 'c.id = s.client_id')
            ->where('s.id = :sessionId');
        $query->createNamedParameter($session->getId(), \PDO::PARAM_STR, ':sessionId');

        $stmt = $query->execute();
        $result = $stmt->fetchAll();
        if (count($result) === 1) {
            $client = new ClientEntity($this->server);
            $client->hydrate(
                [
                    'id' => $result[0]['id'],
                    'name' => $result[0]['name'],
                ]
            );

            return $client;
        }
    }
}
