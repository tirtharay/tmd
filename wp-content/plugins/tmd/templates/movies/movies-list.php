<div class="all-movies-list">
    <?php
    $i = 1;
    $custgenre = [];
    if (!empty($movies['genre_ids'])) {
        foreach ($movies['genre_ids'] as $key => $genre) {
            $custgenre[] = $genredata[$genre];
        }
    }

    foreach ($moviesdata as $movies) {

        if ($i == 1) {
            echo '';
        }


        $imgURL = $imageURL . $movies->poster_path;
        if (empty($movies->poster_path)) {
            $imgURL = $defaultimage;
        }
        $custgenre = [];
        if (!empty($movies->genre_ids)) {
            foreach ($movies->genre_ids as $key => $genre) {
                $custgenre[] = $genredata[$genre];
            }
        }

        echo '<div class="movie">';
        echo '<a href="#"><img src="' . $imgURL . '"></a>';
        echo '<div class="movie-details">';
        echo '<a class="movie-name" href="' . get_site_url() . '/' . $moviesDetailspage . '?mid=' . $movies->id . '">' . $movies->original_title . '</a> <span class="genre">' . implode(',', $custgenre) . '</span>';


        echo '</div>';
        echo '</div>';

        if ($i == 5) {
            $i = 0;
            echo '';
        }
        $i++;
    }

    global $post;
    $post_slug = get_site_url() . '/' . $post->post_name;
    echo getuserpagination($total_results, $displayCount, $currentpage, 5, $post_slug);
    ?>
</div>

<?php

function getuserpagination($total, $displayCount, $currentPage, $displayPageList, $slug)
{

    $paginationHTML = '';
    if ($total > $displayCount) {
        // Total available page
        $str = explode('?', $_SERVER['REQUEST_URI']);
        $queryString = '';
        if (isset($str[1])) {
            $queryString = '?' . $str[1];
        }
        $availablePage = ceil($total / $displayCount);
        $pre_next_count = ceil(($displayPageList - 1) / 2); // count of nect and previos button 
        $pageStart = 1;
        $paginationHTML .= '<div class="paginaion-wrap">';

        //  Go to previous page
        if ($currentPage != 1) {
            $paginationHTML .= '<a class="pagination-page" href="' . $slug . '/page/' . ($currentPage - 1) . $queryString . '">
        <svg xmlns="http://www.w3.org/2000/svg" width="7" height="7" viewBox="0 0 7 7" fill="none" class="pagination-arrow">
        <path d="M0.8 4.256L3.232 6.176V4.8L1.632 3.552L3.232 2.304V0.927999L0.8 2.848V4.256ZM3.936 4.256L6.368 6.176V4.8L4.768 3.552L6.368 2.304V0.927999L3.936 2.848V4.256Z" fill="#FCD21E"></path>
        </svg></a>';
        }

        // If page is more then 3 then display First page
        if ($currentPage > 3) {
            $paginationHTML .= '<a class="pagination-page" href="' . $slug . '/page/1' . $queryString . '">1</a>';
            if ($currentPage > 4) {
                $paginationHTML .= '<a class="pagination-page dot-page">...</a>';
            }



            $pageStart = $currentPage - $pre_next_count; //  Change the Loop start page 
            $displayPageList = $currentPage + $pre_next_count;
        }

        // center page of pagination : START

        for ($page = $pageStart; $page <= $displayPageList; $page++) {


            $activePage = '';
            if ($currentPage == $page) {
                $activePage = 'active'; // make active page
            }
            $paginationHTML .= '<a class="pagination-page ' . $activePage . '" href="' . $slug . '/page/' . $page . $queryString . '">' . $page . '</a>';

            // If page is the last page then stop the loop
            if ($page == $availablePage) {
                break;
            }
        }
        // center page of pagination : END

        // If page is Less then last 2 then display Last page
        if ($currentPage < ($availablePage - 2)) {
            if ($currentPage < ($availablePage - 3)) {
                $paginationHTML .= '<a class="pagination-page dot-page">...</a>';
            }
            $paginationHTML .= '<a class="pagination-page" href="' . $slug . '/page/' . $availablePage . $queryString . '">' . $availablePage . '</a>';
        }

        //  Go to next page
        if ($currentPage != $availablePage) {
            $paginationHTML .= '<a class="pagination-page" href="' . $slug . '/page/' . ($currentPage + 1) . $queryString . '">
        <svg xmlns="http://www.w3.org/2000/svg" width="6" height="7" viewBox="0 0 6 7" fill="none" class="pagination-arrow">
        <path d="M5.608 2.848L3.176 0.927999V2.304L4.776 3.552L3.176 4.8V6.176L5.608 4.256V2.848ZM2.472 2.848L0.0400001 0.927999V2.304L1.64 3.552L0.0400001 4.8V6.176L2.472 4.256V2.848Z" fill="#FCD21E"></path>
        </svg></a>';
        }


        $paginationHTML .= '</div>';
    }


    return $paginationHTML;
}
