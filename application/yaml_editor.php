<?php
    class yaml_editor extends x_table2 {
        function main() {
            echo( '
                <style type="text/css">
                #yamleditor {
                    width:100%;
                    border: 1px solid #DDD;
                    border-radius: 4px;
                    border-bottom-right-radius: 0px;
                }
                </style>
            ' );
            echo( '<script type="text/javascript" src="/appclib/ace/src-min-noconflict/ace.js" data-ace-base="/appclib/ace/src-min-noconflict" charset="utf-8"></script>');


            if ( isset( $_REQUEST['gp_file'] )  && isset( $_REQUEST['gp_app'] ) ) {
                $application=gp('gp_app');
                $appInfo = $this->getAppInfo($application);
                $parts = explode(DIRECTORY_SEPARATOR, $GLOBALS['AG']['dirs']['root']);
                array_pop($parts);
                array_pop($parts);
                $file = join(DIRECTORY_SEPARATOR, $parts) .DIRECTORY_SEPARATOR .trim($application) .DIRECTORY_SEPARATOR .'application' .DIRECTORY_SEPARATOR .gp('gp_file');
                $contents = file_get_contents($file);
                echo( '
                    <div class="btn-toolbar" style="clear:both;padding-bottom:10px;">
                        <div class="btn-group pull-left">
                            <span style="font-size:20pt;"><strong>Application: </strong>' .$application .' &nbsp;&nbsp;<strong>File: </strong>' .gp('gp_file') .(!empty( $appInfo['vcs_url'] ) ?  ' <strong>(READ ONLY)</strong>' : '' ) .'</span>
                        </div>
                ');
                if ( empty( $appInfo['vcs_url'] ) ) {
                    echo( '<div class="btn-group pull-right">
                            <a class="btn btn-primary" href="#"><i class="icon-ok icon-white"></i> Save</a>
                        </div>
                    ' );
                }
                echo( '
                        &nbsp;
                    </div>
                ');
                echo( '<div id="yamleditor">' .$contents .'</div>');
                echo( '
                    <div class="btn-toolbar" style="clear:both;padding-bottom:10px;">
                ');
                if ( empty( $appInfo['vcs_url'] ) ) {
                    echo( '<div class="btn-group pull-right">
                            <a class="btn btn-primary" href="#"><i class="icon-ok icon-white"></i> Save</a>
                        </div>
                    ' );
                }
                echo( '
                        &nbsp;
                    </div>
                ');
                JqDocReady('
                    editor = ace.edit(\'yamleditor\');
                    editor.setTheme(\'ace/theme/github\');
                    editor.setFontSize(14);
                    editor.setHighlightSelectedWord(true);
                    editor.setShowInvisibles(true);
                    editor.setShowPrintMargin(false);
                    editor.setAnimatedScroll(true);
                    editor.setBehavioursEnabled(true);
                    editor.setDisplayIndentGuides(true);
                    editor.getSession().setMode(\'ace/mode/yaml\');
                    ' .(!empty( $appInfo['vcs_url'] ) ?  'editor.setReadOnly(true);' : '' ) .'

                    heightUpdateFunction = function() {
                        var newHeight = $(window).height() -  ($(\'.navbar\').height() + 145);

                        $(\'#yamleditor\').height(newHeight.toString() + "px");

                        // This call is required for the editor to fix all of
                        // its inner structure for adapting to a change in size
                        editor.resize();
                    };
                    width = \'\';
                    height = \'\';
                    // Set initial size to match initial content
                    heightUpdateFunction();
                    setInterval(function () {
                        if ((width != $(window).width()) || (height != $(window).height())) {
                            width = $(window).width();
                            height = $(window).height();
                            heightUpdateFunction();
                        }
                    }, 300);
                ');
            } else {
                $this->showFileChooser();
            }
        }

        function getAppInfo($application) {
            $query = "SELECT * FROM " .ddView('applications') ." WHERE application='" .$application ."'";
            $data = SQL_OneRow( $query );
            return $data;
        }

        function showFileChooser() {
            $query = "SELECT * FROM " .ddView('applications') ." ORDER BY application";
            $applications = SQL_AllRows( $query );
            foreach( $applications as $application ) {
                echo( '<ul><strong>' .$application['application'] .'</strong>');
                $yaml_files = $this->getYAMLFiles($application['application']);
                foreach( $yaml_files as $yaml_file) {
                    echo( '<li><a href="/index.php?gp_page=yaml_editor&gp_app=' .trim($application['application']) .'&gp_file=' .$yaml_file .'">' .$yaml_file .'</a></li>');
                }
                echo( '</ul>' );
            }
        }

        function getYAMLFiles($application) {
            $files = array();
            $parts = explode(DIRECTORY_SEPARATOR, $GLOBALS['AG']['dirs']['root']);
            array_pop($parts);
            array_pop($parts);
            $path = join(DIRECTORY_SEPARATOR, $parts) .DIRECTORY_SEPARATOR .trim($application) .DIRECTORY_SEPARATOR .'application';
            if ($handle = opendir($path)) {
                while( false !== ($entry = readdir($handle))) {
                   if (!is_dir($entry)) {
                       if (stripos($entry, '.yaml') !== false) {
                           array_push($files, $entry);
                       }
                   }
                }
            }
            return $files;
        }
    }