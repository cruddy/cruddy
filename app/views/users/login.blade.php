@if ($errors->has('sentry'))
<p class="alert alert-warning">{{ $errors->first('sentry') }}</p>
@endif

{{ Form::open() }}
{{ Form::email('email', null, array(
    'required' => true,
    'placeholder' => 'E-mail',
)) }}

{{ Form::password('password', array(
    'required' => true,
    'placeholder' => 'Пароль',
)) }}

{{ Form::submit('Отправить') }}

{{ Form::close() }}