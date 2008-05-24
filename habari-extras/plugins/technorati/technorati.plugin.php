<?php
class Technorati extends Plugin {

        /* Technorati API key
         * You MUST change this have your Technorati API key
         * which can be found on http://technorati.com/developers/apikey.html
         *
         */

        /* Required Plugin Informations */
        public function info() {
                return array(
                        'name' => 'Technorati',
                        'version' => '0.2',
                        'url' => 'http://habariproject.org/',
                        'author' =>     'Habari Community',
                        'authorurl' => 'http://habariproject.org/',
                        'license' => 'Apache License 2.0',
                        'description' => 'Technorati plugin for Habari',
                        'copyright' => '2007'
                );
        }

        public function filter_plugin_config( $actions, $plugin_id )
        {
                if ( $plugin_id == $this->plugin_id() ) {
                        $actions[] = 'Configure';
                }

                return $actions;
        }

        public function action_plugin_ui( $plugin_id, $action )
        {
                if ( $plugin_id == $this->plugin_id() ) {
                        switch ( $action ) {
                                case 'Configure' :
                                        $ui = new FormUI( strtolower( get_class( $this ) ) );
                                        $technorati_apikey= $ui->add( 'text', 'apikey', 'Technorati API Key:' );
                                        $ui->on_success( array( $this, 'updated_config' ) );
                                        $ui->out();
                                break;
                        }
                }
        }

        public function updated_config( $ui )
        {
                return true;
        }






       public function filter_statistics_summary( $technorati_stats ) {

         $technorati_url= 'http://api.technorati.com/bloginfo?key=' . Options::get( 'technorati:apikey' ) . '&url='. Site::get_url('habari');
         
         $response= RemoteRequest::get_contents( $technorati_url );
         $xml= new SimpleXMLElement($response); 
                           
         $technorati_inbound_blogs= ($xml->document->result->weblog->inboundblogs[0]);
         $technorati_inbound_links= ($xml->document->result->weblog->inboundlinks[0]);
         $technorati_rank= ($xml->document->result->weblog->rank[0]);

         $technorati_stats['Rank']= $technorati_rank;
         $technorati_stats['Inbound Links']= $technorati_inbound_links;
         $technorati_stats['Inbound Blogs']= $technorati_inbound_blogs;
         return $technorati_stats;
         }
}
?>
