<?php
	
//	include_once($api_path.'check_auth.php');
	/* Build By fhalipay */
/**
 * 淘宝API下载产品列表 class
 *
 * @package default
 * @author Batka
 */
class CallTaobao2 {
	
	function GetStoreItems($data)
	{
		$api_path = str_replace(array('web_tao\itemsofnick.php', 'web_tao/itemsofnick.php'), '', __FILE__);
		//echo $api_path.'config.php'; die();
		include($api_path.'config.php');
		//echo $appKey;die();
		include_once($api_path.'lib/functions.php');
		include_once($api_path.'lib/page.Class.php');
		//include_once($api_path.'lib/translator2.php');
		//print_r($data);die();
		//$seller_id = $this->get_user_id($data['keyword']);
		
		$search_type   = empty($data['search_type'])  ? 'keyword' : $data['search_type'];
		$cid           = empty($data['cid'])          ? '0' : intval($data['cid']);
		$parent_cid    = empty($data['parent_cid'])   ? '0' : intval($data['parent_cid']);
		$category_id   = empty($data['category_id'])  ? 0 : intval($data['category_id']);
		$start_price   = empty($data['start_price'])  ? '' : intval($data['start_price']);
		$end_price     = empty($data['end_price'])    ? '' : intval($data['end_price']);
		$state         = empty($data['state'])        ? '' : $data['state'];
		$start_score   = empty($data['start_score'])  ? '1' : $data['start_score'];
		$end_score     = empty($data['end_score'])    ? '20' : $data['end_score'];	  
		$order_by      = empty($data['order_by'])     ? '' : $data['order_by'];
		$page_no       = empty($data['page'])         ? '1' : intval($data['page']);
		$page_size     = empty($data['page_size'])    ? '10' : intval($data['page_size']);
		$nick		   = empty($data['keyword'])     ? '' : $data['keyword'];
		$keyword       = empty($data['keyword'])     ? '' : $data['keyword'];
		$product_id    = empty($data['product_id'])  ? '' : $data['product_id'];
		/*if ($keyword != '' && $data['language'] != '' && $data['language'] != 'cn' && $data['language'] != 'zh-CN' && $this->check_stringType($keyword) != 1)
		{
			$Translator = new Translator;
			$Translator->setText($keyword);
			$keyword = $Translator->translate($data['language'], 'zh-CN');
		}*/
		
		
		
			/* 获取指定类目或指定关键字淘宝商品列表 Start*/
			
			
		//参数数组
		$paramArr = array(
	
			/* API系统级输入参数 Start */
	
				'method' => 'taobao.taobaoke.items.relate.get',  //API名称
			 'timestamp' => date('Y-m-d H:i:s'),			
				'format' => 'xml',  //返回格式,本demo仅支持xml
			   'app_key' => $appKey,  //Appkey			
					 'v' => '2.0',   //API版本号		   
			'sign_method'=> 'md5', //签名方式			
	
			/* API系统级参数 End */				 
	
			/* API应用级输入参数 Start*/
	
				'fields' =>  'iid,num_iid,title,nick,pic_url,price,click_url,commission,commission_rate,commission_num,commission_volume,shop_click_url,seller_credit_score,item_location',  //返回字段
			 	'num_iid'     => $product_id,
			 	'relate_type' => 3,
			 	//'seller_id' => $seller_id

					
			/* API应用级输入参数 End*/
		);
		
		/*if ($search_type == 'keyword')  $paramArr['q'] = $keyword; //查询关键字	
		elseif ($search_type == 'shop') $paramArr['nick'] = $keyword; //查询店铺	*/
		
		$sign = createSign( $paramArr, $appSecret ); //生成签名

		$strParam  = createStrParam( $paramArr ); //组织参数
		$strParam .= 'sign=' . $sign . '&app_key=' . $appKey;
		$urls   = $url.$strParam; //构造Url

		//连接超时自动重试
		$cnt = 0;
		while ( $cnt < 3 && ( $result = @vita_get_url_content( $urls ) ) === FALSE ) $cnt++;

		$result = getXmlData( $result );//解析Xml数据

		//print_r($result);die();

		//var_export($result);die();
		if (!empty($result) && $result['total_results'] > 0)
		{
			//返回结果
			$$data['language']['titles'] = '';
			$TaobaoItems  = $result['taobaoke_items']['taobaoke_item'];
			
			if (isset($TaobaoItems['num_iid']))
			{
				$arr = $TaobaoItems;
				$TaobaoItems = array();
				$$data['language']['titles'] .= '<title>'.$arr['title'].'</title>';
				$TaobaoItems[0] = array(
									'product_id' => $arr['num_iid'],
									'nick'  => $arr['nick'],
									'image' => $arr['pic_url'],
									'price' => $arr['price'],
									'name'  => $arr['title'],
									'score'  => $arr['seller_credit_score'],
									'location_city' => $arr['item_location']
								   );
			}
			else
			{
				foreach ($TaobaoItems as $i => $arr)
				{
					$$data['language']['titles'] .= '<title>'.$arr['title'].'</title>';
					$TaobaoItems[$i] = array(
										'product_id' => $arr['num_iid'],
										'nick'  => $arr['nick'],
										'image' => $arr['pic_url'],
										'price' => $arr['price'],
										'name'  => $arr['title'],
										'score'  => $arr['seller_credit_score'],
										'location_city' => $arr['item_location']
									   );
				}
			}
			$Total_results = $result['total_results'] > 10240 ? 10240 : $result['total_results'];
			//$Sub_categories = $result['item_search']['item_categories']['item_category'];
			
			return array('', $TaobaoItems, $Total_results);
		}
		return array(array(), array(), 0);
	}

