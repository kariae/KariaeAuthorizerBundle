<?php

/*
 * This file is part of the KariaeAuthorizerBundle package.
 *
 * (c) Zakariae Filali <filali.zakariae@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kariae\AuthorizerBundle\Security;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

/**
* Voter class that grant or not acess to a user for a method
*/
class AuthorizerVoter extends Voter
{
    private $redisPrefix = 'auth-';
    private $cacheDuration = 604800; // 7 days
    private $configuration;
    /** @var \Doctrine\ORM\EntityManager */
    private $em;
    private $userClass;

    public function __construct(array $configuration, EntityManager $em)
    {
        $this->em = $em;
        $this->userClass = $configuration['user_class'];
        $this->configuration = $configuration;
    }

    /**
     * Check if auth support both Controller and Action
     * @param $attribute Controller
     * @param $subject
     * @return boolean
     */
    protected function supports($attribute, $subject)
    {
        return true;
    }

    /**
     * Check Controller Action authorization
     * @param $attribute
     * @param $subject
     * @param TokenInterface $token
     * @return boolean
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (! $user instanceof $this->userClass) {
            return false;
        }

        /**
         * In case access controls are used in the security configuration file
         * the voters are asked to vote on the Request subject
         * this voter should not break the verification so it returns true
         */
        if ($subject instanceof Request) {
            return true;
        }

        return $this->isAuthorized($user, $subject, $attribute);
    }

    /**
     * Check if the action is autorized from cache if is set
     * else from database and set it in cache
     * @param $user
     * @param Controller $controller
     * @param string $action
     * @return boolean
     */
    private function isAuthorized($user, Controller $controller, string $action)
    {
        // Cache configuration from bundle config
        $cacheConfig = $this->configuration['cache'];

        if ($cacheConfig['enabled']) {
            $cache = $this->getCachePool($cacheConfig);
            $cacheKey = $this->generateCacheKey($user->getId(), $controller->getName(), $action);
            $auth = $this->getItemFromCache($cache, $cacheKey);
            if (! is_bool($auth)) {
                // Get authorization from database
                $vote = $this->em->getRepository('KariaeAuthorizerBundle:Authorizer')->isAuthorized($user->getRoles(), get_class($controller), $action);

                $auth->set($vote);
                $cache->save($auth);

                return $vote;
            }

            return $auth;
        } else {
            $vote = $this->em->getRepository('KariaeAuthorizerBundle:Authorizer')->isAuthorized($user->getRoles(), get_class($controller), $action);

            return $vote;
        }
    }

    /**
     * Get Cache pool
     * @param  array  $cacheConfig cache configuration from config file
     * @return CacheAdapter
     */
    private function getCachePool(array $cacheConfig)
    {
        // TODO implement other cache adapters
        $redisConnection = RedisAdapter::createConnection(
            'redis://' . $cacheConfig['redis']['host'] . ':' . $cacheConfig['redis']['port']
        );
        $cache = new RedisAdapter($redisConnection, $this->redisPrefix, $this->cacheDuration);

        return $cache;
    }

    /**
     * Generate cache key
     * @param int $userId
     * @param string $controller
     * @param string $action
     * @return string
     */
    private function generateCacheKey(int $userId, string $controller, string $action)
    {
        return $userId . '.' . $controller . '.' . $action;
    }

    /**
     * Get key from cache
     * @param RedisAdapter $cache
     * @param string $key
     */
    private function getItemFromCache(RedisAdapter $cache, string $key)
    {
        $item = $cache->getItem($key);

        if ($item->isHit()) {
            return $item->get();
        }

        return $item;
    }
}
