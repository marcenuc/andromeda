<?php
    $query = "SELECT * FROM applications";
    $rows = SQL_AllRows( $query );

    if (!empty($rows)) {
        foreach($rows as $row) {
            if ($row['flag_svn'] == 'Y') {
                $update = array(
                    'vcs_type'=>'svn',
                    'vcs_url'=>$row['svn_url'],
                    'vcs_uid'=>$row['svn_uid'],
                    'vcs_pwd'=>$row['svn_pwd'],
                    'skey'=>$row['skey']
                );
                SQL_Update('applications', $update);
            }
        }
    }
?>