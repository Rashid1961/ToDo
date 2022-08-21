<!-- < ?php
//use App\Http\Middleware\checkAuth;
//include_once('/var/www/ToDo/include/auth.php');

? >


<html>
    <head>
        <title>
            < T I T L E >
        </title>
    </head>
    <body>
        <div>
            А что дальше?
        </div>
    </body>
</html>
 -->

 <html lang="en">
<head>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="icon" href="/v2/favicon.ico" /> 
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />

        <title>ToDo</title>
    <!-- <title>Laravel Ajax jquery Validation Tutorial</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css"> -->
    </head>
    <body>
        <div class="container panel panel-default ">
                <h2 class="panel-heading">Laravel Ajax jquery Validation</h2>
            <form id="contactForm">
                <div class="form-group">
                    <input type="text" name="name" class="form-control" placeholder="Enter Name" id="name">
                </div>

                <div class="form-group">
                    <input type="text" name="email" class="form-control" placeholder="Enter Email" id="email">
                </div>

                <div class="form-group">
                    <input type="text" name="mobile_number" class="form-control" placeholder="Enter Mobile Number" id="mobile_number">
                </div>

                <div class="form-group">
                    <input type="text" name="subject" class="form-control" placeholder="Enter subject" id="subject">
                </div>

                <div class="form-group"> 
                  <textarea class="form-control" name="message" placeholder="Message" id="message"></textarea>
                </div>
                <div class="form-group">
                    <button class="btn btn-success" id="submit">Submit</button>
                </div>
            </form>
        </div>

        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script> -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

        <script>
            $('#contactForm').on('submit',function(event){
                event.preventDefault();

                let name = $('#name').val();
                let email = $('#email').val();
                let mobile_number = $('#mobile_number').val();
                let subject = $('#subject').val();
                let message = $('#message').val();

                $.ajax({
                    url:  "/contact-form",
                    type: "POST",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        name:name,
                        email:email,
                        mobile_number:mobile_number,
                        subject:subject,
                        message:message,
                    },
                    success:function(response){
                        console.log(response);
                    },
                });
            });
        </script>
    </body>
</html>