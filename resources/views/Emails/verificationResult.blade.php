@extends('Layouts.authentication')
@section('title', 'Giriş Yap')


@section('content')
    <div class="pattern">
        <span class="red"></span>
        <span class="indigo"></span>
        <span class="blue"></span>
        <span class="green"></span>
        <span class="orange"></span>
    </div>

    <div class="auth-main particles_js">
        <div class="auth_div vivify popIn">
            <div class="auth_brand">
                <a class="navbar-brand" href="javascript:void(0);"><img src="{{asset('assets/images/icon.svg')}}" width="30" height="30" class="d-inline-block align-top mr-2" alt="">backlinksatisi.com</a>
            </div>
            <div class="card">
                <div class="body">
                    <form class="form-auth-small m-t-20">
                        <div class="bottom">
                            <span>{{$message}}</span>
                        </div>
                        <div class="margin-top-30">
                            <a href="{{route('login')}}" class="btn btn-round btn-primary btn-block"><i class="fa fa-home"></i> <span>Giriş Sayfası</span></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="particles-js"></div>
    </div>
@stop

@section('page-styles')

@stop

@section('page-script')

@stop
