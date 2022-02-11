@extends('thinkific.webhook')

@section('hook_content')
    <div class="col-lg-12 justify-content-center">
        <div class="row">
            <div class="col-lg-2">
                <span class="justify-content-cente">ID</span>
            </div>
            <div class="col-lg-2">
                <span class="justify-content-cente">Created on</span>
            </div>
            <div class="col-lg-2">
                <span class="justify-content-cente">Subdomain</span>
            </div>
            <div class="col-lg-3">
                <span class="justify-content-cente">Event</span>
            </div>
            <div class="col-lg-3">
                <span class="justify-content-cente">Data</span>
            </div>
        </div>
        <hr>
        @foreach($webhooks as $webhook)
            <div class="col-lg-12 justify-content-center">
                <div class="row">
                    <div class="col-lg-2 overflow-auto">
                        <span>{!! $webhook->hook_id !!}</span>
                    </div>
                    <div class="col-lg-2 overflow-auto">
                        <span>{!! $webhook->created_at->diffForHumans() !!}</span>
                    </div>
                    <div class="col-lg-2 overflow-auto">
                        <span class="bg-info">{!! $webhook->subdomain !!}</span>
                    </div>
                    <div class="col-lg-3 overflow-auto">
                        <span class="bg-info">{!! $webhook->resource !!}.{!! $webhook->action !!}</span>
                    </div>
                    <div class="col-lg-3 overflow-auto">
                        <span>{!! $webhook->webhook_data !!}</span>
                    </div>
                    <div class="col-lg-3"></div>
                </div>
            </div>
            <hr>
    @endforeach
@endsection
