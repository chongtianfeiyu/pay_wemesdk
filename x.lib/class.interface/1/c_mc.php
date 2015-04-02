<?php
class c_mc {

        public function __construct(){

                /* ini memcache
                 *
                 */
                ini_set('memcache.hash_function','crc32'        );
                ini_set('memcache.hash_strategy','consistent'   );

                /* current client object
                 *
                 */
                $this->m_o_mc   = null;

                /* all memcached svr
                 *
                 */
                $this->m_ar_mc  = array (
                                                array   ( 'host' => '0.0.0.0','port' => 11211, 'weight' => 10),
                                                array   ( 'host' => '0.0.0.0','port' => 11212, 'weight' => 20),
                                                array   ( 'host' => '0.0.0.0','port' => 11213, 'weight' => 70),
                                        );
        }
        private function mc_open(){

                $o = new Memcache();foreach($this->m_ar_mc as $v){$o->addServer($v["host"],$v["port"]);};$this->m_o_mc = $o;

                /*
                 *
                 */
                $this->mc_get_status();
        }
        private function mc_close(){

                $this->m_o_mc->close();unset($this->m_o_mc);$this->m_o_mc = null;
        }
        public function mc_set($key,$value){

                $this->mc_open();$this->m_o_mc->set($key,$value);$this->mc_close();
        }
        public function mc_get($key){

                $this->mc_open();$r=$this->m_o_mc->get($key);$this->mc_close();return $r;
        }
        private function mc_get_status(){

                var_dump($this->m_o_mc);
        }
}
?>