	function QueryItems($data)
	{
		$api_path = str_replace(array('web_tao\itemsofnick.php', 'web_tao/itemsofnick.php'), '', __FILE__);
		//echo $api_path.'config.php'; die();
		include($api_path.'config.php');
		//echo $appKey;die();
		include_once($api_path.'lib/functions.php');
		include_once($api_path.'lib/page.Class.php');
		//include_once($api_path.'lib/translator2.php');
		
		$search_type   = empty($data['search_type'])  ? 'keyword' : $data['search_type'];
		$cid           = empty($data['cid'])          ? '0' : intval($data['cid']);
		$parent_cid    = empty($data['parent_cid'])   ? '0' : intval($data['parent_cid']);
		$category_id   = empty($data['category_id'])  ? 0 : intval($data['category_id']);
		$start_price   = empty($data['start_price'])  ? '' : intval($data['start_price']);
		$end_price     = empty($data['end_price'])    ? '' : intval($data['end_price']);
		$state         = empty($data['state'])        ? '' : $data['state'];
		$start_score   = empty($data['start_score'])  ? '1' : $data['start_score'];
		$end_score     = empty($data['end_score'])    ? '20' : $data['end_score'];	  
		$order_by      = empty($data['order_by'])     ? '' : $data['order_by'];
		$page_no       = empty($data['page'])         ? '1' : intval($data['page']);
		$page_size     = empty($data['page_size'])    ? '10' : intval($data['page_size']);
	
		$keyword       = empty($data['keyword'])     ? '' : $data['keyword'];
		
		/*if ($keyword != '' && $data['language'] != '' && $data['language'] != 'cn' && $data['language'] != 'zh-CN' && $this->check_stringType($keyword) != 1)
		{
			$Translator = new Translator;
			$Translator->setText($keyword);
			$keyword = $Translator->translate($data['language'], 'zh-CN');
		}*/
		
		
		
			/* 获取指定类目或指定关键字淘宝商品列表 Start*/
			
			
		//参数数组
		$paramArr = array(
	
			/* API系统级输入参数 Start */
	
				'method' => 'taobao.sellercats.list.get',  //API名称
			 'timestamp' => date('Y-m-d H:i:s'),			
				'format' => 'xml',  //返回格式,本demo仅支持xml
			   'app_key' => $appKey,  //Appkey			
					 'v' => '2.0',   //API版本号		   
			'sign_method'=> 'md5', //签名方式			
	
			/* API系统级参数 End */				 
	
			/* API应用级输入参数 Start*/
	
				'fields' =>  'product_id,outer_id,created,cid,cat_name,props,props_str,name,binds,binds_str,sale_props,desc,pic_url,modified',  //返回字段
			 	'nick'     => '爱华仕旗舰店'
					
			/* API应用级输入参数 End*/
		);
		
		/*if ($search_type == 'keyword')  $paramArr['q'] = $keyword; //查询关键字	
		elseif ($search_type == 'shop') $paramArr['nick'] = $keyword; //查询店铺	*/
		
		$paramArr = array_filter($paramArr);
		
		$sign = createSign($paramArr, $appSecret);	//生成签名
		
		$strParam  = createStrParam($paramArr);	//组织参数		
		$strParam .= 'sign=' . $sign . '&app_key=' . $appKey;
		$urls   = $url.$strParam; //构造Url		
		$cnt = 0;	//连接超时自动重试
		
		while($cnt < 3 && ($result = vita_get_url_content($urls)) === FALSE) $cnt++;
		
		//解析Xml数据
		$result = getXmlData($result);
		//print_r($result);die();
		//var_export($result);die();
		if (!empty($result) && $result['total_results'] > 0)
		{
			//返回结果
			$$data['language']['titles'] = '';
			$TaobaoItems  = $result['item_search']['items']['item'];
			
			if (isset($TaobaoItems['num_iid']))
			{
				$arr = $TaobaoItems;
				$TaobaoItems = array();
				$$data['language']['titles'] .= '<title>'.$arr['title'].'</title>';
				$TaobaoItems[0] = array(
									'product_id' => $arr['num_iid'],
									'nick'  => $arr['nick'],
									'image' => $arr['pic_url'],
									'price' => $arr['price'],
									'name'  => $arr['title'],
									'score'  => $arr['seller_credit_score'],
									'location_city' => $arr['item_location']
								   );
			}
			else
			{
				foreach ($TaobaoItems as $i => $arr)
				{
					$$data['language']['titles'] .= '<title>'.$arr['title'].'</title>';
					$TaobaoItems[$i] = array(
										'product_id' => $arr['num_iid'],
										'nick'  => $arr['nick'],
										'image' => $arr['pic_url'],
										'price' => $arr['price'],
										'name'  => $arr['title'],
										'score'  => $arr['seller_credit_score'],
										'location_city' => $arr['item_location']
									   );
				}
			}
			$Total_results = $result['total_results'] > 10240 ? 10240 : $result['total_results'];
			$Sub_categories = $result['item_search']['item_categories']['item_category'];
			
			return array($Sub_categories, $TaobaoItems, $Total_results);
		}
		return array(array(), array(), 0);
	}
	//判断是否输入全英文 如果是则返回 1
	function check_stringType($str1)
	{
			$strA = trim($str1);
			$lenA = strlen($strA);
			$lenB = mb_strlen($strA, "utf-8");
			if ($lenA === $lenB)
			{
				return "1"; //全英文
			}
			else
			{
				if ($lenA % $lenB == 0)
				{
					return "2"; //全中文
				}
				else
				{
					return "3"; //中英混合
				}
		   }
	 }

