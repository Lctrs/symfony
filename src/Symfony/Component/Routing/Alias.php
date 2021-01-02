<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing;

use Symfony\Component\Routing\Exception\InvalidArgumentException;

final class Alias
{
    private $name;
    private $target;
    private $deprecation = [];

    public function __construct(string $name, string $target)
    {
        $this->name = $name;
        $this->target = $target;
    }

    public function with(string $name, string $target): self
    {
        $new = clone $this;

        $new->name = $name;
        $new->target = $target;

        return $new;
    }

    /**
     * Returns the target name of this alias.
     *
     * @return string The target name
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * Whether this alias is deprecated, that means it should not be referenced
     * anymore.
     *
     * @param string $package The name of the composer package that is triggering the deprecation
     * @param string $version The version of the package that introduced the deprecation
     * @param string $message The deprecation message to use
     *
     * @return $this
     *
     * @throws InvalidArgumentException when the message template is invalid
     */
    public function setDeprecated(string $package, string $version, string $message): self
    {
        if ('' !== $message) {
            if (preg_match('#[\r\n]|\*/#', $message)) {
                throw new InvalidArgumentException('Invalid characters found in deprecation template.');
            }

            if (false === strpos($message, '%alias%')) {
                throw new InvalidArgumentException('The deprecation template must contain the "%alias%" placeholder.');
            }
        }

        $this->deprecation = [
            'package' => $package,
            'version' => $version,
            'message' => $message ?: 'The "%alias%" route alias is deprecated. You should stop using it, as it will be removed in the future.',
        ];

        return $this;
    }

    public function isDeprecated(): bool
    {
        return [] !== $this->deprecation;
    }

    /**
     * @return array{package: string, version: string, message: string}
     */
    public function getDeprecation(): array
    {
        return [
            'package' => $this->deprecation['package'],
            'version' => $this->deprecation['version'],
            'message' => str_replace('%alias%', $this->name, $this->deprecation['message']),
        ];
    }
}
