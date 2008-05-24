<?php

class JWYSIWYG extends Plugin {

  /**
    * Required Plugin Information
    **/
  function info() {
    return array(
      'name' => 'JWYSIWYG',
      'license' => 'Apache License 2.0',
      'url' => 'http://habariproject.org/',
      'author' => 'Habari Community',
      'authorurl' => 'http://habariproject.org/',
      'version' => '0.1',
      'description' => 'Publish posts using the JWYSIWYG editor.',
      'copyright' => '2008'
    );
  }

  public function action_admin_header($theme) {
    if ( $theme->admin_page == 'publish' ) {
      Stack::add('admin_header_javascript', $this->get_url() . '/jwysiwyg/jquery.wysiwyg.js');
      Stack::add('admin_stylesheet', array($this->get_url() . '/jwysiwyg/jquery.wysiwyg.css', 'screen'));
    }
  }

  public function action_admin_footer($theme) {
    if ( $theme->admin_page == 'publish' ) {
      echo <<<JWYSIWYG
      <script type="text/javascript">
      $(function()
      {
        $('#content').wysiwyg();
      });
      habari.editor = {
        insertSelection: function(value) {
          var instance = $.data($('#content')[0], 'wysiwyg');
          instance.setContent(instance.getContent() + value);
        }
      }
      </script>
JWYSIWYG;
    }
  }


  public function action_update_check() {
    Update::add( 'JWYSIWYG', 'b5f0c17d-22e6-4d6c-8011-c79481d5efc7',  $this->info->version );
  }
}

?>
