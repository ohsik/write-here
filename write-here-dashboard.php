<?php
/*
**  Write Here 

    Dashboard, shows all posts by current user
    ------------------------------------------------------------------
*/
// Edit post function
function wh_edit_post_link($link = 'Edit this', $before = '', $after = '') {
    global $post;
    $editpage = 'edit-post'; // Edit page URL. This should be set up by site owner. Add this to plug in setting.
    $editLink = wp_nonce_url( get_bloginfo('url') . "/".$editpage."/?action=edit&post=" . $post->ID); 
    $htmllink = "<a href='" . $editLink . "'>".$link."</a>";
    echo $before . $htmllink . $after;
}

// Delete post function
function wh_delete_post_link($link = 'Delete this', $before = '', $after = '') {
    global $post;
    $message = "Are you sure you want to delete ".get_the_title($post->ID)." ?";
    $delLink = wp_nonce_url( get_bloginfo('url') . "/wp-admin/post.php?action=delete&post=" . $post->ID, 'delete-post_' . $post->ID);
    $htmllink = "<a href='" . $delLink . "' onclick = \"if ( confirm('".$message."' ) ) { return true; } return false;\"/>".$link."</a>";
    echo $before . $htmllink . $after;
}

function write_here_dashboard(){
    global $current_user;
    get_currentuserinfo();
    
    $page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
    $author_total_posts = count_user_posts($current_user->ID);

    $post_pp = 10; // Set number of posts per page
    
    $pages = ceil($author_total_posts/$post_pp);
    $offset = ($page * $post_pp) - $post_pp;
    
    $author_query = array(
        'posts_per_page' => $post_pp,
        'offset'=>  $offset,
        'author' => $current_user->ID
     );
    $author_posts = new WP_Query($author_query);
    
    // Show all posts by current user
    if($author_posts->have_posts()){
        echo '<div class="write-here-dashboard"><ul>';
        while($author_posts->have_posts()) : $author_posts->the_post();
        ?>
            <li>
                <p><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></p>
                <p class="wh-post-meta"><i><?php echo get_the_date(); ?></i> <span class="wh-edit"> <?php wh_edit_post_link('Edit', '', ''); ?> / <?php wh_delete_post_link('Delete', '', ''); ?></span></p>
            </li>
        <?php           
        endwhile;
        echo '</ul></div>';
        
        // Show pagination for the posts
        echo '<div class="wh-pagenavi">';
            $big = 999999999999; // need an unlikely integer
            $prev_arrow = is_rtl() ? '&rarr;' : '&larr;';
		    $next_arrow = is_rtl() ? '&larr;' : '&rarr;';
            
            $args = array(
                'base'         => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                'format'       => '?page=%#%',
                'total'        => $pages,
                'current'      => $page,
                'show_all'     => False,
                'end_size'     => 1,
                'mid_size'     => 3,
                'prev_next'    => True,
                'prev_text'		=> $prev_arrow,
				'next_text'		=> $next_arrow,
                'type'         => 'list');
                // ECHO THE PAGENATION 
            echo paginate_links( $args );
        echo '</div>';
        
        wp_reset_query();
    }else{
        echo '<p>Write your first post!</p>';
    }
}
?>