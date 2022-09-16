<?php

class shortcode
{

    private $templatePath;
    private $apikey;
    private $moviesDetailspage;
    private $movieslistpage;
    private $defaultimage;
    private $imagePath;
    private $moviesDetailsshortcode;
    private $actordetailspage;
    private $actorlistpage;
    private $actorDetailsshortcode;
    private $region;
    private $genredata;
    private $searchpage;
    public function __construct()
    {

        $apikey = get_option('movies_apikey', '');
        if (empty($apikey)) {
            return 'please enter a valid API KEY';
        }
        $this->apikey = $apikey;
        $this->moviesDetailspage = 'movie-detail'; // movies details page slug
        $this->movieslistpage = 'movie-list'; // movies details page slug
        $this->actorDetailspage = 'actor-detail'; // actor details page slug
        $this->actorlistpage = 'actors-list'; // actor details page slug
        $this->searchpage = 'search-results'; // actor details page slug
        $this->imagePath = 'https://image.tmdb.org/t/p/w500/';
        $this->defaultimage = 'https://via.placeholder.com/500x750.png';
        $this->region = 'US';
        $this->templatePath = MOVIESPATH . '/templates';


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.themoviedb.org/3/genre/movie/list?api_key=' . $this->apikey,
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

        // all genre data
        $genredata =  json_decode($response);
        $genredata = $genredata->genres;
        $genredata = json_decode(json_encode($genredata), true);
        $genrearray = [];
        foreach ($genredata as $genre) {
            $genrearray[$genre['id']] = $genre['name'];
        }
        $this->genredata = $genrearray;


        // list of shortcods
        add_shortcode('get_movies', array($this, 'get_movies')); // display upcomming and recents movies
        add_shortcode('get_popular_actors', array($this, 'get_popular_actors')); // get 10 populer actors
        add_shortcode('get_movies_list', array($this, 'get_movies_list')); // Movies list page
        add_shortcode('get_actor_list', array($this, 'get_actor_list')); // get the list of actor
        add_shortcode('get_search_form', array($this, 'get_search_form')); // get searching form
        add_shortcode('get_search_results', array($this, 'get_search_results')); // get the search results

        if (isset($_GET['mid']) && !empty($_GET['mid'])) {
            $this->moviesDetailsshortcode = $this->load_movies_details($_GET['mid']);
            add_shortcode('get_movies_details', array($this, 'get_movies_details'));
        }

        if (isset($_GET['aid']) && !empty($_GET['aid'])) {
            $this->actorDetailsshortcode = $this->load_actor_details($_GET['aid']);
            add_shortcode('get_actor_details', array($this, 'get_actor_details'));
        }
    }

    public function get_movies($atts)
    {

        // config var
        // $page = 1; &page=' . $page
        $displayresults = 10;
        $region = $this->region;
        $imageURL = $this->imagePath;
        $defaultimage = $this->defaultimage;
        $detailspage = $this->moviesDetailspage;
        $type = isset($atts['type']) ? $atts['type'] : ''; // movies type like upcoming or recents
        $genredata = $this->genredata;
        $apiURL = '';

        // Make the url dynamic : START
        if ($type == 'upcoming') {
            // if upcomming
            $start_date = date("Y-m-d", strtotime('+1 day'));
            $end_date = date("Y-m-d", strtotime('+30 day'));
        } else {
            // if recents
            $start_date = date("Y-m-d", strtotime('-30 day'));
            $end_date = date("Y-m-d", strtotime('-1 day'));
        }

        $apiURL = 'https://api.themoviedb.org/3/discover/movie?api_key=' .  $this->apikey . '&primary_release_date.gte=' . $start_date . '&primary_release_date.lte=' . $end_date . '&region=' . $region;

        // Make the url dynamic : END

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiURL,
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
            return 'There is Somthing Wrong with API';
        }
        // all movies data
        $moviesdata = json_decode($response);
        if (empty($moviesdata->results)) {
            return 'No movies available';
        }
        $moviesdata = $moviesdata->results;
        $moviesdata = json_decode(json_encode($moviesdata), true);

        if ($type == 'upcoming') {
            usort($moviesdata, array($this, 'date_compare'));
        } else {
            usort($moviesdata, array($this, 'rdate_compare'));
        }

        // list of month options
        $months = array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December');


