<?php

// Get Movies by filter 
function get_movies_ajax()
{
    $apikey = get_option('movies_apikey', '');
    if (empty($apikey)) {
        $return = array(
            'status'       => 'failed',
            'message'  => 'please enter a valid API KEY',
        );
        wp_send_json($return);
    } else {

        $displayresults = 10;
        $region = 'US';
        $imageURL = 'https://image.tmdb.org/t/p/w500/';
        $defaultimage = 'https://via.placeholder.com/500x750.png';
        $detailspage = 'movie-detail';
        $type = isset($_POST['listtype']) ? $_POST['listtype'] : '';
        $start_date = $_POST['year'] . '-' . $_POST['month'] . '-01'; // start from
        $end_date = $_POST['year'] . '-' . $_POST['month'] . '-28'; // end from

        $apiURL = 'https://api.themoviedb.org/3/discover/movie?api_key=' .  $apikey . '&primary_release_date.gte=' . $start_date . '&primary_release_date.lte=' . $end_date . '&region=' . $region;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiURL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        if (empty($response)) {
            $return = array(
                'status'       => 'failed',
                'message'  => 'There is Somthing Wrong with API',
            );
            wp_send_json($return);
        }
        // all movies data
        $moviesdata = json_decode($response);
        if (empty($moviesdata->results)) {
            $return = array(
                'status'       => 'failed',
                'message'  => 'No movies available',
            );
            wp_send_json($return);
        }

        $moviesdata = $moviesdata->results;
        $moviesdata = json_decode(json_encode($moviesdata), true);

        if ($type == 'upcoming') {
            usort($moviesdata, 'date_compare');
        } else {
            usort($moviesdata, 'rdate_compare');
        }


        $html = '';
        foreach ($moviesdata as $key => $movies) {
            if ($key == $displayresults) {
                break;
            }

            $moviesID = $movies['id'];
            $poster = $movies['poster_path'];
            $short_desc = wp_trim_words($movies['overview'], 10, '...');
            $html .= '<div class="movie item">';
            if (!empty($poster)) {
                $html .= '<a href="' . get_site_url() . '/' . $detailspage . '?mid=' . $moviesID . '"><img src="' . $imageURL . $poster . '" /></a>';
            } else {
                $html .= '<a href="' . get_site_url() . '/' . $detailspage . '?mid=' . $moviesID . '"><img src="' . $defaultimage . '" /></a>';
            }
            $html .= '<div class="movie-details">';
            $html .= '<a class="movie-name" href="' . get_site_url() . '/' . $detailspage . '?mid=' . $moviesID . '">' . $movies['original_title'] . ' | </a><span class="genre">' . $movies['genre'] . '</span>';
            $html .= '<span class="releasedate">' . $movies['release_date'] . '</span>';
            $html .= '<span class="releasedate">' . $short_desc . '</span>';
            $html .= '<span class="genre">' . $movies['genre'] . '</span>';
            $html .= '</div>';

            $html .= '</div>';
        }

        $return = array(
            'status'       => 'success',
            'data'  => $html,
        );
        wp_send_json($return);
    }

    exit;
}

function date_compare($a, $b)
{
    $t1 = strtotime($a['release_date']);
    $t2 = strtotime($b['release_date']);
    return $t1 - $t2;
}
function rdate_compare($a, $b)
{
    $t1 = strtotime($b['release_date']);
    $t2 = strtotime($a['release_date']);
    return $t1 - $t2;
}


// Disables the block editor from managing widgets in the Gutenberg plugin.
add_filter('gutenberg_use_widgets_block_editor', '__return_false');
// Disables the block editor from managing widgets.
add_filter('use_widgets_block_editor', '__return_false');


//Register sidebar Movies list
function wpm_moviesidebar_init()
{

    register_sidebar(array(
        'name' => __('Movies List Sidebar', 'wpm'),
        'id' => 'sidebar-movies-list',
        'description' => __('The sidebar appears on the left side of the movies list page', 'wpm'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
}

add_action('widgets_init', 'wpm_moviesidebar_init');


//Register sidebar Actorss list
function wpm_actorssidebar_init()
{

    register_sidebar(array(
        'name' => __('Actorss List Sidebar', 'wpm'),
        'id' => 'sidebar-actors-list',
        'description' => __('The sidebar appears on the left side of the actors list page', 'wpm'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
}

add_action('widgets_init', 'wpm_actorssidebar_init');
