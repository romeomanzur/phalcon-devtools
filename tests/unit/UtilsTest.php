<?php

/**
 * This file is part of the Phalcon Developer Tools.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\DevTools\Tests\Unit;

use Phalcon\DevTools\Utils;
use Phalcon\DevTools\Tests\Support\Module\UnitTest;
use Phalcon\Support\HelperFactory;

final class UtilsTest extends UnitTest
{
    /**
     * Tests Utils::camelize
     *
     * @test
     * @issue 1056
     */
    public function shouldCamelizeString(): void
    {
        $this->assertSame(
            'MyFooBar',
            Utils::camelize('MyFooBar')
        );

        $this->assertSame(
            'MyFooBar',
            Utils::camelize('MyFooBar', '_-')
        );

        $this->assertSame(
            'MyFoo_Bar',
            Utils::camelize('My-Foo_Bar', '-')
        );

        $this->assertSame(
            'MyFooBar',
            Utils::camelize('My-Foo_Bar', '_-')
        );
    }

    /**
     * Tests Utils::lowerCamelizeWithDelimiter
     *
     * @test
     * @issue 1070
     */
    public function shouldCamelizeStringWithDelimiter(): void
    {
        $this->assertSame(
            'myfoobar',
            Utils::lowerCamelizeWithDelimiter('myfoobar')
        );

        $this->assertSame(
            'Myfoobar',
            Utils::lowerCamelizeWithDelimiter('myfoobar', '_-')
        );

        $this->assertSame(
            'MyFooBar',
            Utils::lowerCamelizeWithDelimiter('My-Foo_Bar', '_-')
        );

        $this->assertSame(
            'MyFooBar',
            Utils::lowerCamelizeWithDelimiter('my-foo_bar', '_-')
        );

        $this->assertSame(
            'myFooBar',
            Utils::lowerCamelizeWithDelimiter(
                'my-foo_bar',
                '_-',
                true
            )
        );
    }

    /**
     * Tests Utils::lowerCamelize
     *
     * @test
     */
    public function shouldLowercamelizeString(): void
    {
        $this->assertSame(
            'myFooBar',
            Utils::lowerCamelize('MyFooBar')
        );
    }

    /**
     * Tests HelperFactory::uncamelize
     *
     * @test
     */
    public function shouldUncamelizeString(): void
    {
        $helper = new HelperFactory();

        $this->assertSame(
            'my_foo_bar',
            $helper->uncamelize('MyFooBar')
        );

        $this->assertSame(
            'my-foo-bar',
            $helper->uncamelize('MyFooBar', '-')
        );

        $this->assertSame(
            'my_foo_bar',
            $helper->uncamelize('MyFooBar', '_')
        );
    }
}
