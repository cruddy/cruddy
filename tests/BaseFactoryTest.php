<?php

use Mockery as m;

class BaseFactoryTest extends PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        m::close();
    }

    public function testCallbackMacroResolved()
    {
        $factory = new Kalnoy\Cruddy\Schema\BaseFactory;

        $factory->register('method', function ($entity, $collection, $id)
        {
            return func_get_args();
        });

        $entity = m::mock('Kalnoy\Cruddy\Entity');
        $collection = m::mock('Kalnoy\Cruddy\Schema\BaseCollection');

        $result = $factory->resolve('method', $entity, $collection, ['id']);

        $this->assertEquals($result, [$entity, $collection, 'id']);
    }
}