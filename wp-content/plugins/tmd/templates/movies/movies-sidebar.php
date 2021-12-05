<?php
$mname = isset($_GET['moviename']) ? $_GET['moviename'] : '';
$movieyear = isset($_GET['movieyear']) ? $_GET['movieyear'] : '';
$moviegenre = isset($_GET['moviegenre']) ? $_GET['moviegenre'] : '';
?>
<div class="movies-sidebar-filter">
    <form id="movies-list-filter" class="filter-containers" action="<?php echo get_site_url(); ?>/<?php echo $movieslistpage; ?>">
        <div class="movies-search">

            <select class="filter-dd" name="movieyear">
                <option value="">select Year</option>
                <?php
                for ($i = 20; $i >= -10; $i--) {
                    $time = '-' . $i . ' year';
                    $year = date('Y', strtotime($time));
                    $selected = '';
                    if ($year == $movieyear) {
                        $selected = 'selected';
                    }
                    echo '<option value=' . $year . ' ' . $selected . '>' . $year . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="movies-genre">


            <select class="filter-dd" name="moviegenre">
                <option value="">select Genre </option>
                <?php
                foreach ($genredata as $key => $genre) {
                    $selected = '';
                    if ($key == $moviegenre) {
                        $selected = 'selected';
                    }
                    echo '<option value=' . $key . ' ' . $selected . '>' . $genre . '</option>';
                }

                ?>
            </select>
        </div>
        <div class="movies-search">

            <input class="filter-dd" type="text" name="moviename" value="<?php echo $mname ?>" placeholder="Type Movies Name" />
        </div>
        <div class="submit">
            <button type="submit" value="submit">Find</button>
        </div>
    </form>
</div>