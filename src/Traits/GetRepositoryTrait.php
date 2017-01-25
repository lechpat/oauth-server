<?php

namespace OAuthServer\Traits;

use Cake\Core\App;
use Cake\Core\Exception\Exception;

trait GetRepositoryTrait
{
    /**
     * Resolve a repository class name.
     *
     * @param string $class Partial class name to resolve.
     * @return string The resolved class name.
     * @throws \Cake\Core\Exception\Exception
     */
    protected function _resolveClassName($class)
    {
        $className = App::className($class, 'Model/Repository', 'Repository');
        if (!$className) {
            throw new Exception(sprintf('Repository class "%s" was not found.', $class));
        }
        
        return $className;
    }

    /**
     * Gets the instance of a repository class by name.
     *
     * @param string $name Repository name.
     * @return \League\OAuth2\Server\Repository\AbstractRepository
     * @throws \Cake\Core\Exception\Exception
     */
    protected function _getRepository($name)
    {
        $config = $this->config('repositories.' . $name);

        if (empty($config)) {
            throw new Exception(sprintf('Repository class "%s" has no configuration', $name));
        }

        $className = $this->_resolveClassName($config['className']);
        
        return new $className();
    }
}
