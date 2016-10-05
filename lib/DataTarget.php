<?php

namespace Cylex\Crawlers\Facebook;

use Opis\Database\Database;
use Opis\Database\Connection;

class DataTarget {
    
    protected $db;
    
    protected $table;
    
    protected $connection;
    
    protected $sessionID;
    
    protected $checked = false;

    protected $allowed = array(
        'FILIAL_NAME', 'BRAND_NAME', 'STORE_ID',
        'CITY', 'DISTRICT', 'ZIP',
        'ADDRESS', 'SPECIAL_ADDRESS', 'REGION',
        'STATE', 'FULL_ADDRESS', 'PHONE',
        'FAX', 'EMAIL', 'WEB',
        'COUNTRY', 'CATEGORY', 'CUI',
        'CAM_COMERT', 'CEO', 'DESCRIPTION',
        'GEO_LAT', 'GEO_LNG', 'OPENING_HOURS',
        'SHORTPROFILE', 'KEYWORDS', 'SOCIAL_MEDIA',
        'FACEBOOK', 'LINKEDIN', 'FOURSQUARE',
        'GOOGLEONE', 'TWITTER', 'YOUTUBE',
        'TUMBLR', 'FLICKR', 'PAYMENTMETHODS',
        'ACTIVE_SINCE', 'EMPLOYEES', 'SOCIAL_CAPITAL',
        'OFFICIAL_NAME', 'TRANSPORT_PARKING', 'FACILITIES',
        'SERVICES', 'PRODUCTS', 'BRANDS',
        'AREAS_SERVED', 'MAILING_ADDRESS', 'COMPANY_TYPE',
        'CONTACT_PERSON', 'DEPARTMENTS', 'MOBILE_PHONE',
        'LANGUAGES', 'CERTIFICATIONS', 'PAGE_SOURCE',
        'PAGELINK', 'STATUS', 'FIRNR' ,'FBID' ,'LANG','SESSIONID',
        'CMP_OVERVIEW' , 'FB_USERNAME' ,'AWARDS' , 'MISSION' ,
    );
    
    public function __construct(array $config, $sessionID)
    {
        $this->table = $config['table'];        
        $this->sessionID = $sessionID;
        
        $this->connection = new Connection($config['dsn'], $config['user'], $config['password']);
        $this->connection->persistent();
        $this->db = new Database($this->connection);
    }
    
    public function init()
    {
        //
    }
    
    protected function checkKeys(array $keys)
    {
        foreach($keys as $key)
        {
            if(!in_array(strtoupper($key), $this->allowed))
            {
                throw new e("Unknown table field $key");
            }
        }
        $this->checked = true;
    }
    
    public function save($data = array())
    {
        if(!$this->checked)
        {
            $this->checkKeys(array_keys($data));
        }
        
        $data = $this->cleanData($data);
		
		$keys = array_keys($data);
		$keys = '(`' . implode('`,`',$keys) .'`)';
		$value = "";
		$c = count($data);
		for($i = 0; $i < $c; $i++)
		{
			$value .= "?,";
		}
		
		$value = substr($value, 0, -1);
		$data = array_values($data);
		try{
            $this->connection->command("insert `". $this->table ."` ". $keys. "values(" .$value. ")" , $data);
        }
        catch (\Exception $e)
        {
            print_r($e->getMessage());
            echo PHP_EOL;
        }
    }
	
	public function cleanData(array $input, array $filter = null, $callback = null)
    {
        if($callback === null)
        {
            $callback = function($value){
                $value = trim(preg_replace('/\s\s+/', ' ', preg_replace('/\s/', ' ', $value)));
                //convert 4byte utf-8 to simple utf-8
                $value = iconv('utf-8', 'us-ascii//TRANSLIT', $value);
                return $value;
            };
        }
        
        if($filter !== null)
        {
            $input = array_intersect_key($input, array_flip($filter));
        }
        
        return array_map($callback, $input) + $input;
    }
    
}
