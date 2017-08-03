<div id="receptiviti_search">
<?php
    if ($isManual) {
?>
    <form action="" method="post">
        <input type="text" name="handle" id="ra-handle" value="<?php echo $handle;?>" placeholder="<?php _e("Enter Twitter Username", RECEPTIVITI_PLUGIN_SLUG__);?>">
        <input type="submit" id="r-submit">
    </form>
<?php
    }
?>
    <div id="receptiviti_error"><?php echo $error;?></div>

<?php
if (!empty($output)) echo $output;

if (!empty($this->graphValues)) {
    $graphColors            = array_fill(0, count(self::$GRAPH_CATEGORIES), "'#d36c65'");
    $jsArray                = "";
?>

<script>
    window.graphValues      = [<?php echo $this->graphValues;?>];
    window.graphColors      = [<?php echo implode(",", $graphColors);?>];
</script>

    <div id="receptiviti_wrapper">
        <div id="receptiviti_graph" class="r_component">
            <div class="r_title"><?php _e("Receptiviti Scores", RECEPTIVITI_PLUGIN_SLUG__);?></div>
            <div id="r_graph"></div>
        </div>
        <div id="receptiviti_text" class="r_component">
                <div id="receptiviti_snapshot" class="r_component">
                    <div class="r_title"><?php _e("Psychology Snapshot", RECEPTIVITI_PLUGIN_SLUG__);?></div>
                    <?php foreach ($this->snapshot as $summary=>$desc) {?>
                        <div class="r_snapshot">
                            <div class="r_summary"><?php echo $summary;?></div>
                            <div class="r_desc"><?php echo $desc;?></div>
                        </div>
                    <?php } ?>
                </div>
        </div>
        <div id="receptiviti_text" class="r_component">
                <div id="receptiviti_snapshot" class="r_component">
                    <div class="r_title"><?php _e("Communication Recommendations", RECEPTIVITI_PLUGIN_SLUG__);?></div>
                    <div class="r_summary"><?php echo $this->communication_recommendation;?></div>
                </div>
        </div>
        <div class="r_clear"></div>
    </div>
<?php
}
?>

</div>