        ob_start();
        echo '<div class="movies-list-slider-wrap">';
        // get movies short code header
        // echo do_shortcode('[get_movies_details data="similarmovies"]');
        require_once  $this->templatePath . '/movies/movies-filter.php';
        // // get movies shortcode content
        require_once  $this->templatePath . '/movies/movies-content.php';
        echo '</div>';
        return ob_get_clean();
    }

    public function load_movies_details($moviesId)
    {
        $imageURL = $this->imagePath;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.themoviedb.org/3/movie/' . $moviesId . '?api_key=' . $this->apikey . '',
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
        return json_decode($response);
    }

    public function get_movies_details($atts)
    {
        $moviesdata = $this->moviesDetailsshortcode;

        ob_start();
        if (!empty($moviesdata)) {
            require_once  $this->templatePath . '/movies/movies-details.php';  // Get the template file of movies details page
            new getMovieData($moviesdata, $atts);
        } else {
            echo 'No movies found';
        }

        return ob_get_clean();
    }

    public function get_popular_actors()
    {
        $apikey = $this->apikey;
        $apiURL = 'https://api.themoviedb.org/3/person/popular?api_key=' .  $this->apikey;
        $defaultimage = $this->defaultimage;
        $imageURL = $this->imagePath;
        $actorDetailspage = $this->actorDetailspage;
        $total = 10;
        // Make the url dynamic : END

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiURL,
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
            return 'There is Somthing Wrong with API';
        }
        $actorData = json_decode($response);
        if (empty($actorData)) {
            return 'No actor Found';
        }
        $actorData = $actorData->results;

        ob_start();
        echo '<div class="actor-list-slider-wrap">';
        // get poplar Actor list
        require_once  $this->templatePath . '/actors/popular.php';
        echo '</div>';
        return ob_get_clean();
    }

    public function load_actor_details($actorId)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.themoviedb.org/3/person/' . $actorId . '?api_key=' . $this->apikey . '',
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
        // all actor data
        return json_decode($response);
    }

    public function get_actor_details($atts)
    {

        $actordata = $this->actorDetailsshortcode;

        ob_start();
        if (!empty($actordata)) {
            require_once  $this->templatePath . '/actors/actor-details.php';  // Get the template file of actor details page
            new getActorData($actordata, $atts);
        } else {
            echo 'No actor found';
        }

        return ob_get_clean();
    }

    public function get_movies_list($atts)
    {
        $genredata = $this->genredata;

        if (isset($atts['type']) && $atts['type'] == 'sidebar') {
            $movieslistpage = $this->movieslistpage;
            ob_start();
            require_once  $this->templatePath . '/movies/movies-sidebar.php';  // Get the template file of actor details page
            return ob_get_clean();
        } else {
            $moviesDetailspage = $this->moviesDetailspage;
            $imageURL = $this->imagePath;
            $defaultimage = $this->defaultimage;

            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1; //current number of page

            $movieyear = isset($_GET['movieyear']) ? $_GET['movieyear'] : '';
            $moviegenre = isset($_GET['moviegenre']) ? $_GET['moviegenre'] : '';
            $moviename = isset($_GET['moviename']) ? $_GET['moviename'] : '';

            if (!empty($movieyear)) {
                $movieyear = '&primary_release_year=' . $movieyear;
            }

            if (!empty($moviename)) {
                $moviename = str_replace(' ', '%20', $moviename);
                $moviename = '&with_text_query=' . $moviename;
            }

            if (!empty($moviegenre)) {
                $moviegenre = '&with_genres=' . $moviegenre;
            }


            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.themoviedb.org/3/discover/movie/?api_key=' . $this->apikey . '&region=' . $this->region . '&sort_by=original_title.asc&page=' . $paged . $movieyear . $moviename . $moviegenre,
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
            $moviesdata =  json_decode($response);

            $currentpage = $moviesdata->page;
            $total_results = $moviesdata->total_results;
            $displayCount = 20;
            $moviesdata = $moviesdata->results;

            if (empty($moviesdata)) {
                return 'No movies available with your filters';
            }

            ob_start();
            require_once  $this->templatePath . '/movies/movies-list.php';  // Get the template file of actor details page
            return ob_get_clean();
        }
    }

    public function get_actor_list($atts)
    {
        if (isset($atts['type']) && $atts['type'] == 'sidebar') {
            $actorlistpage = $this->actorlistpage;
            ob_start();
            require_once  $this->templatePath . '/actors/actor-sidebar.php';  // Get the template file of actor details page
            return ob_get_clean();
        } else {

            $imageURL = $this->imagePath;
            $defaultimage = $this->defaultimage;
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1; //current number of page
            $actorDetailspage = $this->actorDetailspage; // actor details page slug

            $actorname = isset($_GET['actorname']) ? $_GET['actorname'] : '';

            $apiURL = '';
            if (!empty($actorname)) {
                $actorname = str_replace(' ', '%20', $actorname);
                $apiURL = 'https://api.themoviedb.org/3/search/person?api_key=' .  $this->apikey . '&page=' . $paged . '&query=' . $actorname;
            } else {
                $apiURL = 'https://api.themoviedb.org/3/person/popular?api_key=' .  $this->apikey . '&page=' . $paged . '&language=en-US&sort_by=name';
            }


            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $apiURL,
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

            $currentpage = $actorData->page;
            $total_results = $actorData->total_results;
            $displayCount = 20;
            $actorData = $actorData->results;

            ob_start();
            require_once  $this->templatePath . '/actors/actor-list.php';  // Get the template file of actor details page
            return ob_get_clean();
        }
    }

    public function get_search_form()
    {
        $type = isset($_GET['type']) ? $_GET['type'] : '';

        $movieselected = '';
        $actorselected = '';
        if ($type == 'actor') {
            $actorselected = 'checked';
        }
        if ($type == 'movies') {
            $movieselected = 'checked';
        }

        $search = isset($_GET['search']) ? $_GET['search'] : '';
        ob_start();
?>
        <div class="search-form">
            <form id="search-form" action="<?php echo $this->searchpage ?>">
                <div>
                    <input type="radio" id="movies" name="type" value="movies" <?php echo $movieselected ?>>
                    <label for="movies">Movies</label><br>
                    <input type="radio" id="actor" name="type" value="actor" <?php echo $actorselected ?>>
                    <label for="actor">Actors</label><br>
                </div>
                <div>
                    <div class="search-input">
                        <input class="filter-dd" type="text" name="search" value="<?php echo $search ?>" required />
                    </div>
                    <button type="submit">Search</button>
            </form>
        </div>
<?php
        return ob_get_clean();
    }

    public function get_search_results()
    {

        if (!empty($_GET)) {

            $type = isset($_GET['type']) ? $_GET['type'] : '';
            $search = isset($_GET['search']) ? $_GET['search'] : '';

            if (empty($type)) {
                return 'Please select the search type';
            }
            if (empty($search)) {
                return 'Please Search somthings';
            }
            ob_start();

            if ($type == 'actor') {
                //  If user select from Actors
                $imageURL = $this->imagePath;
                $defaultimage = $this->defaultimage;
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1; //current number of page
                $actorDetailspage = $this->actorDetailspage; // actor details page slug

                $search = str_replace(' ', '%20', $search);
                $apiURL = 'https://api.themoviedb.org/3/search/person?api_key=' .  $this->apikey . '&page=' . $paged . '&query=' . $search;

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $apiURL,
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
                // all actor  data
                $actorData =  json_decode($response);

                $currentpage = $actorData->page;
                $total_results = $actorData->total_results;
                $displayCount = 20;
                $actorData = $actorData->results;


                ob_start();
                require_once  $this->templatePath . '/actors/actor-list.php';  // Get the template file of actor details page
                return ob_get_clean();
            } else {
                //  If user select from movies
                $moviesDetailspage = $this->moviesDetailspage;
                $imageURL = $this->imagePath;
                $defaultimage = $this->defaultimage;

                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1; //current number of

                if (!empty($search)) {
                    $search = str_replace(' ', '%20', $search);
                    $search = '&with_text_query=' . $search;
                }


                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api.themoviedb.org/3/discover/movie/?api_key=' . $this->apikey . '&region=' . $this->region . '&sort_by=original_title.asc&page=' . $paged  . $search,
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
                $moviesdata =  json_decode($response);

                $currentpage = $moviesdata->page;
                $total_results = $moviesdata->total_results;
                $displayCount = 20;
                $moviesdata = $moviesdata->results;

                if (empty($moviesdata)) {
                    return 'No movies available with your filters';
                }

                require_once  $this->templatePath . '/movies/movies-list.php';  // Get the template file of actor details page
            }

            return ob_get_clean();
        } else {
            return 'Please Search somthing from search form';
        }
    }

    public function date_compare($a, $b)
    {
        $t1 = strtotime($a['release_date']);
        $t2 = strtotime($b['release_date']);
        return $t1 - $t2;
    }

    public function rdate_compare($a, $b)
    {
        $t1 = strtotime($b['release_date']);
        $t2 = strtotime($a['release_date']);
        return $t1 - $t2;
    }
}
