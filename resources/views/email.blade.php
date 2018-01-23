<!doctype html>

<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <title>Kora 3 - Email</title>

    <link href="https://fonts.googleapis.com/css?family=Ubuntu:400,400i,500,500i,700,700i" rel="stylesheet">
    <style>
        .email-body {
            background-color: #FFFFFF;
            font-family: 'Ubuntu', sans-serif;
            margin: 30px 50px 0 50px;
        }

        /* BEGIN GLOBALS */

        .bold-highlight {
            /*font-family: 'Ubuntu-Bold', sans-serif;*/
            color: #17A9A0;
        }

        div:empty {
            display: none;
            padding: 0;
        }

        .max-width-large {
            max-width: 500px;
        }

        .max-width-regular {
            max-width: 400px;
        }

        .top-list-item {
            padding-top: 10px;
        }

        /* END GLOBALS */

        .email {
            width: 100%;
            text-align: center;
        }

        .email > .header {
            background-color: #007C83;
            border-radius: 5px;
            -webkit-box-shadow: 0 3px 5px 0 rgba(21,39,48,0.05);
            box-shadow: 0 3px 5px 0 rgba(21,39,48,0.05);
            height: 102px;
            margin: auto;
            width: 100%;
        }

        .email > .header > img {
            height: 58px;
            padding-top: 22px;
            width: 134px;
        }

        .email > .content {
            margin: auto;
        }

        .email > .content > .main-text {
            font-size: 24px;
            line-height: 34px;
            padding-top: 22px;
            text-shadow: 0 2px 4px 0 rgba(0,0,0,0.1);
        }

        .email > .content > .project-text {
            font-size: 24px;
            line-height: 28px;
            padding-top: 22px;
            text-shadow: 0 2px 4px 0 rgba(0,0,0,0.1);
        }

        .email > .content > .sub-text {
            font-size: 14px;
            line-height: 24px;
            padding-top: 34px;
        }

        .email > .content > .action-btn {
            padding-top: 34px;
        }

        .email > .content > .action-btn > a > button {
            -webkit-transition: all .2s ease-in-out;
            transition: all .2s ease-in-out;
            background: -webkit-gradient(linear, left top, right top, from(#00c7b8), to(#04b6af));
            background: linear-gradient(to right, #00c7b8 0%, #04b6af 100%);
            border: 0;
            border-radius: 100px;
            -webkit-box-shadow: 0 3px 5px 0 rgba(4,182,175,0.1);
            box-shadow: 0 3px 5px 0 rgba(4,182,175,0.1);
            color: #fff;
            cursor: pointer;
            font-family: 'Ubuntu', sans-serif;
            font-size: 16px;
            height: 50px;
            line-height: 18px;
            opacity: .9;
            text-align: center;
            width: 100%;
        }

        .email > .content > .action-btn > a > button:hover {
            -webkit-box-shadow: 0 5px 10px 0 rgba(4,182,175,0.2);
            box-shadow: 0 5px 10px 0 rgba(4,182,175,0.2);
            opacity: 1;
        }

        .email > .content > .action-btn > a > button:focus {
            outline: none;
        }

        .email > .content > .post-action-text {
            font-size: 14px;
            line-height: 24px;
            padding-top: 57px;
        }

        .email > .content > .line {
            border: 2px solid rgba(21,39,48,0.05);
            box-sizing: border-box;
            height: 3px;
            margin-top: 51px;
            width: 100%;
        }

        .email > .content > .pre-footer-text {
            font-size: 14px;
            line-height: 24px;
            padding-top: 39px;
        }

        .email > .content > .footer-text {
            font-family: 'Ubuntu-Bold', sans-serif;
            font-size: 14px;
            line-height: 24px;
            padding-top: 39px;
        }

        .email > .content > .footer-email {
            font-size: 12px;
            line-height: 20px;
            padding-top: 5px;
        }
    </style>
</head>
<body class="email-body">

<div class="email">
    <div class="header max-width-large">
        <img src="{{ config('app.url') }}logos/k3Dummy.jpg" alt="Logo" title="Logo">
    </div>

    <div class="content max-width-regular">
        <div class="main-text">@yield('main-text')</div>

        <div class="project-text bold-highlight">@yield('project-text')</div>

        <div class="sub-text">@yield('sub-text')</div>

        <div class="action-btn">
            <a href="@yield('button-link')">
                <button type="button">@yield('button-text')</button>
            </a>
        </div>

        <div class="post-action-text">@yield('post-action-text')</div>

        <div class="line"> </div>

        <div class="pre-footer-text">@yield('pre-footer-text')</div>

        <div class="footer-text">@yield('footer-text')</div>

        <div class="footer-email">
            {{--(username, <a class="bold-highlight" href="mailto:useremail@msu.edu">useremail@example.com</a>)--}}
            @yield('footer-email')
        </div>
    </div>
</div>

</body>
</html>
