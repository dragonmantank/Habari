<?php
class FreeStyle extends Plugin {
    function info() {
        return array(
            'url'            => 'http://iamgraham.net/plugins',
            'name'            => 'FreeStyle',
            'description'    => 'Allows you to inject arbitrary CSS into themes.',
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
                    $clientcode    = $ui->add('textarea', 'css', _t('FreeStyle CSS'));
                    
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
        return Options::get('freestyle:'.$var);
    }

    function action_template_header() {
        echo '<style type="text/css">'."\n";
        echo self::getvar('css')."\n";
        echo '</style>'."\n";
    }

}
?>

