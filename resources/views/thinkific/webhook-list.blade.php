@extends('thinkific.webhook')

@section('hook_content')
    <div class="col-lg-12 justify-content-center">
        <div class="row">
            <div class="col-lg-3">
                <span class="justify-content-cente">ID</span>
            </div>
            <div class="col-lg-3">
                <span class="justify-content-cente">Topic</span>
            </div>
            <div class="col-lg-3">
                <span class="justify-content-cente">URL</span>
            </div>
            <div class="col-lg-3">
                <span class="justify-content-cente">Delete</span>
            </div>
        </div>
        <hr>
        @if($response->has('items'))
        @foreach($response->get('items') as $item)
            <div class="row">
                <div class="col-lg-3">
                    <span class="justify-content-cente">{!! $item['id'] !!}</span>
                </div>
                <div class="col-lg-3">
                    <span class="justify-content-cente">{!! $item['topic'] !!}</span>
                </div>
                <div class="col-lg-3">
                    <span class="justify-content-cente">{!! $item['target_url'] !!}</span>
                </div>
                <div class="col-lg-3">
                    <form method="post" action="{!! route("webhooks.delete") !!}">
                        @csrf
                        <input type="hidden" value="{!! $item['id'] !!}" name="id">
                        <button type="submit" class="btn btn-dark">Delete</button>
                    </form>
                </div>
            </div>
            <hr>
        @endforeach
        @else
            <div class="row justify-content-center">No webhooks registered</div>
        @endif
    </div>
@endsection
