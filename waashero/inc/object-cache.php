<?php  

function wp_cache_add($key, $data, $group = '', $expire = 0)
{
    global $wp_object_cache;

    return $wp_object_cache->add((string)$key, $data, trim((string)$group) ? : 'default', (int)$expire);
}

function wp_cache_close()
{
    global $wp_object_cache;

    return $wp_object_cache->close();
}

function wp_cache_decr($key, $offset = 1, $group = '')
{
    global $wp_object_cache;

    return $wp_object_cache->decr((string)$key, (int)$offset, trim((string)$group) ? : 'default');
}

function wp_cache_delete($key, $group = '')
{
    global $wp_object_cache;

    return $wp_object_cache->delete((string)$key, trim((string)$group) ? : 'default');
}

function wp_cache_flush()
{
    global $wp_object_cache;

    return $wp_object_cache->flush();
}

function wp_cache_get($key, $group = '', $force = false, &$found = null)
{
    global $wp_object_cache;

    return $wp_object_cache->get((string)$key, trim((string)$group) ? : 'default', (bool)$force, $found);
}

function wp_cache_incr($key, $offset = 1, $group = '')
{
    global $wp_object_cache;

    return $wp_object_cache->incr((string)$key, (int)$offset, trim((string)$group) ? : 'default');
}

function wp_cache_replace($key, $data, $group = '', $expire = 0)
{
    global $wp_object_cache;

    return $wp_object_cache->replace((string)$key, $data, trim((string)$group) ? : 'default', (int)$expire);
}

function wp_cache_set($key, $data, $group = '', $expire = 0)
{
    global $wp_object_cache;

    return $wp_object_cache->set((string)$key, $data, trim((string)$group) ? : 'default', (int)$expire);
}

function wp_cache_switch_to_blog($blog_id)
{
    global $wp_object_cache;

    $wp_object_cache->switch_to_blog((int)$blog_id);
}

function wp_cache_add_global_groups($groups)
{
    global $wp_object_cache;

    $wp_object_cache->add_global_groups((array)$groups);
}

function wp_cache_add_non_persistent_groups($groups)
{
    global $wp_object_cache;

    $wp_object_cache->add_non_persistent_groups((array)$groups);
}

function wp_cache_init()
{
    global $wp_object_cache;

    $wp_object_cache = new ApcuObjectCache();

}

class ApcuObjectCache
{

    protected $cache = [];
    protected $blogId;
    protected $isMultisite = false;
    protected $globalGroups = [];
    protected $nonPersistentGroups = [];
    protected $transientGroups = [];


    public function __construct()
    {


        $this->add_global_groups(array(
       'blog-details',
       'blog-id-cache',
       'blog-lookup',
       'global-posts',
       'networks',
       'rss',
       'sites',
       'site-details',
       'site-lookup',
       'site-options',
       'site-transient',
       'users',
       'useremail',
       'userlogins',
       'usermeta',
       'user_meta',
       'userslugs',
   ));

        $this->add_non_persistent_groups(array(
       'counts',
       'plugins'       
       ));

        $this->transientGroups['transient'] = true;
        $this->transientGroups['site-transient'] = true;


        // set up multisite environments
        if (is_multisite())
        {
            $this->set_multisite(true);
            $this->set_blog_id((int)get_current_blog_id());
            
        }

      

    }


    public function add(string $key, $data, string $group = '', int $expire = 0):
        bool
    {
        if (\wp_suspend_cache_addition())
        {
            return false;
        }

        
        $id = $this->create_unique_key($key, $group);

        if ($this->isCachedLocally($id))
        {
            return false;
        }



        if(\is_object($data)){
            $data = clone $data;
        }

        if ($this->isNonPersistentGroup($group))
        {
            $this->setLocalCache($id, $data);

            return true;
        }

        $result = (bool)$this->waashero_apcu_store_if_not_exist($id, $data, $expire);

        if ($result)
        {
            $this->setLocalCache($id, $data);
        }

        return $result;
    }

    public function close():
        bool
    {
        return true;
    }

