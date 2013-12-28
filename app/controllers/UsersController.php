<?php

use App\Service\Form\UsersForm;

class UsersController extends BaseController {

	protected $layout = 'layouts/master';

	protected $form;

	public function __construct(UsersForm $form)
	{
		$this->form = $form;
	}

	public function login($redirect = null)
	{
		$this->layout->content = View::make('users.login', compact('redirect'));
	}

	public function logout()
	{
		$this->form->logout();

		return Redirect::to('/');
	}

	public function authenticate()
	{
		if ($this->form->authenticate(Input::only('email', 'password')))
		{
			$redirect = Input::get('redirect', '/');

			return Redirect::intended();
		}

		return Redirect::to('login')->withErrors($this->form->errors())->withInput();
	}

}