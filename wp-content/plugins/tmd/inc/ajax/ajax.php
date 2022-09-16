<?php

// Get Movies by filter 
add_action('wp_ajax_get_movies_ajax', 'get_movies_ajax');
add_action('wp_ajax_nopriv_get_movies_ajax', 'get_movies_ajax');
