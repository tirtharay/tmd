<?php
$actorname = isset($_GET['actorname']) ? $_GET['actorname'] : '';
?>
<div class="actor-sidebar-filter">
    <form class="filter-containers" id="actor-list-filter" action="<?php echo get_site_url(); ?>/<?php echo $actorlistpage ?>">


        <div class="actor-search">
            <input class="filter-dd" type="text" name="actorname" value="<?php echo $actorname ?>" placeholder="Type actor Name" />
        </div>
        <div class="submit">
            <button type="submit" value="submit">Find</button>
        </div>
    </form>
</div>