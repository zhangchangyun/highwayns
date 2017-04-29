﻿<?php
define('IN_HIGHWAY', true);
require_once(dirname(__FILE__).'/../../include/common.inc.php');
require_once(HIGHWAY_ROOT_PATH.'include/fun_wap.php');
require_once(HIGHWAY_ROOT_PATH.'include/fun_company.php');
require_once(HIGHWAY_ROOT_PATH.'include/mysql.class.php');
$smarty->cache = false;
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'index';
if ($_SESSION['uid']=='' || $_SESSION['username']==''||intval($_SESSION['utype'])==2)
{
	header("Location: ../wap_login.php");
}
elseif ($act == 'index')
{
	$smarty->cache = false;
	$company_info=get_company(intval($_SESSION['uid']));
	if(empty($company_info)){
		header("Location: ?act=company_info");
	}else{
		$smarty->assign('company_info',$company_info);
		$smarty->display("wap/company/wap-user-company-index.html");
	}
}
// 企业信息
elseif($act=="company_info")
{
	$smarty->cache = false;
	$company_info=get_company(intval($_SESSION['uid']));
	if($company_info){
		$company_info['contents'] = strip_tags(htmlspecialchars_decode($company_info['contents'],ENT_QUOTES));
	}
	$smarty->assign('company_info',$company_info);
	$smarty->display("wap/company/wap-com-info.html");
	
}
elseif($act=="company_info_save")
{
	$smarty->cache = false;
	$company_info=get_company(intval($_SESSION['uid']));
	$_POST=array_map("utf8_to_gbk", $_POST);
	$setsqlarr['uid']=intval($_SESSION['uid']);
	$setsqlarr['companyname']=trim($_POST['companyname'])?trim($_POST['companyname']):exit('企業名称を入力してください！');
	$setsqlarr['nature']=trim($_POST['nature'])?intval($_POST['nature']):exit('企業の性質を選択してください！');
	$setsqlarr['nature_cn']=trim($_POST['nature_cn']);
	$setsqlarr['trade']=trim($_POST['trade'])?intval($_POST['trade']):exit('業界を選択してください！');
	$setsqlarr['trade_cn']=trim($_POST['trade_cn']);
	$setsqlarr['district']=intval($_POST['district'])>0?intval($_POST['district']):exit('所属地区を選択してください！');
	$setsqlarr['sdistrict']=intval($_POST['sdistrict']);
	$setsqlarr['district_cn']=trim($_POST['district_cn']);
	if (intval($_POST['street'])>0)
	{
	$setsqlarr['street']=intval($_POST['street']);
	$setsqlarr['street_cn']=trim($_POST['street_cn']);
	}
	$setsqlarr['scale']=trim($_POST['scale'])?trim($_POST['scale']):exit('会社規模を選択してください！');
	$setsqlarr['scale_cn']=trim($_POST['scale_cn']);
	$setsqlarr['registered']=trim($_POST['registered']);
	$setsqlarr['currency']=trim($_POST['currency']);
	$setsqlarr['address']=trim($_POST['address'])?trim($_POST['address']):exit('連絡先を入力してください！');
	$setsqlarr['contact']=trim($_POST['contact'])?trim($_POST['contact']):exit('連絡者を入力してください！');
	$setsqlarr['telephone']=trim($_POST['telephone'])?trim($_POST['telephone']):exit('電話番号を入力してください！');
	$setsqlarr['email']=trim($_POST['email'])?trim($_POST['email']):exit('メールを入力してください！');
	$setsqlarr['website']=trim($_POST['website']);
	$setsqlarr['contents']=trim($_POST['contents'])?trim($_POST['contents']):exit('会社の紹介を入力してください！');
	
	
	$setsqlarr['contact_show']=1;
	$setsqlarr['email_show']=1;
	$setsqlarr['telephone_show']=1;
	$setsqlarr['address_show']=1;
		
	if ($_CFG['company_repeat']=="0")
	{
		$info=$db->getone("SELECT uid FROM ".table('company_profile')." WHERE companyname ='{$setsqlarr['companyname']}' AND uid<>'{$_SESSION['uid']}' LIMIT 1");
		if(!empty($info))
		{
			exit("{$setsqlarr['companyname']}已经存在，同公司信息不能重复注册");
		}
	}
	if ($company_info)
	{
			$_CFG['audit_edit_com']<>"-1"?$setsqlarr['audit']=intval($_CFG['audit_edit_com']):'';
			if ($db->updatetable(table('company_profile'), $setsqlarr," uid=$_SESSION[uid]"))
			{
				$jobarr['companyname']=$setsqlarr['companyname'];
				$jobarr['trade']=$setsqlarr['trade'];
				$jobarr['trade_cn']=$setsqlarr['trade_cn'];
				$jobarr['scale']=$setsqlarr['scale'];
				$jobarr['scale_cn']=$setsqlarr['scale_cn'];
				$jobarr['street']=$setsqlarr['street'];
				$jobarr['street_cn']=$setsqlarr['street_cn'];			
				if (!$db->updatetable(table('jobs'),$jobarr," uid=".$setsqlarr['uid']."")) exit('会社名修正エラー！');
				if (!$db->updatetable(table('jobs_tmp'),$jobarr," uid=".$setsqlarr['uid']."")) exit('会社名修正エラー！');
				$soarray['trade']=$jobarr['trade'];
				$soarray['scale']=$jobarr['scale'];
				$soarray['street']=$setsqlarr['street'];
				$db->updatetable(table('jobs_search_scale'),$soarray," uid=".$setsqlarr['uid']."");
				$db->updatetable(table('jobs_search_wage'),$soarray," uid=".$setsqlarr['uid']."");
				$db->updatetable(table('jobs_search_rtime'),$soarray," uid=".$setsqlarr['uid']."");
				$db->updatetable(table('jobs_search_stickrtime'),$soarray," uid=".$setsqlarr['uid']."");
				$db->updatetable(table('jobs_search_hot'),$soarray," uid=".$setsqlarr['uid']."");
				$db->updatetable(table('jobs_search_key'),$soarray," uid=".$setsqlarr['uid']."");
				unset($setsqlarr);
				write_memberslog($_SESSION['uid'],$_SESSION['utype'],8001,$_SESSION['username'],"修改企业资料");
				exit("1");
			}
			else
			{
				exit("保存失败！");
			}
	}
	else
	{
			$setsqlarr['audit']=intval($_CFG['audit_add_com']);
			$setsqlarr['addtime']=$timestamp;
			$setsqlarr['refreshtime']=$timestamp;
			$insertid = $db->inserttable(table('company_profile'),$setsqlarr,1);
			if ($insertid)
			{
				baidu_submiturl(url_rewrite('HW_companyshow',array('id'=>$insertid)),'addcompany');
				write_memberslog($_SESSION['uid'],$_SESSION['utype'],8001,$_SESSION['username'],"完善企业资料");
				exit("1");
			}
			else
			{
				exit("保存失败！");
			}
	}
}
?>
