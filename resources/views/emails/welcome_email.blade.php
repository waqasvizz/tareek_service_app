<section style="width: 100%;">
    <div style="
        min-width:600px;
        width:700px;
        min-height: 700px;
        max-height: 100vh;
        background-image: url({{ asset('storage/default-images/email-background.png') }});
        background-repeat: no-repeat;       
        background-size: cover;
        background-position: bottom center;
        margin: 10px auto;
        box-sizing: border-box;
        overflow: hidden;
        padding: 10px 20px;
        position:relative;
        ">

        <figure style="display:flex;justify-content:center">
            <img src="{{ asset('storage/default-images/app-logo-email.png') }}" alt="{{ config('app.name') }} Logo" height="100" style="margin: 40px 0;">
        </figure>

        <div style="padding: 20px 40px; position:absolute; top: 30%;">
            <p style="font-size:18px;font-family: Arial, Helvetica, sans-serif;color: #FFF;">Hi <b>{{ $email_data['name'] }},</b></p>
            <p style="font-size:18px;font-family: Arial, Helvetica, sans-serif;color: #FFF;">We are happy that you signed up for {{ config('app.name') }}.<br> To start exploring the {{ config('app.name') }}, please confirm your email address.<br><br><br>
            <a href="{{ route('email_verify', $email_data['token']) }}" target="_blank" style="background-color: #FFF; border: none; padding: 10px 20px; text-decoration: none; margin: 4px 2px; cursor: pointer; border-radius: 20px; font-size: 16px; color: #000;">Verify Now</a></p><br>
            <p style="font-size:18px;font-family: Arial, Helvetica, sans-serif;color: #FFF;">Welcome to {{ config('app.name') }}.</p>
            <p style="font-size:16px;font-family: Arial, Helvetica, sans-serif;color: #FFF;">{{ config('app.name') }} Team</p>
            <br>
            <br>
        </div>

    </div>
</section>