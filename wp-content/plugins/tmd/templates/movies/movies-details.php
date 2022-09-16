<?php
class getMovieData
{
    private $moviesdata;
    private $atts;
    private $imageURL;
    private $apikey;
    private $defaultimage;
    private $moviesDetailspage;
    private $actorDetailspage;
    public function __construct($moviesdata, $atts)
    {
        if (!empty($atts)) {
            $apikey = get_option('movies_apikey', '');
            if (empty($apikey)) {
                return 'please enter a valid API KEY';
            }
            $this->apikey = $apikey;
            $this->moviesDetailspage = 'movie-detail'; // movies details page slug
            $this->actorDetailspage = 'actor-detail'; // actor details page slug
            $this->defaultimage = 'https://via.placeholder.com/500x750.png';
            $this->moviesdata = $moviesdata;
            $this->imageURL = 'https://image.tmdb.org/t/p/w500/';


            $function = $atts['data'];
            $this->setup_function("get_movies_" . $function);
        } else {
            echo 'Missing params';
        }
    }

    public function setup_function($method)
    {
        call_user_func(array($this, $method), 'Not a valid params');
    }


    // return movies title
    public function get_movies_title()
    {
        $moviesdata = $this->moviesdata;
        echo '<h1>' . $moviesdata->original_title . '</h1>';
    }

    //  movie poster
    public function get_movies_poster()
    {
        $moviesdata = $this->moviesdata;
        if (empty($moviesdata->poster_path)) {

            echo '<img class="detail-img" src="' . $this->defaultimage . '"/>';
        } else {
            echo '<img class="detail-img" src="' . $this->imageURL . $moviesdata->poster_path . '"/>';
        }
    }

    //  movie banner
    public function get_movies_banner()
    {
        $moviesdata = $this->moviesdata;
        if (empty($moviesdata->poster_path)) {
            echo $this->defaultimage;
        } else {
            echo $this->imageURL . $moviesdata->poster_path;
        }
    }

    //  movie genres
    public function get_movies_genres()
    {
        $moviesdata = $this->moviesdata;
        // Get list of movies genres
        $genres = $moviesdata->genres;
        if (!empty($genres)) {
            $genres = json_decode(json_encode($genres), true);
            $genres = array_column($genres, 'name');
            $genres = implode(',', $genres);
        }else{
            $genres = '';
        }
        echo '<span>' . $genres . '</span>';
    }

    //  movie relese Overview
    public function get_movies_overview()
    {
        $moviesdata = $this->moviesdata;
        echo '<p>' . $moviesdata->overview . '</p>';
    }

    //  movie relese date
    public function get_movies_releasedate()
    {
        $moviesdata = $this->moviesdata;
        echo '<p>' . $moviesdata->release_date . '</p>';
    }

    // Production companies
    public function get_movies_companies()
    {
        $moviesdata = $this->moviesdata;
        $production_companies = $moviesdata->production_companies;

        if (!empty($production_companies)) {
            $production_companies = json_decode(json_encode($production_companies), true);
            $production_companies = array_column($production_companies, 'name');
            $production_companies = implode(',', $production_companies);
        }else{
            $production_companies = '';
        }
        echo '<span>' . $production_companies . '</span>';
    }

    // language
    public function get_movies_language()
    {
        $moviesdata = $this->moviesdata;
        echo '<p>' . $moviesdata->original_language . '</p>';
    }

    // popularity
    public function get_movies_popularity()
    {
        $moviesdata = $this->moviesdata;
        echo '<p>' . $moviesdata->popularity . '</p>';
    }

    // reviews
    public function get_movies_reviews()
    {
        $moviesdata = $this->moviesdata;
        $moviesId = $moviesdata->id;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.themoviedb.org/3/movie/' . $moviesId . '/reviews?api_key=' . $this->apikey . '',
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
        // all movies data
        $reviews =  json_decode($response);
        $allreviews = $reviews->results;
        if (!empty($allreviews)) {
            foreach ($allreviews as $reviews) {
                echo '<div>';
                echo '<span><b>' . $reviews->author . '</b></span>';
                echo '<p>' . $reviews->content . '</p>';
                echo '</div>';
            }
        }
    }

    // similar Movies
    public function get_movies_similar()
    {
        $moviesdata = $this->moviesdata;
        $moviesId = $moviesdata->id;
        $displayresults = 10;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.themoviedb.org/3/movie/' . $moviesId . '/similar?api_key=' . $this->apikey . '&sort_by=release_date.desc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
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

        // all movies data
        $simmovies =  json_decode($response);
        $moviesdata = $simmovies->results;

        if (!empty($moviesdata)) {

            echo '<div class="movies-list-wrap owl-carousel">';
            foreach ($moviesdata as $key => $movies) {
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

    //  Movies Cast
    public function get_movies_cast()
    {
        $moviesdata = $this->moviesdata;
        $moviesId = $moviesdata->id;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.themoviedb.org/3/movie/' . $moviesId . '/credits?api_key=' . $this->apikey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
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

        // all movies data
        $actorData =  json_decode($response);

        $actorData = $actorData->cast;

        if (!empty($actorData)) {

            echo '<div class="actor-list-wrap owl-carousel">';
            foreach ($actorData as $key => $actor) {
                $actorID = $actor->id;
                $poster = $actor->profile_path;
                echo '<div class="actor">';
                echo '<a href="' . get_site_url() . '/' . $this->actorDetailspage . '?aid=' . $actorID . '">';
                echo '<div class="actor-img">';
                if (!empty($poster)) {
                    echo '<img src="' . $this->imageURL . $poster . '" />';
                } else {
                    echo '<img src="' . $this->defaultimage . '" />';
                }
                echo '</div>';
                echo '<div class="actor-name"><span>' . $actor->original_name . '</span></div>';
                echo '</a>';
                echo '</div>';
            }
            echo '</div>';
        }
    }
}
