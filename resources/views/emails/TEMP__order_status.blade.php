<section style="width: 100%;">
    <div style="
        min-width:600px;
        width:700px;
        min-height: 700px;
        max-height: 100vh;
        background-image: url('https://tareek.go-demo.com/public/storage/default-images/email-background.png');
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
            <img src="https://tareek.go-demo.com/public/storage/default-images/app-logo-email.png" alt="{{ config('app.name') }} Logo" height="100" style="margin: 40px 0;">
        </figure>

        <div style="padding: 20px 40px; position:absolute; top: 30%;">
            <p style="font-size:18px;font-family: Arial, Helvetica, sans-serif;color: #FFF;">Hi <b>{{ $email_data['name'] }},</b></p>
            <p style="font-size:18px;font-family: Arial, Helvetica, sans-serif;color: #FFF;">Your order status has been updated on {{ config('app.name') }}.<br></p><br>
            <p style="font-size:18px;font-family: Arial, Helvetica, sans-serif;color: #FFF;">Please sign in to {{ config('app.name') }} and check you order details.</p><br>
            <p style="font-size:16px;font-family: Arial, Helvetica, sans-serif;color: #FFF;">{{ config('app.name') }} Team</p>
            <br>
            <br>
        </div>

    </div>
</section>