<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Confirm Email</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            font-family: 'Open Sans', sans-serif;
        }

        .email-container {
            max-width: 500px;
            margin: 0 auto;
            border: 1px solid #ebebeb;
            border-radius: 3px;
            background-color: #ffffff;
            padding: 40px;
        }

        .logo {
            width: 72px;
            height: 72px;
            margin: 0 auto 10px;
        }

        .title {
            font-family: 'Montserrat', sans-serif;
            font-size: 24px;
            font-weight: bold;
            color: #141414;
            text-align: left;
            margin: 0;
            line-height: 40px;
            margin: 0 auto 10px;
        }

        .message {
            font-size: 15px;
            line-height: 25px;
            color: #141414;
            margin: 20px 0;
            white-space: pre-wrap;
        }

        .button {
            display: inline-block;
            background-color: #0666eb;
            color: #ffffff;
            font-family: 'Sofia Sans', sans-serif;
            font-weight: bold;
            font-size: 16px;
            line-height: 34px;
            padding: 0 24px;
            border-radius: 40px;
            text-decoration: none;
            text-align: center;
            margin: 20px 0;
        }

        .footer {
            border-top: 1px solid #dfe1e4;
            padding-top: 24px;
            font-size: 14px;
            color: #b4becc;
            text-align: center;
        }

        .footer strong {
            color: #222222;
        }

        .footer-note,
        .credits {
            font-size: 14px;
            color: #424040;
            margin-bottom: 30px;
        }

        .credits {
            font-size: 12px;
            background-color: #f2eff3;
            padding: 20px;
            border-radius: 8px;
            color: #84828e;
            margin-bottom: 3px
        }

        .header-div {
            display: flex;
            justify-content: center;
            align-items: center;
            border-bottom: 1px solid #eff1f4;
            padding-bottom: 5px
        }

        .header-div2 {
            display: flex;
            justify-content: center;
            align-items: center;
            padding-bottom: 5px
        }
    </style>
</head>

<body>
    <div class="email-container" style="margin-top: 60px">
        {{-- <div class="header-div2">
            <img src="https://wvmciloilo.com/assets/img/wvmc/WVMClogo2.png" alt="Logo" class="logo" />
        </div> --}}
        <div class="header-div">
            <p class="title">Tibiao MDRRMO Portal</p>
        </div>
        <p class="message">{!! $content ?? '' !!}</p>
        <div class="credits">
            This is an automated message from Tibiao MDRRMO Portal. Please do not reply directly to this
            email.
        </div>
    </div>
</body>

</html>
