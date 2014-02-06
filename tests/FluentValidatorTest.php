<?php

use Kalnoy\Cruddy\Service\Validation\FluentValidator;
use Mockery as m;

class FluentValidatorTest extends PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        m::close();
    }

    public function testMerged()
    {
        $v = new FluentValidator(new StdClass);

        $rules = $v->mergeRules(['id' => 'required'], ['id' => 'exists', 'title' => 'required']);

        $this->assertCount(2, $rules);
        $this->assertEquals('required|exists', $rules['id']);
    }

    public function testProcessedRule()
    {
        $v = new FluentValidator(new StdClass);

        $rule = $v->processRule('exists:1,{id}', ['id' => 1]);

        $this->assertEquals('exists:1,1', $rule);
    }

    public function testResolvesRules()
    {
        $v = new FluentValidator(new StdClass);

        $v->rules(['first' => 'rule']);
        $v->update(['second' => 'rule']);

        $rules = $v->resolveRules('update');
        $this->assertCount(2, $rules);
    }

    public function testValidates()
    {
        $input = ['id' => 1, 'title' => ''];
        $rules = ['id' => 'required', 'title' => 'exists'];
        $messages = ['id.required' => 'message'];
        $attrs = ['id' => 'id'];
        $errors = ['error'];

        $messageBag = m::mock();
        $messageBag->shouldReceive('getMessages')->once()->andReturn($errors);

        $validator = Mockery::mock();
        $validator->shouldReceive('fails')->once()->andReturn(true);
        $validator->shouldReceive('errors')->once()->andReturn($messageBag);

        $factory = Mockery::mock('Illuminate\Validation\Factory');
        $factory->shouldReceive('make')->with($input, $rules, $messages, $attrs)->once()->andReturn($validator);

        $v = new FluentValidator($factory);
        $v->rules($rules)->messages($messages)->customAttributes($attrs);

        $this->assertEquals(false, $v->validate($input, 'update'));
        $this->assertEquals($errors, $v->errors());
    }
}