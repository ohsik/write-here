<?php
/*
**  Date & Time input fields for front-end
    This function is modified from touch_time function in wp-admin/includes/template.php
    https://developer.wordpress.org/reference/functions/touch_time/
*/

function write_here_time( $edit = 1, $for_post = 1, $tab_index = 0, $multi = 0 ) {
    global $wp_locale, $comment;
    //$post = get_post();

    if ( $for_post )
        $edit = ! ( in_array($post->post_status, array('draft', 'pending') ) && (!$post->post_date_gmt || '0000-00-00 00:00:00' == $post->post_date_gmt ) );
 
    $tab_index_attribute = '';
    if ( (int) $tab_index > 0 )
        $tab_index_attribute = " tabindex=\"$tab_index\"";
 
    // todo: Remove this?
    // echo '<label for="timestamp" style="display: block;"><input type="checkbox" class="checkbox" name="edit_date" value="1" id="timestamp"'.$tab_index_attribute.' /> '.__( 'Edit timestamp' ).'</label><br />';
 
    $time_adj = current_time('timestamp');
    $post_date = gmdate("Y-m-d H:i:s", $time_adj);

    $jj = ($edit) ? mysql2date( 'd', $post_date, false ) : gmdate( 'd', $time_adj );
    $mm = ($edit) ? mysql2date( 'm', $post_date, false ) : gmdate( 'm', $time_adj );
    $aa = ($edit) ? mysql2date( 'Y', $post_date, false ) : gmdate( 'Y', $time_adj );
    $hh = ($edit) ? mysql2date( 'H', $post_date, false ) : gmdate( 'H', $time_adj );
    $mn = ($edit) ? mysql2date( 'i', $post_date, false ) : gmdate( 'i', $time_adj );
    $ss = ($edit) ? mysql2date( 's', $post_date, false ) : gmdate( 's', $time_adj );
 
    $cur_jj = gmdate( 'd', $time_adj );
    $cur_mm = gmdate( 'm', $time_adj );
    $cur_aa = gmdate( 'Y', $time_adj );
    $cur_hh = gmdate( 'H', $time_adj );
    $cur_mn = gmdate( 'i', $time_adj );
 
    $month = '<label><span class="screen-reader-text">' . __( 'Month' ) . '</span><select ' . ( $multi ? '' : 'id="mm" ' ) . 'name="mm"' . $tab_index_attribute . ">\n";
    for ( $i = 1; $i < 13; $i = $i +1 ) {
        $monthnum = zeroise($i, 2);
        $monthtext = $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) );
        $month .= "\t\t\t" . '<option value="' . $monthnum . '" data-text="' . $monthtext . '" ' . selected( $monthnum, $mm, false ) . '>';
        /* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
        $month .= sprintf( __( '%1$s-%2$s' ), $monthnum, $monthtext ) . "</option>\n";
    }
    $month .= '</select></label>';
 
    $day = '<label><span class="screen-reader-text">' . __( 'Day' ) . '</span><input type="text" ' . ( $multi ? '' : 'id="jj" ' ) . 'name="jj" value="' . $jj . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';
    $year = '<label><span class="screen-reader-text">' . __( 'Year' ) . '</span><input type="text" ' . ( $multi ? '' : 'id="aa" ' ) . 'name="aa" value="' . $aa . '" size="4" maxlength="4"' . $tab_index_attribute . ' autocomplete="off" /></label>';
    $hour = '<label><span class="screen-reader-text">' . __( 'Hour' ) . '</span><input type="text" ' . ( $multi ? '' : 'id="hh" ' ) . 'name="hh" value="' . $hh . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';
    $minute = '<label><span class="screen-reader-text">' . __( 'Minute' ) . '</span><input type="text" ' . ( $multi ? '' : 'id="mn" ' ) . 'name="mn" value="' . $mn . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';
 
    echo '<div class="timestamp-wrap">';
    /* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
    printf( __( '%1$s %2$s, %3$s @ %4$s:%5$s' ), $month, $day, $year, $hour, $minute );
 
    echo '</div><input type="hidden" id="ss" name="ss" value="' . $ss . '" />';
 
    if ( $multi ) return;
 
    echo "\n\n";
    $map = array(
        'mm' => array( $mm, $cur_mm ),
        'jj' => array( $jj, $cur_jj ),
        'aa' => array( $aa, $cur_aa ),
        'hh' => array( $hh, $cur_hh ),
        'mn' => array( $mn, $cur_mn ),
    );
    foreach ( $map as $timeunit => $value ) {
        list( $unit, $curr ) = $value;
 
        echo '<input type="hidden" id="hidden_' . $timeunit . '" name="hidden_' . $timeunit . '" value="' . $unit . '" />' . "\n";
        $cur_timeunit = 'cur_' . $timeunit;
        echo '<input type="hidden" id="' . $cur_timeunit . '" name="' . $cur_timeunit . '" value="' . $curr . '" />' . "\n";
    }
}


/*
**  Modified to get the published date of posts
    This function is modified from touch_time function in wp-admin/includes/template.php
    https://developer.wordpress.org/reference/functions/touch_time/
*/
function write_here_time_edit( $edit = 1, $for_post = 1, $tab_index = 0, $multi = 0 ) {
    global $wp_locale, $comment;
    
    // Get post ID from URL and assign it to 'get_post' to get post info 
    $post_id = $_REQUEST['post'];
    $post = get_post($post_id);
    
    if ( $for_post )
        $edit = ! ( in_array($post->post_status, array('draft', 'pending') ) && (!$post->post_date_gmt || '0000-00-00 00:00:00' == $post->post_date_gmt ) );
 
    $tab_index_attribute = '';
    if ( (int) $tab_index > 0 )
        $tab_index_attribute = " tabindex=\"$tab_index\"";
 
    // todo: Remove this?
    // echo '<label for="timestamp" style="display: block;"><input type="checkbox" class="checkbox" name="edit_date" value="1" id="timestamp"'.$tab_index_attribute.' /> '.__( 'Edit timestamp' ).'</label><br />';
    
    // Assign published date of posts instead of current time
    $time_adj = get_post_time('U', true, $post_id);
  
    $post_date = $post->post_date;
    $jj = ($edit) ? mysql2date( 'd', $post_date, false ) : gmdate( 'd', $time_adj );
    $mm = ($edit) ? mysql2date( 'm', $post_date, false ) : gmdate( 'm', $time_adj );
    $aa = ($edit) ? mysql2date( 'Y', $post_date, false ) : gmdate( 'Y', $time_adj );
    $hh = ($edit) ? mysql2date( 'H', $post_date, false ) : gmdate( 'H', $time_adj );
    $mn = ($edit) ? mysql2date( 'i', $post_date, false ) : gmdate( 'i', $time_adj );
    $ss = ($edit) ? mysql2date( 's', $post_date, false ) : gmdate( 's', $time_adj );
 
    $cur_jj = gmdate( 'd', $time_adj );
    $cur_mm = gmdate( 'm', $time_adj );
    $cur_aa = gmdate( 'Y', $time_adj );
    $cur_hh = gmdate( 'H', $time_adj );
    $cur_mn = gmdate( 'i', $time_adj );
 
    $month = '<label><span class="screen-reader-text">' . __( 'Month' ) . '</span><select ' . ( $multi ? '' : 'id="mm" ' ) . 'name="mm"' . $tab_index_attribute . ">\n";
    for ( $i = 1; $i < 13; $i = $i +1 ) {
        $monthnum = zeroise($i, 2);
        $monthtext = $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) );
        $month .= "\t\t\t" . '<option value="' . $monthnum . '" data-text="' . $monthtext . '" ' . selected( $monthnum, $mm, false ) . '>';
        /* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
        $month .= sprintf( __( '%1$s-%2$s' ), $monthnum, $monthtext ) . "</option>\n";
    }
    $month .= '</select></label>';
 
    $day = '<label><span class="screen-reader-text">' . __( 'Day' ) . '</span><input type="text" ' . ( $multi ? '' : 'id="jj" ' ) . 'name="jj" value="' . $jj . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';
    $year = '<label><span class="screen-reader-text">' . __( 'Year' ) . '</span><input type="text" ' . ( $multi ? '' : 'id="aa" ' ) . 'name="aa" value="' . $aa . '" size="4" maxlength="4"' . $tab_index_attribute . ' autocomplete="off" /></label>';
    $hour = '<label><span class="screen-reader-text">' . __( 'Hour' ) . '</span><input type="text" ' . ( $multi ? '' : 'id="hh" ' ) . 'name="hh" value="' . $hh . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';
    $minute = '<label><span class="screen-reader-text">' . __( 'Minute' ) . '</span><input type="text" ' . ( $multi ? '' : 'id="mn" ' ) . 'name="mn" value="' . $mn . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';
 
    echo '<div class="timestamp-wrap">';
    /* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
    printf( __( '%1$s %2$s, %3$s @ %4$s:%5$s' ), $month, $day, $year, $hour, $minute );
 
    echo '</div><input type="hidden" id="ss" name="ss" value="' . $ss . '" />';
 
    if ( $multi ) return;
 
    echo "\n\n";
    $map = array(
        'mm' => array( $mm, $cur_mm ),
        'jj' => array( $jj, $cur_jj ),
        'aa' => array( $aa, $cur_aa ),
        'hh' => array( $hh, $cur_hh ),
        'mn' => array( $mn, $cur_mn ),
    );
    foreach ( $map as $timeunit => $value ) {
        list( $unit, $curr ) = $value;
 
        echo '<input type="hidden" id="hidden_' . $timeunit . '" name="hidden_' . $timeunit . '" value="' . $unit . '" />' . "\n";
        $cur_timeunit = 'cur_' . $timeunit;
        echo '<input type="hidden" id="' . $cur_timeunit . '" name="' . $cur_timeunit . '" value="' . $curr . '" />' . "\n";
    }
}