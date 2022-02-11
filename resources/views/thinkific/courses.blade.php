@extends('thinkific.page')

@section('content')
    <div class="col-lg-12 justify-content-center">
        <div class="row">
            <div class="col-lg-4"></div>
            <div class="col-lg-4">
                <h1>
                    Courses List
                </h1>
            </div>
            <div class="col-lg-4 @if(!empty(old('message'))) alert alert-danger alert-dismissible fade show
@endif">{!! old("message") !!}</div>
        </div>
    </div>

    <div class="col-lg-12 justify-content-center">
        <div class="row">
            <div class="col-lg-3">
                <span class="justify-content-cente">ID</span>
            </div>
            <div class="col-lg-3">
                <span class="justify-content-cente">Details</span>
            </div>
            <div class="col-lg-3">
                <span class="justify-content-cente">Enroll</span>
            </div>
        </div>
        <hr>
        @if($coursesList->has('items'))
            @foreach($coursesList->get('items') as $item)
                <div class="row">
                    <div class="col-lg-3">
                        <span class="justify-content-cente">{!! $item['id'] !!}</span>
                    </div>
                    <div class="col-lg-3">
                        <div class="col-md-12">
                            <div class="col-md-3">{!! $item['name'] !!}</div>
                            <div class="col-md-3">
                                <img width="100px" height="70px" src="{!! config()->get("thinkific.base_url") .
                                $item['course_card_image_url']!!}" alt="">
                            </div>
                            <div class="col-md-3">
                                {!! $item['description'] !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <form method="post" action="{!! route("enroll") !!}">
                            @csrf
                            <input type="text" required placeholder="First Name" name="first_name">
                            <input type="text" required placeholder="Last Name" name="last_name">
                            <input type="email" required placeholder="email" name="email">
                            <input type="number" required placeholder="amount in USD" name="amount">
                            <input type="hidden" value="{!! $item['id'] !!}" name="id">
                            <input type="hidden" value="{!! $item['product_id'] !!}" name="product_id">
                            <input type="hidden" value="{!! $item['name'] !!}" name="course_name">
                            <button type="submit" class="btn btn-dark">Enroll</button>
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
