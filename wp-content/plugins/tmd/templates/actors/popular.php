<?php

$i = 1;
foreach ($actorData as $key => $actor) {
    if ($total == $key) {
        break;
    }

    if ($i == 1)
        echo '';


?>
    <div class="actor">
        <a href="<?php echo get_site_url() . '/' . $actorDetailspage . '?aid=' . $actor->id ?>">
            <?php
            if ($actor->profile_path == '') {
                // defaultimage
            ?><div class="actor-img"><img src="<?php echo $defaultimage; ?>" /></div>
            <?php
            } else {

            ?><div class="actor-img"><img src="<?php echo $imageURL . $actor->profile_path; ?>" /></div>
            <?php
            }
            ?>
            <div class="actor-name"><span><?php echo $actor->name ?></span></div>
        </a>
    </div>
<?php

    if ($i == 5) {
        $i = 0;
        echo '';
    }
    $i++;
}
