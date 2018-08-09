<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>iXavier.com</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            div.message {
                background-color: #007700;
                color: white;
                border: 5px solid white;
                padding: 10px;
                max-width: 900px;
                overflow-wrap: break-word;
            }

            div.error {
                background-color: #CC0000;
                color: white;
                border: 5px solid white;
                padding: 10px;
            }

            form {
                padding: 10px;
                clear: both;
            }

            form div.whitespace {
                clear: both;
                height: 1px;
            }

            form div.row {
                padding: 10px;
                clear: both;
            }

            form div.row div.column {
                padding: 5px 7px;
            }

            form div.label {
                font-weight: bold;
                text-align: right;
                width: 300px;
                float: left;
                margin: 3px;
            }

            form div.value {
                float: left;
                width: 300px;
            }

            form div.value input.text-field {
                width: 100%;
                font-size: 14pt;
            }

            form div.value input.button {
                width: 70%;
                font-size: 14pt;
                background-color: #133d55;
                color: white;
            }

        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    <a href="{{ url('/') }}">Home</a>
                    <a href="{{ url('/login') }}">Login</a>
                </div>
            @endif

            <div class="content">

                @if(Request::session()->has('message'))
                    <div class="message">
                        <p>{{ Request::session()->get('message') }}</p>
                    </div>
                @endif

                @if(Request::session()->has('error'))
                    <div class="error">
                        <p>Error while processing your request.</p>
                        <p>{{ Request::session()->get('error') }}</p>
                    </div>
                @endif

