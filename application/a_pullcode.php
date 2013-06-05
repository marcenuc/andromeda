<?php
/* ================================================================== *\
   (C) Copyright 2005 by Secure Data Software, Inc.
   This file is part of Andromeda
   
   Andromeda is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   Andromeda is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with Andromeda; if not, write to the Free Software
   Foundation, Inc., 51 Franklin St, Fifth Floor,
   Boston, MA  02110-1301  USA 
   or visit http://www.gnu.org/licenses/gpl.html
\* ================================================================== */
class a_pullcode extends x_table2 {
    function main() {
        $sql = "SELECT application, vcs_url, vcs_uid, vcs_pwd, a.vcs_type, coalesce(b.vcs_description, '') as vcs_description FROM applications a LEFT JOIN version_control_systems b on a.vcs_type=b.vcs_type ORDER BY application";
        $applications = SQL_AllRows($sql);
        $html = '<div class="hero-unit"><h2>Software Updates</h2></div>';
        $html .= '<table class="table table-bordered table-striped table-condensed table-hover">';
        $html .= '<thead>';
        $html .= '<tr><th>Application</th><th>Version Control</th><th style="text-align:center;">Current Version</th><th style="text-align:center;">Latest Version</th></tr>';
        $html .= '</thead>';
        if (!empty($applications)) {
            foreach( $applications as $application ) {
                $version = $this->getLatestVersion($application);
                $html .= '<tr>
                    <td>' .$application['application'] .'</td>
                    <td>' .( !empty($application['vcs_description']) ? $application['vcs_description'] : '<i>Not Set</i>' ) .'</td>
                    <td style="text-align:center;">' .$version['current'] .'</td>
                    <td style="text-align:center;' .($version['latest'] > $version['current'] ? 'background-color:green;color:#FFFFFF;font-weight:bold;' : '' )  .'">' .$version['latest'].'</td>
                </tr>';
            }
        } else {
            $html .= '<tr><td colspan="2"  style="text-align:center;"><i>No applications found</i></td></tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';

        echo $html;
    }

    function getLatestVersion($application) {
        $version = array(
            'current'=>'N/A',
            'latest'=>'N/A'
        );
        if (!empty($application['vcs_type'])) {
            $version['current'] = $this->getCurrentVersion($application);
            if ($application['vcs_type'] == 'svn') {
                $version['latest'] = $this->getSVNVersion($application);
            }
        }

        return $version;
    }

    function getCurrentVersion($application) {
        $apps = applicationVersions();
        $current = a($apps,$application['application'],array('vcs_url'=>''));
        $current = $current['local'];
    }

    function getSVNVersion($application) {
        $url = parse_url($application['vcs_url']);
        $vcs_url = $url['scheme'] .'://' .$application['vcs_uid'] .':' .$application['vcs_pwd'] .'@' .$url['host'] .$url['path'];

        $htmlVersions = file_get_contents($vcs_url);
        $matches =array();
        preg_match_all(
        '/<li><a href=.*\>(.*)<\/a><\/li>/'
        ,$htmlVersions
        ,$matches
        );
        $versions = ArraySafe($matches,1,array());
        $version = 'Unknown';
        if(count($versions)>0) {
            $latest = array_pop($versions);
            $latest = str_replace('/','',$latest);
            $version = $latest;
        }

        return $version;
    }
}
?>
