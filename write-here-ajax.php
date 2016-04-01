<?php
/*
**  Write Here 

    AJAX APP. Write and Manage on the same page
    ------------------------------------------------------------------
*/
function write_here_ajax_dashboard() {
    check_ajax_referer( 'wh_obj_ajax', 'security' );
    
    // If page number exists
    if(isset($_GET['page'])){
        $page = $_GET['page'];
    }else{
        $page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
    }
    
    // Get current user info
    global $current_user;
    wp_get_current_user();
    $author_total_posts = count_user_posts($current_user->ID);
    
    $get_nop_setting = get_option('write_here_options');
    $post_pp = $get_nop_setting['num_of_posts'];
   
    $pages = ceil($author_total_posts/$post_pp);
    $offset = ($page * $post_pp) - $post_pp;
    
    $author_query = array(
        'posts_per_page' => $post_pp,
        'offset'=>  $offset,
        'post_status' => array('publish', 'future', 'private'),
        'author' => $current_user->ID
     );
    $author_posts = new WP_Query($author_query);

    // Show all posts by current user
    if($author_posts->have_posts()){
        echo '<div class="write-here-dashboard-wrap">';
            echo '<div class="author-total-posts">You have written '.$author_total_posts.' posts.</div>';
            echo '<div class="write-here-dashboard"><ul>';
            while($author_posts->have_posts()) : $author_posts->the_post();
                $postsdb = get_post_status();
            ?>
                <li>
                    <div class="wh-list">
                        <h2><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
                        <p class="wh-post-meta"><?php wh_post_status($postsdb); ?> on <i><?php echo get_the_date(); ?></i></p>
                    </div>
                    <div class="wh-edit">
                        <?php wh_edit_post_link('Edit', '', ''); ?>
                        <?php wh_delete_post_link('Delete', '', ''); ?>
                    </div>
                </li>
            <?php           
            endwhile;
            echo '</ul></div>';

            // Show pagination for the posts
            echo '<div class="wh-pagenavi">';
                $big = 999999999999; // need an unlikely integer

                $args = array(
                    'base'         => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                    'format'       => '?page=%#%',
                    'total'        => $pages,
                    'current'      => $page,
                    'show_all'     => False,
                    'end_size'     => 1,
                    'mid_size'     => 3,
                    'prev_next'    => false,
                    'type'         => 'list'
                );
                // ECHO THE PAGENATION 
                echo paginate_links( $args );
            echo '</div>';

            wp_reset_query();
        echo '</div>';
    }else{
        echo '<p class="write-first-post">Write your first post!</p>';
    }
    
    die();
}
add_action( 'wp_ajax_write_here_get_posts', 'write_here_ajax_dashboard' );