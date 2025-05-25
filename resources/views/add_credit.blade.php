<!DOCTYPE html>
<html>
    <head>
      <link rel="stylesheet" type="text/css" href="{{ asset('/style.css') }}" >
      <title>إضافة رصيد</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device_width,initial_scale=1.0">
    </head>
    <body>
        <form action="add_credit" method="post">
          @csrf
          <input type="email"  name="email"  placeholder="قم بإدخال البريد الإلكتروني الخاص بالمستخدم هنا"> <br><br>
          <input type="number" name="new_credit" placeholder="قم بإدخال الرصيد هنا"> <br><br>
          <button type="submit">تم</button><br><br>
          <br><p class="error">@if(session()->has('message'))  {{ session()->get('message') }}</p> <br><br> @endif
          <br><p class="success">@if(session()->has('success'))  {{ session()->get('success') }}</p> <br><br> @endif
         </form>
      </div>
    </body>
</html>
