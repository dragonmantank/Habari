<?php
class GoogleAds extends Plugin {
        function info() {
                return array(
                        'url'           => 'http://iamgraham.net/plugins',
                        'name'          => 'GoogleAds',
                        'license'       => 'Apache License 2.0',
                        'author'        => 'Graham Christensen',
                        'authorurl' => 'http://iamgraham.net/',
                        'version'       => '0.2'
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
                                        $clientcode     = $ui->add('text', 'clientcode', _t('Ad Client Code'));
                                        $adslot         = $ui->add('text', 'adslot', _t('Ad Slot ID'));
                                        $adwidth        = $ui->add('text', 'adwidth', _t('Ad Width'));
                                        $adheight       = $ui->add('text', 'adheight', _t('Ad Height'));
                                        
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
                return Options::get('googleads:'.$var);
        }

        function action_theme_sidebar_bottom() {
                $code = <<<ENDAD
<div class="sb-adsense">
<h2>Advertising</h2>
<p><script type="text/javascript"><!--
google_ad_client = "CLIENTCODE";
google_ad_slot = "ADSLOT";
google_ad_width = ADWIDTH;
google_ad_height = ADHEIGHT;
//--></script>
<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script></p>
</div>
ENDAD;
                $replace = array(
                        'CLIENTCODE'    => self::getvar('clientcode'),
                        'ADSLOT'                => self::getvar('adslot'),
                        'ADWIDTH'               => self::getvar('adwidth'),
                        'ADHEIGHT'              => self::getvar('adheight'));
                $code = str_replace(array_keys($replace), array_values($replace), $code);
        echo $code;
        }
}
?>
