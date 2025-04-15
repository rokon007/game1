<p class="alert-success" style="margin-left: 35% ">
    <?php
        $message = Session::get('message');
            if($message)
            {
                echo $message;
                Session::put('message',null);
            }
            ?>
    </p>