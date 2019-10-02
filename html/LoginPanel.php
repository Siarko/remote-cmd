<?php
    $cause = null;
    if(isset($_GET['cause'])){
        $cause = \P3rc1val\auth\User::getLoginErrCause($_GET['cause']);
    }
?>

<?php if($cause !== null):?>
    <div class="alert alert-danger" role="alert">
        <?=$cause?>
    </div>
<?php endif;?>

<div class="simple-login-container">
    <h2>Login</h2>
    <form method="post">
        <div class="row">
            <div class="col-md-12 form-group">
                <input type="text" name="login" class="form-control" placeholder="Login">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 form-group">
                <input type="password" name="password" placeholder="Password" class="form-control">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 form-group">
                <input type="submit" class="btn btn-block btn-login">
            </div>
        </div>
    </form>
</div>