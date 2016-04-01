<?php
/*
**  Write Here 

    Dashboard, shows all posts by current user
    ------------------------------------------------------------------
*/
// Edit post function
function wh_edit_post_link($link = 'Edit', $before = '', $after = '') {
    global $post;
    
    // Get plug in options for Edit page URL
    $wh_option_values = get_option('write_here_options');
    $edit_page_id = $wh_option_values['pid_num'];

    $editLink = wp_nonce_url( get_bloginfo('url') . "/?p=".$edit_page_id."/?action=edit&post=" . $post->ID); 
    $htmllink = "<a href='" . $editLink . "'>".$link."</a>";
    echo $before . $htmllink . $after;
}

// Delete post function
function wh_delete_post_link($link = 'Delete', $before = '', $after = '') {
    global $post;
    
    $message = "Are you sure you want to delete ".get_the_title($post->ID)." ?";
    $delLink = wp_nonce_url( get_bloginfo('url') . "/wp-admin/post.php?action=delete&post=" . $post->ID, 'delete-post_' . $post->ID);
    $htmllink = "<a href='" . $delLink . "' onclick = \"if ( confirm('".$message."' ) ) { return true; } return false;\">".$link."</a>";
    echo $before . $htmllink . $after;
}

function wh_post_status($postsdb){
    if ($postsdb == "future"):
        $wp_ps = "Scheduled";
    elseif ($postsdb == "private"):
        $wp_ps = "Private";
    else:
        $wp_ps = "Published";
    endif;
    
    echo $wp_ps;
}

function write_here_dashboard(){
    global $current_user;
    wp_get_current_user();
    
    $page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
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
                    'type'         => 'list');
                    // ECHO THE PAGENATION 
                echo paginate_links( $args );
            echo '</div>';

            wp_reset_query();
        echo '</div>';
    }else{
        echo '<p class="write-first-post">Write your first post!</p>';
    }
}