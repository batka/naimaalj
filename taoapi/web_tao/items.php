<?php

// include_once($api_path.'check_auth.php');
/* Build By fhalipay */
/**
 * 淘宝API下载产品列表 class
 *
 * @package default
 * @author Batka
 */
class CallTaobao {

	function Items( $data ) {
		$api_path = str_replace( array( 'web_tao\items.php', 'web_tao/items.php' ), '', __FILE__ );

		include_once $api_path.'config.php';
		include_once $api_path.'lib/functions.php';
		include_once $api_path.'lib/page.Class.php';
		include_once $api_path.'lib/translator2.php';

		$search_type   = empty( $data['search_type'] )  ? 'keyword' : $data['search_type'];
		$cid           = empty( $data['cid'] )          ? '0' : intval( $data['cid'] );
		$parent_cid    = empty( $data['parent_cid'] )   ? '0' : intval( $data['parent_cid'] );
		$category_id   = empty( $data['category_id'] )  ? 0 : intval( $data['category_id'] );
		$start_price   = empty( $data['start_price'] )  ? '' : intval( $data['start_price'] );
		$end_price     = empty( $data['end_price'] )    ? '' : intval( $data['end_price'] );
		$state         = empty( $data['state'] )        ? '' : $data['state'];
		$start_score   = empty( $data['start_score'] )  ? '1' : $data['start_score'];
		$end_score     = empty( $data['end_score'] )    ? '20' : $data['end_score'];
		$order_by      = empty( $data['order_by'] )     ? '' : $data['order_by'];
		$page_no       = empty( $data['page'] )         ? '1' : intval( $data['page'] );
		$page_size     = empty( $data['page_size'] )    ? '10' : intval( $data['page_size'] );

		$keyword       = empty( $data['keyword'] )     ? '' : $data['keyword'];

		if ( $keyword != '' && $data['language'] != '' && $data['language'] != 'cn' && $data['language'] != 'zh-CN' && $this->check_stringType( $keyword ) != 1 ) {
			$Translator = new Translator;
			$Translator->setText( $keyword );
			$keyword = $Translator->translate( $data['language'], 'zh-CN' );
		}

		$paramArr = array(
			'search_type' => $search_type, //查询类型
			'keyword' => $keyword,     //查询关键字
			'category_id' => $category_id, //本站商品分类
			'cid' => $cid,         //商品所属分类id
			'state' => $state,       //商品所属分类id
			'start_price' => $start_price, //起始价格
			'end_price' => $end_price,   //最高价格
			'start_score' => $start_score, //店铺等级
			'end_score' => $end_score,   //店铺等级
			'order_by' => $order_by,    //排序方式
			'page_size' => $page_size ,  //每页返回结果数.最大每页40
		);
		$strPageParam  = createStrParam( $paramArr ); //组织分页参数
		$paramArr = array();

		/* 获取指定类目或指定关键字淘宝商品列表 Start*/


		//参数数组
		$paramArr = array(

			/* API系统级输入参数 Start */

			'method' => 'taobao.taobaoke.items.get',  //API名称
			'timestamp' => date( 'Y-m-d H:i:s' ),
			'format' => 'xml',  //返回格式,本demo仅支持xml
			'app_key' => $appKey,  //Appkey
			'v' => '2.0',   //API版本号
			'sign_method'=> 'md5', //签名方式

			/* API系统级参数 End */

			/* API应用级输入参数 Start*/

			/* API应用级输入参数 Start*/

	    	'fields' =>  'iid,num_iid,title,nick,pic_url,price,click_url,commission,commission_rate,commission_num,commission_volume,shop_click_url,seller_credit_score,item_location',  //返回字段
	    	  'nick' => $userNick,  //推广者淘宝昵称 
   	       'keyword' => $keyword,  //查询关键字	
	    	   'cid' => $cid,  //商品所属分类id 		   
	   'start_price' => $start_price, //起始价格
	     'end_price' => $end_price, //最高价格
		 'auto_send' => $auto_send, //是否自动发货
		 	  'area' => $area, //商品所在地 例如:杭州市
	  'start_credit' => $start_credit, //卖家起始信用
	    'end_credit' => $end_credit,  //卖家最高信用
	   		  'sort' => $sort, //排序方式
		 'guarantee' => $guarantee, //查询是否消保卖家
'start_commissionRate'=>$start_commissionRate, //起始佣金比率选项
 'end_commissionRate'=> $end_commissionRate, //最高佣金比率
'start_commissionNum'=> $start_commissionNum, //起始累计推广量选项
 'end_commissionNum' => $end_commissionNum, //最高累计推广量
		   'page_no' => $page_no, //结果页数.1~99
  	     'page_size' => $page_size , //每页返回结果数.最大每页40 
			/* API应用级输入参数 End*/
		);

		if ( $search_type == 'keyword' )  $paramArr['q'] = $keyword; //查询关键字
		elseif ( $search_type == 'shop' ) $paramArr['nicks'] = $keyword; //查询店铺

		$paramArr = array_filter( $paramArr );

		$sign = createSign( $paramArr, $appSecret ); //生成签名

		$strParam  = createStrParam( $paramArr ); //组织参数
		$strParam .= 'sign=' . $sign . '&app_key=' . $appKey;
		$urls   = $url.$strParam; //构造Url
		$cnt = 0; //连接超时自动重试

		while($cnt < 3 && ($result = @vita_get_url_content($urls)) === FALSE) $cnt++;

		//解析Xml数据
		$result = getXmlData( $result );

		$sub_msg = isset($result['sub_msg']) ? $result['sub_msg'] : FALSE;
		if(!$TaobaokeItem = $result['taobaoke_items']['taobaoke_item']) return FALSE;
		//echo "testing";
		$TaobaokeCount = $result['total_results'];
		//print_r($result);
		//var_export($result);die();
		if ( !empty( $result ) && $TaobaokeCount > 0 ) {
			//返回结果
			$$data['language']['titles'] = '';
			$TaobaoItems  = $TaobaokeItem;

			if ( isset( $TaobaoItems['num_iid'] ) ) {
				$arr = $TaobaoItems;
				$TaobaoItems = array();
				$$data['language']['titles'] .= '<title>'.$arr['title'].'</title>';
				$TaobaoItems[0] = array(
					'product_id' => $arr['num_iid'],
					'nick'  => $arr['nick'],
					'image' => $arr['pic_url'],
					'price' => $arr['price'],
					'name'  => $arr['title'],
					'score'  => $arr['seller_credit_score']
				);
			}
			else {
				foreach ( $TaobaoItems as $i => $arr ) {
					$$data['language']['titles'] .= '<title>'.$arr['title'].'</title>';
					$TaobaoItems[$i] = array(
						'product_id' => $arr['num_iid'],
						'nick'  => $arr['nick'],
						'image' => $arr['pic_url'],
						'price' => $arr['price'],
						'name'  => $arr['title'],
						'score'  => $arr['seller_credit_score']
					);
				}
			}
			$Total_results = $TaobaokeCount > 10240 ? 10240 : $TaobaokeCount;
			//$Sub_categories = $result['item_search']['item_categories']['item_category'];

			return array( '', $TaobaoItems, $Total_results );
		}
		return array( array(), array(), 0 );
	}
	function QueryItems( $data ) {
		$api_path = str_replace( array( 'web_tao\items.php', 'web_tao/items.php' ), '', __FILE__ );

		include_once $api_path.'config.php';
		include_once $api_path.'lib/functions.php';
		include_once $api_path.'lib/page.Class.php';
		include_once $api_path.'lib/translator2.php';

		$search_type   = empty( $data['search_type'] )  ? 'keyword' : $data['search_type'];
		$cid           = empty( $data['cid'] )          ? '0' : intval( $data['cid'] );
		$parent_cid    = empty( $data['parent_cid'] )   ? '0' : intval( $data['parent_cid'] );
		$category_id   = empty( $data['category_id'] )  ? 0 : intval( $data['category_id'] );
		$start_price   = empty( $data['start_price'] )  ? '' : intval( $data['start_price'] );
		$end_price     = empty( $data['end_price'] )    ? '' : intval( $data['end_price'] );
		$state         = empty( $data['state'] )        ? '' : $data['state'];
		$start_score   = empty( $data['start_score'] )  ? '1' : $data['start_score'];
		$end_score     = empty( $data['end_score'] )    ? '20' : $data['end_score'];
		$order_by      = empty( $data['order_by'] )     ? '' : $data['order_by'];
		$page_no       = empty( $data['page'] )         ? '1' : intval( $data['page'] );
		$page_size     = empty( $data['page_size'] )    ? '10' : intval( $data['page_size'] );

		$keyword       = empty( $data['keyword'] )     ? '' : $data['keyword'];

		if ( $keyword != '' && $data['language'] != '' && $data['language'] != 'cn' && $data['language'] != 'zh-CN' && $this->check_stringType( $keyword ) != 1 ) {
			$Translator = new Translator;
			$Translator->setText( $keyword );
			$keyword = $Translator->translate( $data['language'], 'zh-CN' );
		}

		$paramArr = array(
			'search_type' => $search_type, //查询类型
			'keyword' => $keyword,     //查询关键字
			'category_id' => $category_id, //本站商品分类
			'cid' => $cid,         //商品所属分类id
			'state' => $state,       //商品所属分类id
			'start_price' => $start_price, //起始价格
			'end_price' => $end_price,   //最高价格
			'start_score' => $start_score, //店铺等级
			'end_score' => $end_score,   //店铺等级
			'order_by' => $order_by,    //排序方式
			'page_size' => $page_size ,  //每页返回结果数.最大每页40
		);
		$strPageParam  = createStrParam( $paramArr ); //组织分页参数
		$paramArr = array();

		/* 获取指定类目或指定关键字淘宝商品列表 Start*/


		//参数数组
		$paramArr = array(

			/* API系统级输入参数 Start */

			'method' => 'taobao.items.search',  //API名称
			'timestamp' => date( 'Y-m-d H:i:s' ),
			'format' => 'xml',  //返回格式,本demo仅支持xml
			'app_key' => $appKey,  //Appkey
			'v' => '2.0',   //API版本号
			'sign_method'=> 'md5', //签名方式
			'partner_id' => 'top-apitools',

			/* API系统级参数 End */

			/* API应用级输入参数 Start*/

			'fields' =>  'detail_url,num_iid,title,nick,pic_url,cid,price,type,location.state,location.city,delist_time,post_fee,score,volume,is_prepay',  //返回字段
			'cid' => $cid,         //商品所属分类id
			'location.state' => $state,       //商品所属分类id
			'start_price' => $start_price, //起始价格
			'end_price' => $end_price,   //最高价格
			'start_score' => $start_score, //店铺等级
			'end_score' => $end_score,   //店铺等级
			'stuff_status' => 'new',        //只搜索新品
			'order_by' => $order_by,    //排序方式
			'page_no' => $page_no,     //结果页数.1~99
			'page_size' => $page_size ,  //每页返回结果数.最大每页40

			/* API应用级输入参数 End*/
		);

		if ( $search_type == 'keyword' )  $paramArr['q'] = $keyword; //查询关键字
		elseif ( $search_type == 'shop' ) $paramArr['nicks'] = $keyword; //查询店铺

		$paramArr = array_filter( $paramArr );

		$sign = createSign( $paramArr, $appSecret ); //生成签名

		$strParam  = createStrParam( $paramArr ); //组织参数
		$strParam .= 'sign=' . $sign . '&app_key=' . $appKey;
		$urls   = $url.$strParam; //构造Url
		$cnt = 0; //连接超时自动重试

		while($cnt < 3 && ($result = @vita_get_url_content($urls)) === FALSE) $cnt++;

		//解析Xml数据
		$result = getXmlData( $result );
		print_r($result);
		//var_export($result);die();
		if ( !empty( $result ) && $result['total_results'] > 0 ) {
			//返回结果
			$$data['language']['titles'] = '';
			$TaobaoItems  = $result['item_search']['items']['item'];

			if ( isset( $TaobaoItems['num_iid'] ) ) {
				$arr = $TaobaoItems;
				$TaobaoItems = array();
				$$data['language']['titles'] .= '<title>'.$arr['title'].'</title>';
				$TaobaoItems[0] = array(
					'product_id' => $arr['num_iid'],
					'nick'  => $arr['nick'],
					'image' => $arr['pic_url'],
					'price' => $arr['price'],
					'name'  => $arr['title'],
					'score'  => $arr['score']
				);
			}
			else {
				foreach ( $TaobaoItems as $i => $arr ) {
					$$data['language']['titles'] .= '<title>'.$arr['title'].'</title>';
					$TaobaoItems[$i] = array(
						'product_id' => $arr['num_iid'],
						'nick'  => $arr['nick'],
						'image' => $arr['pic_url'],
						'price' => $arr['price'],
						'name'  => $arr['title'],
						'score'  => $arr['score']
					);
				}
			}
			$Total_results = $result['total_results'] > 10240 ? 10240 : $result['total_results'];
			$Sub_categories = $result['item_search']['item_categories']['item_category'];

			return array( $Sub_categories, $TaobaoItems, $Total_results );
		}
		return array( array(), array(), 0 );
	}
	//判断是否输入全英文 如果是则返回 1
	function check_stringType( $str1 ) {
		$strA = trim( $str1 );
		$lenA = strlen( $strA );
		$lenB = mb_strlen( $strA, "utf-8" );
		if ( $lenA === $lenB ) {
			return "1"; //全英文
		}
		else {
			if ( $lenA % $lenB == 0 ) {
				return "2"; //全中文
			}
			else {
				return "3"; //中英混合
			}
		}
	}
}
?>
