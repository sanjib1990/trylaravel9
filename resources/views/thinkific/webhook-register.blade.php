@extends('thinkific.webhook')

@section('hook_content')
    <div class="col-lg-12 justify-content-center">
        <div class="row">
            <div class="col-lg-4"></div>
            <div class="col-lg-4">
                <form action="{!! route('webhooks.register') !!}" method="post">
                    @csrf
                    <div class="form-row align-items-center">
                        <div class="col-auto">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="inlineFormInputGroup"
                                       placeholder="event name" name="event" required
                                       value="{!! old("event") !!}">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary mb-2">Register</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-4"></div>
        </div>
    </div>
@endsection
