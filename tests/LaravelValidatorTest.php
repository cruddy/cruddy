<?php

use Kalnoy\Cruddy\Service\Validation\LaravelValidator;
use Mockery as m;

class LaravelValidatorTest extends TestCase {

    public function tearDown()
    {
        m::close();
    }

    public function testMerged()
    {
        $v = new LaravelValidator(null);

        $rules = $v->mergeRules(['id' => 'required'], ['id' => 'exists', 'title' => 'required']);

        $this->assertCount(2, $rules);
        $this->assertEquals('required|exists', $rules['id']);
    }

    public function testProcessedRule()
    {
        $v = new LaravelValidator(null);

        $rule = $v->processRule('exists:1,{id}', ['id' => 1]);

        $this->assertEquals('exists:1,1', $rule);
    }

    public function testResolvesRules()
    {
        $v = new LaravelValidator(null);

        $v->rules(['first' => 'rule']);
        $v->update(['second' => 'rule']);

        $rules = $v->resolveRules('update');
        $this->assertCount(2, $rules);
    }

    /**
     * @expectedException Kalnoy\Cruddy\Service\Validation\ValidationException
     */
    public function testValidates()
    {
        $input = ['id' => 1, 'title' => ''];
        $rules = ['id' => 'required', 'title' => 'exists'];
        $messages = ['id.required' => 'message'];
        $attrs = ['id' => 'id'];

        $messageBag = m::mock();
        $messageBag->shouldReceive('all')->once()->andReturn([]);

        $validator = Mockery::mock();
        $validator->shouldReceive('passes')->once()->andReturn(false);
        $validator->shouldReceive('errors')->once()->andReturn($messageBag);

        $factory = Mockery::mock('Illuminate\Validation\Factory');
        $factory->shouldReceive('make')->with($input, $rules, $messages, $attrs)->once()->andReturn($validator);

        $v = new LaravelValidator($factory);
        $v->rules($rules)->messages($messages)->customAttributes($attrs);

        $v->validate($input, 'update');
    }
}