	 function get_user_id($nick){
	 	$api_path = str_replace(array('web_tao\itemsofnick.php', 'web_tao/itemsofnick.php'), '', __FILE__);
		//echo $api_path.'config.php'; die();
		include($api_path.'config.php');
		//echo $appKey;die();
		include_once($api_path.'lib/functions.php');
		include_once($api_path.'lib/page.Class.php');
	 	//参数数组
		$paramArr = array(
	
			/* API系统级输入参数 Start */
	
				'method' => 'taobao.taobaoke.shops.convert',  //API名称
			 'timestamp' => date('Y-m-d H:i:s'),			
				'format' => 'xml',  //返回格式,本demo仅支持xml
			   'app_key' => $appKey,  //Appkey			
					 'v' => '2.0',   //API版本号		   
			'sign_method'=> 'md5', //签名方式			
	
			/* API系统级参数 End */				 
	
			/* API应用级输入参数 Start*/
	
				'fields' =>  'user_id,shop_title,click_url,commission_rate',  //返回字段
			 	'nick'     => $userNick,
			 	'seller_nicks' => $nick

					
			/* API应用级输入参数 End*/
		);
		
		/*if ($search_type == 'keyword')  $paramArr['q'] = $keyword; //查询关键字	
		elseif ($search_type == 'shop') $paramArr['nick'] = $keyword; //查询店铺	*/
		
		$paramArr = array_filter($paramArr);
		$sign = createSign($paramArr, $appSecret);	//生成签名
		$strParam  = createStrParam($paramArr);	//组织参数		
		$strParam .= 'sign=' . $sign . '&app_key=' . $appKey;
		$urls   = $url.$strParam; //构造Url		
		$cnt = 0;	//连接超时自动重试
		
		while($cnt < 3 && ($result = vita_get_url_content($urls)) === FALSE) $cnt++;
		
		//解析Xml数据
		$result = getXmlData($result);

		print_r($result);die();

		if($result['taobaoke_shops']['taobaoke_shop']['user_id']){
			return $result['taobaoke_shops']['taobaoke_shop']['user_id'];
		}else{
			return "";
		}
	 }
}
?>