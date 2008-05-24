<?php
/*

  Photozou Silo

  Revision: $Id: photozousilo.plugin.php 206 2008-05-12 01:58:11Z ayunyan $
  Head URL: $URL: https://trac.habariproject.org/habari-extras/plugins/photozousilo/photozousilo.plugin.php $

*/
class PhotozouSilo extends Plugin implements MediaSilo
{
	const SILO_NAME= 'Photozou';

    /**
     * info
     *
     * @access public
     * @return array
     */
    function info()
    {
        return array(
          'name' => 'Photozou Silo',
          'version' => '0.01',
          'url' => 'http://ayu.commun.jp/',
          'author' => 'ayunyan',
          'authorurl' => 'http://ayu.commun.jp/',
          'license' => 'Apache License 2.0',
          'description' => 'Photozou silo (http://photozou.jp/)',
        );
    }

    /**
     * action: plugin_activation
     *
     * @access public
     * @param string $file
     */
    public function action_plugin_activation( $file )
    {
        if ( $file != $this->get_file() ) return;

        Options::set( 'photozousilo:username', '' );
        Options::set( 'photozousilo:password', '' );
    }

    /**
     * action: update_check
     *
     * @access public
     * @return void
     */
    public function action_update_check()
    {
        Update::add( 'Photozou Silo', 'a290a808-1fc2-11dd-b5d6-001b210f913f', $this->info->version );
    }

    /**
     * action: plugin_ui
     *
     * @access public
     * @param string $plugin_id
     * @param string $action
     * @return void
     */
    public function action_plugin_ui($plugin_id, $action)
    {
        if ( $plugin_id != $this->plugin_id() ) return;
        if ( $action == _t( 'Configure' ) ) {
            $ui = new FormUI( strtolower( get_class( $this ) ) );
            $username = $ui->add( 'text', 'username', _t('Photozou Username: ') );
            $password = $ui->add( 'text', 'password', _t('Photozou Password: ') );
            $ui->on_success( array( $this, 'updated_config' ) );
            $ui->out();
        }
    }

    /**
     * FormUI callback
     *
     * @access public
     * @return boolean
     */
    public function updated_config($ui)
    {
        return true;
    }

    /**
     * actuin: admin_footer
     *
     * @access public
     * @param string $theme
     * @return void
     */
	public function action_admin_footer( $theme )
    {
        if ( $theme->admin_page != 'publish' ) return;
?>
<script type="text/javascript">
habari.media.output.photozou = {
    display: function(fileindex, fileobj) {
        habari.editor.insertSelection('<a href="' + fileobj.photozou_url + '"><img src="' + fileobj.url + '" alt="' + fileobj.title + '" /></a>');
	}
}
</script>
<?php
	}

    /**
     * filter: plugin_config
     *
     * @access public
     * @return array
     */
    public function filter_plugin_config($actions, $plugin_id)
    {
        if ( $plugin_id == $this->plugin_id() ) {
            $actions[] = _t('Configure');
        }
        return $actions;
    }

    /**
     * silo info
     *
     * @access public
     * @return string
     */
    public function silo_info()
    {
        $photozou = new PhotozouAPI( Options::get('photozousilo:username'), Options::get('photozousilo:password') );
        if ( $photozou->nop() ) {
            return array(
                'name' => self::SILO_NAME
                );
        } else {
            Session::error( _t('Photozou Silo: Authencation Error') );
            return array();
        }
    }

