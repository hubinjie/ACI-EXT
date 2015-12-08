<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Token extends Front_Controller {
	
	function __construct()
	{
		parent::__construct();
		
		$this->load->library('weixin');
		$this->load->helper(array('auto_codeIgniter','global','array'));
	}


	
	function openID($id=0)
	{
		$id= intval($id);
		
		
		log_message('error',"comeon!!!");
		$echoStr = isset( $_GET["echostr"])?$_GET["echostr"]:"";
		
		if(trim($echoStr)!="")echo $echoStr;
		$signature = isset($_GET["signature"])?$_GET["signature"]:die();
        $timestamp = isset( $_GET["timestamp"])?$_GET["timestamp"]:die();
        $nonce = isset( $_GET["nonce"])?$_GET["nonce"]:die();
		
		$this->weixin->token = $token; //你的TOKEN
		if($this->weixin->checkSignature($signature,$timestamp,$nonce)){
        	echo $this->responseMsg($id);
        }else
		{
			log_message('error',"failure!");
		 	echo "why?";
		}
		
	}
	
	function responseMsg($mall_id=0)
	{
		log_message('error',"mall_id:".$mall_id);
		$postStr =  isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
	  	//extract post data
		if (!empty($postStr)){
                log_message('error',"postStr:here");

              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
				
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = safe_replace(trim($postObj->Content));
				$msgType= trim($postObj->MsgType);
				$Event= trim($postObj->Event);

				
                $time = time();
                $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";   
				
				
				//普通消息 
				if($Event =='subscribe')
                {
              		$msgType = "text";
					
					$where_sql = "keywords like '首次关注' and mall_id =" .$mall_id;
					$menuinfo_callback = $this->Weixin_response_model->get_one($where_sql);
					
					if($menuinfo_callback)
					{
							$menuinfo_callback['articles'] = unserialize($menuinfo_callback['articles']);
							
							
							if($menuinfo_callback['msgtype']=="news")
							{
								$textTpl = "<xml>
												<ToUserName><![CDATA[%s]]></ToUserName>
												<FromUserName><![CDATA[%s]]></FromUserName>
												<CreateTime>%s</CreateTime>
												<MsgType><![CDATA[%s]]></MsgType>
												<ArticleCount>%s</ArticleCount>
												<Articles>
												%s
												</Articles>
												</xml>"; 
							
								$menuItemTpl="";
								foreach($menuinfo_callback['articles'] as $k=>$v):
								
								$v['description'] = str_replace("{OPENID}",$fromUsername,$v['description']);
								$v['url'] = str_replace("{OPENID}",$fromUsername,$v['url']);
								$v['title'] = str_replace("{OPENID}",$fromUsername,$v['title']);
								
								
								$menuItemTpl .= "<item>
																	<Title><![CDATA[{$v['title']}]]></Title> 
																	<Description><![CDATA[{$v['description']}]]></Description>
																	<PicUrl><![CDATA[http://m.touchsoft.cn".trim($v['picUrl'])."]]></PicUrl>
																	<Url><![CDATA[{$v['url']}]]></Url>
																</item>";
								endforeach;
								
								$menuItemTpl = str_replace("{OPENID}",$fromUsername,$menuItemTpl);
								$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, "news",count($menuinfo_callback['articles']), $menuItemTpl);
								echo $resultStr;
							}else
							{
								$contentStr= $menuinfo_callback['articles']['content'];
								$contentStr = str_replace("{OPENID}",$fromUsername,$contentStr);
								$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
								echo $resultStr;
							}
					}else
					{
						
						$contentStr = "感谢您关注我们的官方微信";
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
						echo $resultStr;
					}
					
                }else{

					log_message('error',"msgType:".$msgType);
					//即自定义菜单事件推送
					if($msgType=="event")
					{
						$eventKey= trim($postObj->EventKey);
						
						//log_message('error',"eventKey:".$eventKey);

						
						$menu_id = intval(str_replace("M_E_N_U_","",$eventKey));

						if($menu_id)
						{
							log_message('error',"menu_id:".$menu_id);
							

						}else{


								
							//log_message('error',"resultStr: ".$resultStr);				
						}
					}elseif($msgType=="text")
					{
						
							$msgType = "text";
							log_message('error',"keyword:".$keyword);
							$where_sql = "keywords like '%".$keyword."%' and mall_id =" .$mall_id;
							
						
					}
					
                	
                }

        }else {
        	echo "";
        	exit;
        }
	}
	

}