    public function decr(string $key, int $offset = 1, string $group = '')
    {

        
        $id = $this->create_unique_key($key, $group);

        if ($this->isNonPersistentGroup($group))
        {
            if (!$this->isCachedLocally($id))
            {
                return false;
            }

            $value = $this->getLocalCache($id);
            $value = $this->decrement($value, $offset);

            $this->setLocalCache($id, $value);

            return $value;
        }

        $value = $this->waashero_apcu_get($id);

        if ($value == false)
        {
            return false;
        }

        $value = $this->decrement($value, $offset);
        $result = $this->waashero_apcu_set($id, $value, 300);

        if ($result)
        {
            $this->setLocalCache($id, $value);
        }

        return $value;
    }

    public function delete(string $key, string $group = ''):
        bool
    {

        if(isset($this->transientGroups[$group])){
            return $this->_transient_del($key,$group);
        }

        
        $id = $this->create_unique_key($key, $group);

        if ($this->isNonPersistentGroup($group))
        {
            if (!$this->isCachedLocally($id))
            {
                return false;
            }

            unset($this->cache[$id]);

            return true;
        }

        unset($this->cache[$id]);

        return (bool)$this->waashero_apcu_delete($id);
    }

    public function flush():
        bool
    {
        $this->cache = [];
        apcu_clear_cache();
        return true;
    }

    public function get(string $key, string $group = '', bool $force = false, &$found = null)
    {

        if(isset($this->transientGroups[$group])){
            return $this->_transient_get($key,$group);             
        }
        
        $id = $this->create_unique_key($key, $group);

        $is_cached_locally = $this->isCachedLocally($id);

        if ($this->isNonPersistentGroup($group))
        {
            if (!$is_cached_locally)
            {
                $found = false;

                return false;
            }

            $found = true;

            return $this->getLocalCache($id);
        }

        if ($is_cached_locally && !$force)
        {
            $found = true;

            return $this->getLocalCache($id);
        }

        $found = false;

        $data = $this->waashero_apcu_get($id);

        if ($data == false)
        {

            return false;
        }

        $found = true;

        if(\is_object($data)){
            $data = clone $data;
        }

        $this->setLocalCache($id, $data);

        return $data;
    }

    protected function getLocalCache(string $id)
    {
        if (\is_object($this->cache[$id]))
        {
            return clone $this->cache[$id];

        }
        else
        {
            return $this->cache[$id];
        }

    }

    protected function isCachedLocally(string $id)
    {
        return isset($this->cache[$id]);
    }

    public function incr(string $key, int $offset = 1, string $group = '')
    {

        
        $id = $this->create_unique_key($key, $group);

        if ($this->isNonPersistentGroup($group))
        {
            if (!$this->isCachedLocally($id))
            {
                return false;
            }

            $value = $this->getLocalCache($id);
            $value = $this->increment($value, $offset);

            $this->setLocalCache($id, $value);

            return $value;
        }

        $value = $this->waashero_apcu_get($id);

        if ($value == false)
        {
            return false;
        }

        $value = $this->increment($value, $offset);
        $result = $this->waashero_apcu_set($id, $value, 300);

        if ($result)
        {
            $this->setLocalCache($id, $value);
        }

        return $value;
    }

    public function replace(string $key, $data, string $group = '', int $expire = 0):
        bool
    {

        
        $id = $this->create_unique_key($key, $group);

        if(\is_object($data)){
            $data = clone $data;
        }

        if ($this->isNonPersistentGroup($group))
        {
            if (!$this->isCachedLocally($id))
            {
                return false;
            }

            $this->setLocalCache($id, $data);

            return true;
        }

        $exist = $this->waashero_apcu_exist($id);

        if ($exist == false)
        {
            return false;
        }

        $result = (bool)$this->waashero_apcu_set($id, $data, $expire);

        if ($result)
        {
            $this->setLocalCache($id, $data);
        }

        return $result;
    }

