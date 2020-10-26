<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Loader\Configurator;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Routing\Alias;

final class AliasConfigurator
{
    /**
     * @var Alias
     */
    private $alias;

    public function __construct(Alias $alias)
    {
        $this->alias = $alias;
    }

    /**
     * Whether this alias is deprecated, that means it should not be called anymore.
     *
     * @param string      $package The name of the composer package that is triggering the deprecation
     * @param string      $version The version of the package that introduced the deprecation
     * @param string|null $message The deprecation message to use, or null to use default
     *
     * @return $this
     *
     * @throws InvalidArgumentException when the message template is invalid
     */
    public function deprecate(string $package, string $version, string $message = null): self
    {
        $this->alias->setDeprecated($package, $version, $message);

        return $this;
    }
}
