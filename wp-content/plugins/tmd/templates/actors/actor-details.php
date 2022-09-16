<?php
class getActorData
{
    private $actordata;
    private $atts;
    private $imageURL;
    private $apikey;
    private $moviesDetailspage;
    private $defaultimage;
    public function __construct($actordata, $atts)
    {
        if (!empty($atts)) {
            $apikey = get_option('movies_apikey', '');
            if (empty($apikey)) {
                return 'please enter a valid API KEY';
            }
            $this->apikey = $apikey;
            $this->moviesDetailspage = 'movie-detail'; // movies details page slug
            $this->actordata = $actordata;
            $this->defaultimage = 'https://via.placeholder.com/500x750.png';
            $this->imageURL = 'https://image.tmdb.org/t/p/w500/';

            $function = $atts['data'];
            $this->setup_function("get_actor_" . $function);
        } else {
            echo 'Missing params';
        }
    }

    public function setup_function($method)
    {
        call_user_func(array($this, $method), 'Not a valid params');
    }


    // return actor title
    public function get_actor_name()
    {
        $actordata = $this->actordata;
        echo '<h1>' . $actordata->name . '</h1>';
    }

    //  actor poster
    public function get_actor_poster()
    {
        $actordata = $this->actordata;
        echo '<img src="' . $this->imageURL . $actordata->profile_path . '"/>';
    }

    //  actor relese date
    public function get_actor_birthday()
    {
        $actordata = $this->actordata;
        echo '<p> ' . $actordata->birthday . ' </p>';
    }

    //  actor Birtplace
    public function get_actor_birthplace()
    {
        $actordata = $this->actordata;
        echo '<p>' . $actordata->place_of_birth . ' </p>';
    }

    //  actor relese Overview 
    public function get_actor_bio()
    {
        $actordata = $this->actordata;
        echo '<p>' . $actordata->biography . '</p>';
    }

    // popularity 
    public function get_actor_popularity()
    {
        $actordata = $this->actordata;
        echo '<p>' . $actordata->popularity . '</p>';
    }

    // images 
    public function get_actor_images()
    {
        $actordata = $this->actordata;
        $actorId = $actordata->id;
        $displayresults = 10;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.themoviedb.org/3/person/' . $actorId . '/images?api_key=' . $this->apikey,
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
            return '';
        }
        // all actor data
        $images =  json_decode($response);
        $allimages = $images->profiles;

        if (!empty($allimages)) {
            echo '<div class="actor-list-wrap owl-carousel">';
            foreach ($allimages as $key => $images) {
                if ($key == $displayresults) {
                    break;
                }
                $poster = $images->file_path;
                echo '<div class="actor item">';
                if (!empty($poster)) {
                    echo '<img class="detail-img" src="' . $this->imageURL . $poster . '" />';
                } else {
                    echo '<img class="detail-img" src="' . $this->defaultimage . '" />';
                }
                echo '</div>';
            }
            echo '</div>';
        }
    }

    public function get_actor_movies()
    {
        $actordata = $this->actordata;
        $actorId = $actordata->id;
        $displayresults = 10;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.themoviedb.org/3/person/' . $actorId . '/movie_credits?api_key=' . $this->apikey,
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
            return '';
        }
        // all actor data
        $movies =  json_decode($response);
        $allmovies = $movies->cast;

        if (!empty($allmovies)) {
            echo '<div class="movies-list-wrap owl-carousel">';
            foreach ($allmovies as $key => $movies) {
                if ($key == $displayresults) {
                    break;
                }

                $moviesID = $movies->id;
                $poster = $movies->poster_path;
                echo '<div class="movie item"><a href="' . get_site_url() . '/' . $this->moviesDetailspage  . '?mid=' . $moviesID . '">';
                if (!empty($poster)) {
                    echo '<img src="' . $this->imageURL . $poster . '" />';
                } else {
                    echo '<img src="' . $this->defaultimage . '" />';
                }
                echo '<div class="movie-details">';
                echo $movies->original_title;
                echo '</div>';

                echo '</a></div>';
            }
            echo '</div>';
        }
    }
    public function get_actor_deathday()
    {
        return '';
    }
}
