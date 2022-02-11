@extends('layouts.base')

@section("styles")
    <style>
        body {
            overflow-x: hidden;
        }

        #wrapper {
            padding-left: 0;
            -webkit-transition: all 0.5s ease;
            -moz-transition: all 0.5s ease;
            -o-transition: all 0.5s ease;
            transition: all 0.5s ease;
        }

        #wrapper.toggled {
            padding-left: 250px;
        }

        #sidebar-wrapper {
            z-index: 1000;
            position: fixed;
            left: 250px;
            width: 0;
            height: 100%;
            margin-left: -250px;
            overflow-y: auto;
            background: #000;
            -webkit-transition: all 0.5s ease;
            -moz-transition: all 0.5s ease;
            -o-transition: all 0.5s ease;
            transition: all 0.5s ease;
        }

        #wrapper.toggled #sidebar-wrapper {
            width: 250px;
        }

        #page-content-wrapper {
            width: 100%;
            position: absolute;
            padding: 15px;
        }

        #wrapper.toggled #page-content-wrapper {
            position: absolute;
            margin-right: -250px;
        }


        /* Sidebar Styles */

        .sidebar-nav {
            position: absolute;
            top: 0;
            width: 250px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .sidebar-nav li {
            text-indent: 20px;
            line-height: 40px;
        }

        .sidebar-nav li a {
            display: block;
            text-decoration: none;
            color: #999999;
        }

        .sidebar-nav li a:hover {
            text-decoration: none;
            color: #fff;
            background: rgba(255, 255, 255, 0.2);
        }

        .sidebar-nav li a:active, .sidebar-nav li a:focus {
            text-decoration: none;
        }

        .sidebar-nav>.sidebar-brand {
            height: 65px;
            font-size: 18px;
            line-height: 60px;
        }

        .sidebar-nav>.sidebar-brand a {
            color: #999999;
        }

        .sidebar-nav>.sidebar-brand a:hover {
            color: #fff;
            background: none;
        }

        @media(min-width:768px) {
            #wrapper {
                padding-left: 0;
            }
            #wrapper.toggled {
                padding-left: 250px;
            }
            #sidebar-wrapper {
                width: 0;
            }
            #wrapper.toggled #sidebar-wrapper {
                width: 250px;
            }
            #page-content-wrapper {
                padding: 20px;
                position: relative;
            }
            #wrapper.toggled #page-content-wrapper {
                position: relative;
                margin-right: 0;
            }
        }
    </style>
@endsection

@section('body')
    <div id="wrapper" class="toggled">

        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <ul class="sidebar-nav">
                <li><a href="{!! route('authourizedPage') !!}">Home</a></li>
                <li><a href="{!! route("courses") !!}">Courses</a></li>
                <li><a href="{!! route("order") !!}">Order</a></li>
                <li><a href="{!! route("support") !!}">Support</a></li>
                <li><a href="{!! route("webhooks.view") !!}">Webhook</a></li>
            </ul>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <div class="container-fluid">
                @yield('content')
                @sectionMissing("content")
                    <div class="col-lg-12 justify-content-center">
                        <div class="row m-md-5">
                            <div class="col-lg-2"></div>
                            <div class="col-lg-6">
                                <h1>
                                    Authenticated Page
                                </h1>
                            </div>
                            <div class="col-lg-4"> SUBDOMAIN: {{ session()->get("subdomain") }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <!-- /#page-content-wrapper -->

    </div>
    <!-- /#wrapper -->
@endsection

@section("scripts")
@endsection