    public function set(string $key, $data, string $group = '', int $expire = 0):
        bool
    {
        if(isset($this->transientGroups[$group])){
            return $this->_transient_set($key,$data,$group,$expire);
        }
        
        $id = $this->create_unique_key($key, $group);

        if(\is_object($data)){
            $data = clone $data;
        }

        if ($this->isNonPersistentGroup($group))
        {
            $this->setLocalCache($id, $data);

            return true;
        }

        $result = (bool)$this->waashero_apcu_set($id, $data, $expire);

        if ($result)
        {
            $this->setLocalCache($id, $data);
        }

        return $result;
    }

    protected function setLocalCache(string $id, $data)
    {
        $this->cache[$id] = $data;

    }

    public function switch_to_blog(int $blog_id):
        bool
    {
        if ($this->isMultisite)
        {
            $this->set_blog_id($blog_id);

            return true;
        }

        return false;
    }

    //OTHER
    

    public function add_global_groups(array $groups):
        void
    {
        foreach ((array)$groups as $group) {
            $this->globalGroups[$group] = true;
        }
    }

    public function add_non_persistent_groups(array $groups):
        void
    {
        foreach ((array)$groups as $group) {
            $this->nonPersistentGroups[$group] = true;
        }
    }

    

    protected function decrement($value, int $offset) : int
    {
        if (!\is_integer($value))
        {
            $value = 0;
        }

        $value -= $offset;

        return max(0, $value);
    }

    

    protected function create_unique_key(string $key, string $group):
        string
    {

        if ($this->isMultisite) { 
            
            $prefix = isset($this->globalGroups[$group]) ? 'global' : $this->blogId;  

            return "{$prefix}-{$group}-{$key}";
        }else{
            return "{$group}-{$key}";
        }

    }

    protected function increment($value, int $offset):
        int
    {
        if (!is_integer($value))
        {
            $value = 0;
        }

        $value += $offset;

        return max(0, $value);
    }

    public function isPersistentGroup(string $group):
        bool
    {
        return !isset($this->nonPersistentGroups[$group]);           
    }

    public function isNonPersistentGroup(string $group):
        bool
    {
        return isset($this->nonPersistentGroups[$group]);           
    }

    

    public function set_blog_id(int $blogId):
        void
    {
        $this->blogId = $blogId;
    }

    public function set_multisite(bool $isMultisite):
        void
    {
        $this->isMultisite = $isMultisite;
    }

  

    public function isTransientGroup(string $group):
       bool
    {
        return isset($this->transientGroups[$group]);           
    }


    private function setTtl(int $ttl):
       int
    {
        
        if ($ttl < 0)
        {
            $ttl = 900;

        }else if($ttl > 3600){

            $ttl = 1800;
        }
        return $ttl;
    }

    //APCU
    function waashero_apcu_store_if_not_exist($key, $value, $ttl):
        bool
    {
        

        if (apcu_add($key, $value, $this->setTtl($ttl)))
        {
            return true;
        }

        return false;
    }

    function waashero_apcu_get($key)
    {


        $value = apcu_fetch($key, $result);

        if ($result)
        {
            return $value;
        }

        return false;
    }

    function waashero_apcu_set($key, $value, $ttl):
        bool
    {


        $result = apcu_store($key, $value, $this->setTtl($ttl));

        if ($result)
        {
            return true;
        }

        return false;
    }

    function waashero_apcu_delete($key):
        bool
    {

        if (apcu_delete($key))
        {
            return true;
        }

        return false;
    }

    function waashero_apcu_exist($key):
        bool
    {

        if (apcu_exists($key))
        {
            return true;
        }
        return false;
    }


    //TRANSIENT

