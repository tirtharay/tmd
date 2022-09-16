<?php

echo '<div class="movies-list-wrap owl-carousel">';
foreach ($moviesdata as $key => $movies) {
    if ($key == $displayresults) {
        break;
    }

    $moviesID = $movies['id'];
    $poster = $movies['poster_path'];
    $short_desc = wp_trim_words($movies['overview'], 10, '...');
    $custgenre = [];
    if (!empty($movies['genre_ids'])) {
        foreach ($movies['genre_ids'] as $key => $genre) {
            $custgenre[] = $genredata[$genre];
        }
    }
    echo '<div class="movie item">';
    if (!empty($poster)) {
        echo '<a href="' . get_site_url() . '/' . $detailspage . '?mid=' . $moviesID . '"><img src="' . $imageURL . $poster . '" /></a>';
    } else {
        echo '<a href="' . get_site_url() . '/' . $detailspage . '?mid=' . $moviesID . '"><img src="' . $defaultimage . '" /></a>';
    }
    echo '<div class="movie-details">';
    echo '<a class="movie-name" href="' . get_site_url() . '/' . $detailspage . '?mid=' . $moviesID . '">' . $movies['original_title'] . ' | </a><span class="genre">' . implode(',', $custgenre) . '</span>';
    echo '<span class="releasedate">' . $movies['release_date'] . '</span>';
    echo '<span class="releasedate">' . $short_desc . '</span>';




    echo '</div>';

    echo '</div>';
}
echo '</div>';
