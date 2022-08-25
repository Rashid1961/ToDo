@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="panel panel-default">
                <div class="panel-heading">Авторизация</div>
                <div class="panel-body">
                    <form
                        class="form-horizontal"
                        role="form"
                        method="POST"
                        action="{{ url('/login') }}"
                    >
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <div class="col-md-12">
                                <input
                                    id="email"
                                    type="email"
                                    class="form-control"
                                    name="email"
                                    placeholder="Введите email"
                                    value="{{ old('email') }}"
                                >
                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <div class="col-md-12">
                                <input
                                    id="password"
                                    type="password"
                                    class="form-control"
                                    name="password"
                                    placeholder="Введите пароль"
                                    autocomplete="on"
                                >

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox text-center">
                                <label>
                                    <input type="checkbox" name="remember"> Запомнить меня
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row text-center">
                                <button type="submit" class="btn btn-primary text-center">
                                    <i class="fa fa-btn fa-sign-in"></i>
                                    Вход
                                </button>
                            </div>
                            <div class="row text-center">
                                <a
                                    class="btn btn-link text-center"
                                    href="{{ url('/password/reset') }}"
                                >
                                    Забыли пароль?
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
