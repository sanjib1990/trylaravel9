@extends('thinkific.page')

@section('content')
    <div class="col-lg-12 justify-content-center">
        <div class="row">
            <div class="col-lg-4"></div>
            <div class="col-lg-4">
                <h1>
                    Webhooks
                </h1>
            </div>
            <div class="col-lg-4"></div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-4">
                <a class="btn btn-dark text-white" href="{!! route("webhooks.register.view") !!}">Register event</a>
            </div>
            <div class="col-lg-4">
                <a class="btn btn-dark text-white" href="{!! route("webhooks.registered.view") !!}">View Registered Recieved hooks</a>
            </div>
            <div class="col-lg-4">
                <a class="btn btn-dark text-white" href="{!! route("webhooks.view") !!}">View Recieved hooks</a>
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="row">
            <h2>
                <strong>{!! old("message") !!}</strong>
            </h2>
        </div>
    </div>

    @yield("hook_content")
    </div>
@endsection
