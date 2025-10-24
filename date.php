<?php
//Redirect date pages to the homepage with WordPress redirect function
wp_safe_redirect( get_home_url(), 301 );
exit;