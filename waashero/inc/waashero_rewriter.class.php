<?php
defined('ABSPATH') OR exit;

class Waashero_Rewriter
{
	
	var $excludes = null;
    var $_CDNURL = null;
    var $_ORIGIN_HOSTNAME = null;
    var $file_extensions = 'aac|css|eot|gif|jpeg|js|jpg|less|mp3|mp4|ogg|otf|pdf|png|ttf|woff|pdf|txt|webp|csv|swf|woff2|doc|ppt|tif|xls|docx|pptx|tiff|xlsx|ico';


	function __construct(array $excludes) {
        
        if($excludes){
            $this->excludes = implode("|", $excludes);
        }

        $this->_CDNURL = '//'.WAASHERO_CDN_HOSTNAME;
        $this->_ORIGIN_HOSTNAME = $_SERVER['HTTP_HOST'];

        if ( strpos( WAASHERO_CDN_HOSTNAME, 'waashero.com' ) === false) {
            $this->file_extensions = $this->file_extensions . '|svg';
        }
	}

    public function cdn_replace_urls( $matches ) {
       

        $cdnurl = $this->_CDNURL ;
        $origin_hostname = $this->_ORIGIN_HOSTNAME; 

     

        if( $this->excludes ) {          
                                
            if( preg_match('/('.preg_quote($this->excludes, "/").')/i', $matches[0])){
                return $matches[0];
            }           
        }

        

        if( preg_match( "/\.(".$this->file_extensions.")[\"\'\?\)\s]/i", $matches[0])){
         
        } else {
            if( preg_match( "/js/", $this->file_extensions ) ) {
                if( !preg_match("/\/revslider\/public\/assets\/js/", $matches[0])){
                    return $matches[0];
                }
            } else {
                return $matches[0];
            }
        }

      

       

       if(preg_match("/(data-product_variations|data-siteorigin-parallax)\=[\"\'][^\"\']+[\"\']/i", $matches[0])){
            $cdnurl = preg_replace("/(https?\:)?(\/\/)(www\.)?/", "", $cdnurl);
            $matches[0] = preg_replace("/(quot\;|\s)(https?\:)?(\\\\\/\\\\\/|\/\/)(www\.)?".$origin_hostname."/i", "$1$2$3".$cdnurl, $matches[0]);            
        }else if(preg_match("/\{\"concatemoji\"\:\"[^\"]+\"\}/i", $matches[0])){
            $matches[0] = preg_replace("/(http(s?)\:)?".preg_quote("\/\/", "/")."(www\.)?/i", "", $matches[0]);
            $matches[0] = preg_replace("/".preg_quote($origin_hostname, "/")."/i", $cdnurl, $matches[0]);
        }else if(isset($matches[2]) && preg_match("/".preg_quote($origin_hostname, "/")."/", $matches[2])){
            $matches[0] = preg_replace("/(http(s?)\:)?\/\/(www\.)?".preg_quote($origin_hostname, "/")."/i", $cdnurl, $matches[0]);
        }else if(isset($matches[2]) && preg_match("/^(\/?)(wp-includes|wp-content)/", $matches[2])){
            $matches[0] = preg_replace("/(\/?)(wp-includes|wp-content)/i", $cdnurl."/"."$2", $matches[0]);
        }else if(preg_match("/[\"\']https?\:\\\\\/\\\\\/[^\"\']+[\"\']/i", $matches[0])){
            if(preg_match("/^(logo|url|image)$/i", $matches[1])){
              
            }else{             
                $matches[0] = preg_replace("/\\\\\//", "/", $matches[0]);
                
                if(preg_match("/".preg_quote($origin_hostname, "/")."/", $matches[0])){
                    $matches[0] = preg_replace("/(http(s?)\:)?\/\/(www\.)?".preg_quote($origin_hostname, "/")."/i", $cdnurl, $matches[0]);
                    $matches[0] = preg_replace("/\//", "\/", $matches[0]);
                }
            }
        }

        return $matches[0];
    }




    public function rewrite( $html ) {

        $html = preg_replace_callback("/(srcset|src|href|data-vc-parallax-image|data-bg|data-fullurl|data-mobileurl|data-img-url|data-cvpsrc|data-cvpset|data-thumb|data-bg-url|data-large_image|data-lazyload|data-lazy|data-source-url|data-srcsmall|data-srclarge|data-srcfull|data-slide-img|data-lazy-original)\s{0,2}\=\s{0,2}[\'\"]([^\'\"]+)[\'\"]/i", array($this, 'cdn_replace_urls'), $html);
        $html = preg_replace_callback("/(url)\(([^\)\>]+)\)/i", array($this, 'cdn_replace_urls'), $html);       
        $html = preg_replace_callback("/\{\"concatemoji\"\:\"[^\"]+\"\}/i", array($this, 'cdn_replace_urls'), $html);
        $html = preg_replace_callback("/[\"\']([^\'\"]+)[\"\']\s*\:\s*[\"\']https?\:\\\\\/\\\\\/[^\"\']+[\"\']/i", array($this, 'cdn_replace_urls'), $html);      
        $html = preg_replace_callback("/(jsFileLocation)\s*\:[\"\']([^\"\']+)[\"\']/i", array($this, 'cdn_replace_urls'), $html);       
        $html = preg_replace_callback("/data-product_variations\=[\"\'][^\"\']+[\"\']/i", array($this, 'cdn_replace_urls'), $html);     
        $html = preg_replace_callback("/<object[^\>]+(data)\s{0,2}\=[\'\"]([^\'\"]+)[\'\"][^\>]+>/i", array($this, 'cdn_replace_urls'), $html);

        return $html;

    }
}