    /**
     * Get transient from wp table
     *
     * @since 1.8.3
     * @access private
     * @see `wp-includes/option.php` function `get_transient`/`set_site_transient`
     */
    private function _transient_get( $transient, $group )
    {
        if ( $group == 'transient' ) {
            /**** Ori WP func start ****/
            $transient_option = '_transient_' . $transient;
            if ( ! wp_installing() ) {
                // If option is not in alloptions, it is not autoloaded and thus has a timeout
                $alloptions = wp_load_alloptions();
                if ( !isset( $alloptions[$transient_option] ) ) {
                    $transient_timeout = '_transient_timeout_' . $transient;
                    $timeout = get_option( $transient_timeout );
                    if ( false !== $timeout && $timeout < time() ) {
                        delete_option( $transient_option  );
                        delete_option( $transient_timeout );
                        $value = false;
                    }
                }
            }

            if ( ! isset( $value ) )
                $value = get_option( $transient_option );
            /**** Ori WP func end ****/
        }
        elseif ( $group == 'site-transient' ) {
            /**** Ori WP func start ****/
            $no_timeout = array('update_core', 'update_plugins', 'update_themes');
            $transient_option = '_site_transient_' . $transient;
            if ( ! in_array( $transient, $no_timeout ) ) {
                $transient_timeout = '_site_transient_timeout_' . $transient;
                $timeout = get_site_option( $transient_timeout );
                if ( false !== $timeout && $timeout < time() ) {
                    delete_site_option( $transient_option  );
                    delete_site_option( $transient_timeout );
                    $value = false;
                }
            }

            if ( ! isset( $value ) )
                $value = get_site_option( $transient_option );
            /**** Ori WP func end ****/
        }
        else {
            $value = false ;
        }

        return $value ;
    }

    /**
     * Set transient to WP table
     *
     * @since 1.8.3
     * @access private
     * @see `wp-includes/option.php` function `set_transient`/`set_site_transient`
     */
    private function _transient_set( $transient, $value, $group, $expiration ) : bool
    {
        if ( $group == 'transient' ) {
            /**** Ori WP func start ****/
            $transient_timeout = '_transient_timeout_' . $transient;
            $transient_option = '_transient_' . $transient;
            if ( false === get_option( $transient_option ) ) {
                $autoload = 'yes';
                if ( $expiration ) {
                    $autoload = 'no';
                    add_option( $transient_timeout, time() + $expiration, '', 'no' );
                }
                $result = add_option( $transient_option, $value, '', $autoload );
            } else {
                // If expiration is requested, but the transient has no timeout option,
                // delete, then re-create transient rather than update.
                $update = true;
                if ( $expiration ) {
                    if ( false === get_option( $transient_timeout ) ) {
                        delete_option( $transient_option );
                        add_option( $transient_timeout, time() + $expiration, '', 'no' );
                        $result = add_option( $transient_option, $value, '', 'no' );
                        $update = false;
                    } else {
                        update_option( $transient_timeout, time() + $expiration );
                    }
                }
                if ( $update ) {
                    $result = update_option( $transient_option, $value );
                }
            }
            /**** Ori WP func end ****/
        }
        elseif ( $group == 'site-transient' ) {
            /**** Ori WP func start ****/
            $transient_timeout = '_site_transient_timeout_' . $transient;
            $option = '_site_transient_' . $transient;
            if ( false === get_site_option( $option ) ) {
                if ( $expiration )
                    add_site_option( $transient_timeout, time() + $expiration );
                $result = add_site_option( $option, $value );
            } else {
                if ( $expiration )
                    update_site_option( $transient_timeout, time() + $expiration );
                $result = update_site_option( $option, $value );
            }
            /**** Ori WP func end ****/
        }
        else {
            $result = false ;
        }

        return $result ;
    }

    /**
     * Delete transient from WP table
     *
     * @since 1.8.3
     * @access private
     * @see `wp-includes/option.php` function `delete_transient`/`delete_site_transient`
     */
    private function _transient_del( $transient, $group ) : bool
    {
        if ( $group == 'transient' ) {
            /**** Ori WP func start ****/
            $option_timeout = '_transient_timeout_' . $transient;
            $option = '_transient_' . $transient;
            $result = delete_option( $option );
            if ( $result ){
                delete_option( $option_timeout );
                return true;
            }else{
                return false;
            }
            
            /**** Ori WP func end ****/
        }
        elseif ( $group == 'site-transient' ) {
            /**** Ori WP func start ****/
            $option_timeout = '_site_transient_timeout_' . $transient;
            $option = '_site_transient_' . $transient;
            $result = delete_site_option( $option );
            if ( $result ){
                delete_site_option( $option_timeout );
                return true;
            }else{
                return false;
            }
            
            /**** Ori WP func end ****/
        }
    }
}