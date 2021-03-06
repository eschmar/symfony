<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\Controller;

use Psr\Container\ContainerInterface as Psr11ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Tests\Controller\ContainerControllerResolverTest;

class ControllerResolverTest extends ContainerControllerResolverTest
{
    public function testGetControllerOnContainerAware()
    {
        $resolver = $this->createControllerResolver();
        $request = Request::create('/');
        $request->attributes->set('_controller', 'Symfony\Bundle\FrameworkBundle\Tests\Controller\ContainerAwareController::testAction');

        $controller = $resolver->getController($request);

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerInterface', $controller[0]->getContainer());
        $this->assertSame('testAction', $controller[1]);
    }

    public function testGetControllerOnContainerAwareInvokable()
    {
        $resolver = $this->createControllerResolver();
        $request = Request::create('/');
        $request->attributes->set('_controller', 'Symfony\Bundle\FrameworkBundle\Tests\Controller\ContainerAwareController');

        $controller = $resolver->getController($request);

        $this->assertInstanceOf('Symfony\Bundle\FrameworkBundle\Tests\Controller\ContainerAwareController', $controller);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerInterface', $controller->getContainer());
    }

    public function testGetControllerWithBundleNotation()
    {
        $shortName = 'FooBundle:Default:test';
        $parser = $this->createMockParser();
        $parser->expects($this->once())
            ->method('parse')
            ->with($shortName)
            ->will($this->returnValue('Symfony\Bundle\FrameworkBundle\Tests\Controller\ContainerAwareController::testAction'))
        ;

        $resolver = $this->createControllerResolver(null, null, $parser);
        $request = Request::create('/');
        $request->attributes->set('_controller', $shortName);

        $controller = $resolver->getController($request);

        $this->assertInstanceOf('Symfony\Bundle\FrameworkBundle\Tests\Controller\ContainerAwareController', $controller[0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerInterface', $controller[0]->getContainer());
        $this->assertSame('testAction', $controller[1]);
    }

    protected function createControllerResolver(LoggerInterface $logger = null, Psr11ContainerInterface $container = null, ControllerNameParser $parser = null)
    {
        if (!$parser) {
            $parser = $this->createMockParser();
        }

        if (!$container) {
            $container = $this->createMockContainer();
        }

        return new ControllerResolver($container, $parser, $logger);
    }

    protected function createMockParser()
    {
        return $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser')->disableOriginalConstructor()->getMock();
    }

    protected function createMockContainer()
    {
        return $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')->getMock();
    }
}

class ContainerAwareController implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function testAction()
    {
    }

    public function __invoke()
    {
    }
}
