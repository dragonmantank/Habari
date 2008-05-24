<?php
class FreeStyleAdmin extends Plugin {
    function info() {
        return array(
            'url'            => 'http://iamgraham.net/plugins',
            'name'            => 'FreeStyleAdmin',
            'description'    => 'Allows you to load arbitrary CSS files into the admin pages.',
            'license'        => 'Apache License 2.0',
            'author'        => 'Graham Christensen',
            'authorurl'        => 'http://iamgraham.net/',
            'version'        => '0.1'
        );
    }

    public function filter_plugin_config($actions, $plugin_id) {
        if ($plugin_id == $this->plugin_id()) {
            $actions[] = _t('Configure');
        }
        return $actions;
    }

    public function action_plugin_ui($plugin_id, $action) {

        if ($plugin_id == $this->plugin_id()) {
            switch ($action) {
                case _t('Configure'):
                    $ui = new FormUI(strtolower(get_class($this)));
                    $clientcode    = $ui->add('text', 'css_location', _t('FreeStyle CSS Location'));
                    
                    $ui->on_success(array($this, 'updated_config'));
                    $ui->out();
                break;
            }
        }
    }

    public function updated_config($ui) {
        return true;
    }

    private static function getvar($var) {
        return Options::get('freestyleadmin:'.$var);
    }

    function action_admin_header() {
		if (self::getvar('css_location') != '') {
			Stack::add('admin_stylesheet', array(self::getvar('css_location'), 'screen'));
		}
    }

}
?>

