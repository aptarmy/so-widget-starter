<div class="apt">
   <?php
    $query_result = $this->get_posts($instance);
    if ($query_result->have_posts()) {?>
    <div class="woocommerce">
        <ul class="products">
            <?php while ($query_result->have_posts()): $query_result->the_post();?>
	                <?php wc_get_template_part('content', 'product');?>
	            <?php endwhile;?>
            <?php wp_reset_postdata();?>
        </ul>
    </div>
    <?php }?>

</div>