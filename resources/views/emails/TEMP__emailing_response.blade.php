
<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Serenity Garden Rooms</title>

    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('font-awesome/css/font-awesome.css') }}" rel="stylesheet">

    <link href="{{ asset('css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <style>
        .loginscreen.middle-box {
            width: 575px;
        }
        .msg {
            color: red;
        }
    </style>
</head>

<body class="gray-bg">

    <div class="middle-box text-center loginscreen animated fadeInDown">
        <div class="row">
            <div class="col-md-12">
                <div class="ibox-content">
                    
                    @php
                        $notif_status = 0;
                        $notif_message = 0;
                    @endphp

                    @php $notif_status = $status @endphp
                    @php $notif_message = $message @endphp

                    <h2 class="font-bold" style="text-align: center;">Email Verification</h2>

                    <p style="text-align: left;margin-top: 35px;font-size: 14px;"> <span class="badge" style="font-size: 14px;"> Status: </span>

                        @if ($notif_status == 404)
                            <span class="badge badge-danger" style="font-size: 13px; margin-left: 5px;">FAILED</span>
                        @elseif ($notif_status == 200)
                            <span class="badge badge-primary" style="font-size: 13px; margin-left: 5px;">SUCCESS</span>
                        @endif
                    </p>

                    <p style="margin-top: -5px;text-align: left;font-size: 14px;text-color:red;">
                        <span class="badge" style="font-size: 14px;"> Message: </span>
                        
                        @if ($notif_status == 404)
                            <span style="font-size: 13px; margin-left: 5px;"> @php echo $notif_message @endphp </span>
                        @elseif ($notif_status == 200)
                            <span style="font-size: 13px; margin-left: 5px;"> @php echo $notif_message @endphp </span>
                        @endif
                    </p>
                </div>

            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                SERENITY GARDEN ROOMS
            </div>
            <div class="col-md-6 text-right">
                <small>Copyrights &copy; 2021</small>
            </div>
        </div>
    </div>

    <!-- Mainly scripts -->
    <script src="{{ asset('js/jquery-3.1.1.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.js') }}"></script>

</body>

</html>