    /**
     * silo dir
     *
     * @access public
     * @return
     */
    public function silo_dir($path)
    {
        $photozou = new PhotozouAPI( Options::get('photozousilo:username'), Options::get('photozousilo:password') );

        $paths = explode('/', $path);
        $results = array();

        if ( empty ( $paths[0] ) ) {
            $albums = $photozou->photo_album();
            @reset( $albums );
            while ( list( $album_id, $album_name ) = @each ( $albums ) ) {
                $results[] = new MediaAsset( self::SILO_NAME . '/albums/' . $album_id, true, array( 'title' => $album_name ) );
            }
        } else {
            list( $user_id, $album_id ) = explode( ':', $paths[1] );
            $photos = $photozou->photo_list_public( array( 'type' => 'album', 'album_id' => $album_id, 'user_id' => $user_id ) );

            if ( $photos === false ) return array();
            for ( $i = 0; $i < count( $photos ); $i++ ) {
                $props = array();
                $props['title'] = (string)$photos[$i]->photo_title;
                $props['url'] = (string)$photos[$i]->image_url;
                $props['thumbnail_url'] = (string)$photos[$i]->thumbnail_image_url;
                $props['photozou_url'] = (string)$photos[$i]->url;
                $props['filetype'] = 'photozou';
                $results[] = new MediaAsset( self::SILO_NAME . '/albums/albums/' . $user_id . ':' . $album_id . '/' . (string)$photos[$i]->photo_id, false, $props);
            }
        }

        return $results;
    }

    /**
     * silo get
     *
     * @access public
     */
    public function silo_get($path, $qualities = null)
    {
    }

    /**
     * silo_put
     *
     * @access public
     */
    public function silo_put($path, $filedata)
    {
        // TODO: built-in file uploading mechanism is not implemented?
    }

    /**
     * silo_url
     *
     * @access public
     * @param string $path
     * @param string $qualities
     */
    public function silo_url($path, $qualities = null)
    {
    }

    /**
     * silo_delete
     *
     * @access public
     * @param string $path
     */
    public function silo_delete($path)
    {
    }

    /**
     * silo highlights
     *
     * @access public
     */
    public function silo_highlights()
    {
    }

    /**
     * silo permissions
     *
     * @access public
     * @param string $path
     */
    public function silo_permissions($path)
    {
    }

    /**
     * silo contents
     *
     * @access public
     */
    public function silo_contents()
	{
    }
}

class PhotozouAPI
{
    var $base_url = 'http://api.photozou.jp/rest/';
    var $username;
    var $password;

    /**
     * PhotozouAPI: constructer
     *
     * @access public
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * PhotozouAPI: nop for authentication test
     *
     * @access public
     * @return boolean
     */
    public function nop()
    {
        $request = new RemoteRequest( $this->base_url . 'nop', 'GET' );
        $request->add_header( 'Authorization: Basic ' . rtrim( base64_encode( $this->username . ':' . $this->password ), '=' ) );
        $result = $request->execute();
        if ( $result !== true ) return false;
        return true;
    }

    /**
     * PhotozouAPI: photo_album
     *
     * @access public
     * @return object
     */
    public function photo_album()
    {
        $request = new RemoteRequest( $this->base_url . 'photo_album', 'GET' );
        $request->add_header( 'Authorization: Basic ' . rtrim( base64_encode( $this->username . ':' . $this->password ), '=' ) );
        $result = $request->execute();
        if ( $result !== true ) return false;

        $xml = simplexml_load_string( $request->get_response_body() );
        if ($xml['stat'] != 'ok') return false;

        $albums = array();
        for ( $i = 0; $i < count( $xml->info->album ); $i++ ) {
            $albums[ (string)$xml->info->album[$i]->user_id . ':' . (string)$xml->info->album[$i]->album_id ] = (string)$xml->info->album[$i]->name;
        }
        return $albums;
    }

    /**
     * PhotozouAPI: photo_list_public
     *
     * @access public
     * @param array $params
     * @return object
     */
    public function photo_list_public($params)
    {
        $param = array();
        @reset( $params );
        while ( list ( $name, $value ) = @each( $params ) ) {
            $param[] = $name . '=' . urlencode($value);
        }

        $request = new RemoteRequest( $this->base_url . 'photo_list_public?' . join( '&', $param ) , 'GET' );
        $result = $request->execute();
        if ( $result !== true ) return false;

        $xml = simplexml_load_string( $request->get_response_body() );
        if ($xml['stat'] != 'ok') return false;

        return $xml->info->photo;
    }
}
?>