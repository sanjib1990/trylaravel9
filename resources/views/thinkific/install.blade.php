@extends('layouts.base')

@section('body')
    <div class="col-lg-12 justify-content-center">
        <div class="row">
            <div class="col-lg-4"></div>
            <div class="col-lg-4">
                <h1>
                    Install page-test app
                </h1>
            </div>
            <div class="col-lg-4"></div>
        </div>
        <div class="row">
            <div class="col-lg-4">{!! session()->get('errors') !!}</div>
            <div class="col-lg-4">
                <form action="{!! route('startOauthFlow') !!}" method="post">
                    @csrf
                    <div class="form-row align-items-center">
                        <div class="col-auto">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="inlineFormInputGroup"
                                       placeholder="subdomain" name="subdomain" required value="{!! session()->get("subdomain") !!}">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <strong>
                                            .thinkific.com
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary mb-2"> Start Authorize</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-4"></div>
        </div>
    </div>
@endsection
