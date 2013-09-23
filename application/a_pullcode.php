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
        $sql = "SELECT application, vcs_url, vcs_uid, vcs_pwd, a.vcs_type, coalesce(b.vcs_description, '') as vcs_description FROM " .ddView('applications' ) ." a LEFT JOIN " .ddView('version_control_systems') ." b on a.vcs_type=b.vcs_type ORDER BY application";
        $applications = SQL_AllRows($sql);
        $html = '<div class="hero-unit"><h2>Application Updates</h2></div>';
        $html .= '<table class="table table-bordered table-striped table-condensed table-hover">';
        $html .= '<thead>';
        $html .= '<tr><th>Application</th><th>Version Control</th><th style="text-align:center;">Current Version</th><th style="text-align:center;">Latest Version</th><th style="text-align:right;">Options</th></tr>';
        $html .= '</thead>';
        if (!empty($applications)) {
            foreach( $applications as $application ) {
                $version = $this->getLatestVersion($application);
                $class = ($version['latest'] > $version['current'] ? 'info' : '' );
                $html .= '<tr class="' .$class .'">
                    <td>' .$application['application'] .'</td>
                    <td>' .( !empty($application['vcs_description']) ? $application['vcs_description'] : '<i>Not Set</i>' ) .'</td>
                    <td style="text-align:center;">' .$version['current'] .'</td>
                    <td style="text-align:center;">' .$version['latest'].'</td>
                    <td style="text-align:right;">' .($class === 'info' ? $this->getUpdateLink($application) : '') .'</td>
                </tr>';
            }
        } else {
            $html .= '<tr><td colspan="2"  style="text-align:center;"><i>No applications found</i></td></tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';

        echo $html;
    }

    function getUpdateLink($application) {
        $link = '';
        if ($application['vcs_type'] === 'svn') {
            $link = '<a data-title="Upgrade Application: ' .trim($application['application']) .'" data-controls-modal="modal-create" class="iframe-modal" data-toggle="modal" href="" data-url="' .(!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') .$_SERVER['SERVER_NAME'] .'/index.php?gp_page=a_pullsvn&amp;svnpull=1&amp;gp_out=success&amp;app=' .str_replace(' ', '', $application['application']) .'">Upgrade Now</a>';
        }

        return $link;
    }

    function getLatestVersion($application) {
        $version = array(
            'current'=>'N/A',
            'latest'=>'N/A'
        );
        if (!empty($application['vcs_type'])) {
            $version['current'] = self::getCurrentVersion($application);
            if ($application['vcs_type'] == 'svn') {
                $version['latest'] = self::getSVNVersion($application);
            } else if ($application['vcs_type'] == 'git') {
                $version['latest'] = self::getGitVersion($application);
            }
        }

        return $version;
    }

    function getCurrentVersion($application) {
        $apps = applicationVersions();
        $appName = trim($application['application']);
        $current = a($apps, $appName,array('vcs_url'=>''));
        $current['local'];
        return $current['local'];
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

    function getGitVersion($application) {
        $version = '';
        $gitcmd = 'git ls-remote --tags ' .$application['vcs_url'] .' release-*';
        exec($gitcmd, $tags);
        if (!empty($tags)) {
            for($i=(count($tags) - 1);$i>=0;$i--) {
                if (substr($tags[$i], -3) !== '^{}') {
                    if (preg_match('/\/(?P<version>release-.+)/i', $tags[$i], $matches) > 0) {
                        $version = $matches['version'];
                    }
                }
            }
        }

        return $version;
    }
}
?>
