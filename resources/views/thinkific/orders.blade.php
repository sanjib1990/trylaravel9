@extends('thinkific.page')

@section('content')
    <div class="col-lg-12 justify-content-center">
        <div class="row">
            <div class="col-lg-4"></div>
            <div class="col-lg-4">
                <h1>
                    Orders
                </h1>
            </div>
            <div class="col-lg-4 @if(!empty(old('message'))) alert alert-danger alert-dismissible fade show
@endif">{!! old("message") !!}</div>
        </div>
    </div>
    <hr>
    <div class="col-lg-12 justify-content-center">
        <div class="row">
            <div class="col-lg-1">
                <span class="justify-content-cente">ID</span>
            </div>
            <div class="col-lg-5 justify-content-cente text-center">
                <span class="justify-content-cente">Thinkific Order Id</span> |
                <span class="justify-content-cente">Course Name</span> |
                <span class="justify-content-cente">Amount</span>
            </div>
            <div class="col-lg-2 justify-content-cente text-center">
                <span class="justify-content-cente">Status</span>
            </div>
            <div class="col-lg-2 justify-content-cente text-center">
                <span class="justify-content-cente">Student Email</span>
            </div>
            <div class="col-lg-2">
                <span class="justify-content-cente">Action</span>
            </div>
        </div>
    </div>

    @if(! empty($orders))
        @foreach($orders as $order)
            <div class="col-lg-12 justify-content-center">
                <div class="row">
                    <div class="col-lg-1">
                        <span class="justify-content-cente">{!! $order['id'] !!}</span>
                    </div>
                    <div class="col-lg-5 justify-content-cente text-center">
                        <span class="justify-content-cente">{!! $order['external_order_id'] !!}</span> |
                        <span class="justify-content-cente">{!! $order['course_name'] !!}</span> |
                        <span class="justify-content-cente">{!! $order['amount'] !!} {!! $order['currency'] !!}</span>
                    </div>
                    <div class="col-lg-2 justify-content-cente text-center">
                        <span class="justify-content-cente">{!! $order['status'] !!}</span>
                    </div>
                    <div class="col-lg-2 justify-content-cente text-center">
                        <span class="justify-content-cente">{!! $order['student_email'] !!}</span>
                    </div>
                    <div class="col-lg-2 justify-content-cente">
                        <form method="post" action="{!! route("refund") !!}">
                            @csrf
                            <input type="hidden" value="{!! $order['id'] !!}" name="id">
                            <button type="submit" class="btn btn-dark">Refund</button>
                        </form>
                    </div>
                </div>
            </div>
            <hr>
        @endforeach
    @else
        <div class="col-lg-12 justify-content-center">
            <div class="row justify-content-cente text-center">
                <div class="col-lg-5"></div>
                <div class="col-lg-2 justify-content-cente text-center">
                    <h1>
                        No Orders
                    </h1>
                </div>
                <div class="col-lg-5"></div>
            </div>
        </div>
    @endif
@endsection
