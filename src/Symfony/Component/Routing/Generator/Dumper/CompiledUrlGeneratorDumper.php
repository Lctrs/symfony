<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Generator\Dumper;

use Symfony\Component\Routing\Exception\RouteCircularReferenceException;
use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper;

/**
 * CompiledUrlGeneratorDumper creates a PHP array to be used with CompiledUrlGenerator.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Tobias Schultze <http://tobion.de>
 * @author Nicolas Grekas <p@tchwork.com>
 */
class CompiledUrlGeneratorDumper extends GeneratorDumper
{
    public function getCompiledRoutes(): array
    {
        $compiledRoutes = [];
        foreach ($this->getRoutes()->all() as $name => $route) {
            $compiledRoute = $route->compile();

            $compiledRoutes[$name] = [
                $compiledRoute->getVariables(),
                $route->getDefaults(),
                $route->getRequirements(),
                $compiledRoute->getTokens(),
                $compiledRoute->getHostTokens(),
                $route->getSchemes(),
            ];
        }

        return $compiledRoutes;
    }

    public function getCompiledAliases(): array
    {
        $routes = $this->getRoutes();
        $compiledAliases = [];
        foreach ($routes->getAliases() as $name => $alias) {
            $rootDeprecation = $alias->isDeprecated() ? $alias->getDeprecation($name) : [];
            $visited = [];
            while ($routes->hasAlias($alias->getId())) {
                $currentId = $alias->getId();
                $searchKey = array_search($currentId, $visited);
                $visited[] = $currentId;

                if (false !== $searchKey) {
                    throw new RouteCircularReferenceException($currentId, \array_slice($visited, $searchKey));
                }

                $alias = $routes->getAlias($currentId);

                if ($alias->isDeprecated()) {
                    $deprecation = $alias->getDeprecation($currentId);

                    trigger_deprecation($deprecation['package'], $deprecation['version'], $deprecation['message']);
                }
            }

            $compiledAliases[$name] = [
                $alias->getId(),
                $rootDeprecation,
            ];
        }

        return $compiledAliases;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $options = [])
    {
        return <<<EOF
<?php

// This file has been auto-generated by the Symfony Routing Component.

return [
    'routes' => [{$this->generateDeclaredRoutes()}
    ],
    'aliases' => [{$this->generateDeclaredAliases()}
    ],
];

EOF;
    }

    /**
     * Generates PHP code representing an array of defined routes
     * together with the routes properties (e.g. requirements).
     */
    private function generateDeclaredRoutes(): string
    {
        $routes = '';
        foreach ($this->getCompiledRoutes() as $name => $properties) {
            $routes .= sprintf("\n        '%s' => %s,", $name, CompiledUrlMatcherDumper::export($properties));
        }

        return $routes;
    }

    private function generateDeclaredAliases(): string
    {
        $aliases = '';
        foreach ($this->getCompiledAliases() as $alias => $target) {
            $aliases .= sprintf("\n        '%s' => %s,", $alias, CompiledUrlMatcherDumper::export($target));
        }

        return $aliases;
    }
}
