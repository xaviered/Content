@include('header')

<h2>Login</h2>
<form method="POST">
    <div class="whitespace"></div>
    
    <div class="row">
        <div class="column label">Email Address:</div>
        <div class="column value"><input name="email" type="text" class="text-field"></div>
    </div>
    <div class="row">
        <div class="column label">Password:</div>
        <div class="column value"><input name="password" type="password" class="text-field"></div>
    </div>
    <div class="row">
        <div class="column label"></div>
        <div class="column value"><input value="login" type="submit" class="button"></div>
    </div>

    <div class="whitespace"></div>
</form>

@include('footer')
