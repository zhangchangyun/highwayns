﻿<?php
define('IN_HIGHWAY', true);
require_once(dirname(__FILE__).'/company_common.php');
$smarty->assign('leftmenu',"info");
if ($act=='company_profile')
{
	$company_profile['contents'] = htmlspecialchars_decode($company_profile['contents'],ENT_QUOTES);
	$smarty->assign('title','企業資料管理 - 企業会員センター - '.$_CFG['site_name']);
	$smarty->assign('company_profile',$company_profile);
	$jobs=get_auditjobs(intval($_SESSION['uid']));
	if(!empty($jobs))
	{
		$smarty->assign('company_jobs',$jobs);
	}
	// 新注册会员 邮箱调取注册邮箱
	$smarty->assign('user',$user);
	$smarty->display('member_company/company_profile.htm');
}
elseif ($act=='company_profile_save')
{
	
	$uid=intval($_SESSION['uid']);
	$setsqlarr['uid']=intval($_SESSION['uid']);
	if($company_profile['audit']!="1")
	{
		$setsqlarr['companyname']=trim($_POST['companyname'])?trim($_POST['companyname']):showmsg('企業名称を入力してください！',1);
	}
	else
	{
		$setsqlarr['companyname']=$company_profile['companyname'];
	}
	check_word($_CFG['filter'],$setsqlarr['companyname'])?showmsg($_CFG['filter_tips'],1):'';
	$setsqlarr['nature']=trim($_POST['nature'])?intval($_POST['nature']):showmsg('企業の性質を選択してください！',1);
	$setsqlarr['nature_cn']=trim($_POST['nature_cn']);
	$setsqlarr['trade']=trim($_POST['trade'])?intval($_POST['trade']):showmsg('業界を選択してください！',1);
	$setsqlarr['trade_cn']=trim($_POST['trade_cn']);
	$setsqlarr['district']=intval($_POST['district'])>0?intval($_POST['district']):showmsg('所属地区を選択してください！',1);
	$setsqlarr['sdistrict']=intval($_POST['sdistrict']);
	$setsqlarr['district_cn']=trim($_POST['district_cn']);
	if (intval($_POST['street'])>0)
	{
	$setsqlarr['street']=intval($_POST['street']);
	$setsqlarr['street_cn']=trim($_POST['street_cn']);
	}
	$setsqlarr['scale']=trim($_POST['scale'])?trim($_POST['scale']):showmsg('会社規模を選択してください！',1);
	$setsqlarr['scale_cn']=trim($_POST['scale_cn']);
	$setsqlarr['registered']=trim($_POST['registered']);
	$setsqlarr['currency']=trim($_POST['currency']);
	$setsqlarr['address']=trim($_POST['address'])?trim($_POST['address']):showmsg('連絡先を入力してください！',1);
	check_word($_CFG['filter'],$setsqlarr['address'])?showmsg($_CFG['filter_tips'],1):'';
	$setsqlarr['contact']=trim($_POST['contact'])?trim($_POST['contact']):showmsg('連絡者を入力してください！',1);
	check_word($_CFG['filter'],$setsqlarr['contact'])?showmsg($_CFG['filter_tips'],1):'';
	$setsqlarr['telephone']=trim($_POST['telephone'])?trim($_POST['telephone']):showmsg('電話番号を入力してください！',1);
	check_word($_CFG['filter'],$setsqlarr['telephone'])?showmsg($_CFG['filter_tips'],1):'';
	$setsqlarr['email']=trim($_POST['email'])?trim($_POST['email']):showmsg('メールを入力してください！',1);
	check_word($_CFG['filter'],$setsqlarr['email'])?showmsg($_CFG['filter_tips'],1):'';
	$setsqlarr['website']=trim($_POST['website']);
	check_word($_CFG['filter'],$setsqlarr['website'])?showmsg($_CFG['filter_tips'],1):'';
	$setsqlarr['contents']=trim($_POST['contents'])?trim($_POST['contents']):showmsg('会社の紹介を入力してください！',1);
	check_word($_CFG['filter'],$setsqlarr['contents'])?showmsg($_CFG['filter_tips'],1):'';
	
	$setsqlarr['contact_show']=intval($_POST['contact_show']);
	$setsqlarr['email_show']=intval($_POST['email_show']);
	$setsqlarr['telephone_show']=intval($_POST['telephone_show']);
	$setsqlarr['address_show']=intval($_POST['address_show']);
		
	if ($_CFG['company_repeat']=="0")
	{
		$info=$db->getone("SELECT uid FROM ".table('company_profile')." WHERE companyname ='{$setsqlarr['companyname']}' AND uid<>'{$_SESSION['uid']}' LIMIT 1");
		if(!empty($info))
		{
			showmsg("{$setsqlarr['companyname']}既に存在しました，同じ会社再度登録できません",1);
		}
	}
	if ($company_profile)
	{
			$_CFG['audit_edit_com']<>"-1"?$setsqlarr['audit']=intval($_CFG['audit_edit_com']):'';

			if ($db->updatetable(table('company_profile'), $setsqlarr," uid='{$uid}'"))
			{
				$jobarr['companyname']=$setsqlarr['companyname'];
				$jobarr['trade']=$setsqlarr['trade'];
				$jobarr['trade_cn']=$setsqlarr['trade_cn'];
				$jobarr['scale']=$setsqlarr['scale'];
				$jobarr['scale_cn']=$setsqlarr['scale_cn'];
				$jobarr['street']=$setsqlarr['street'];
				$jobarr['street_cn']=$setsqlarr['street_cn'];
				if (!$db->updatetable(table('jobs'),$jobarr," uid=".$setsqlarr['uid']."")) showmsg('会社名修正エラー！',1);
				if (!$db->updatetable(table('jobs_tmp'),$jobarr," uid=".$setsqlarr['uid']."")) showmsg('会社名修正エラー！',1);
				$soarray['trade']=$jobarr['trade'];
				$soarray['scale']=$jobarr['scale'];
				$soarray['street']=$setsqlarr['street'];
				$db->updatetable(table('jobs_search_scale'),$soarray," uid=".$setsqlarr['uid']."");
				$db->updatetable(table('jobs_search_wage'),$soarray," uid=".$setsqlarr['uid']."");
				$db->updatetable(table('jobs_search_rtime'),$soarray," uid=".$setsqlarr['uid']."");
				$db->updatetable(table('jobs_search_stickrtime'),$soarray," uid=".$setsqlarr['uid']."");
				$db->updatetable(table('jobs_search_hot'),$soarray," uid=".$setsqlarr['uid']."");
				$db->updatetable(table('jobs_search_key'),$soarray," uid=".$setsqlarr['uid']."");
				$db->query("update ".table("jobs_search_key")." set `key`=replace(`key`,'{$company_profile["companyname"]}','{$setsqlarr[companyname]}'),`likekey`=replace(`likekey`,'{$company_profile["companyname"]}','{$setsqlarr[companyname]}') where uid=".intval($_SESSION['uid'])." ");
				$db->query("update ".table("jobs")." set `key`=replace(`key`,'{$company_profile["companyname"]}','{$setsqlarr[companyname]}') where uid=".intval($_SESSION['uid'] )." ");
				//同步到职位联系方式
				if(intval($_POST['telephone_to_jobs'])==1)
				{
					$jobsid_arr=$db->getall("select id from ".table("jobs")." where uid=".intval($_SESSION['uid']));
					
					foreach ($jobsid_arr as $key => $value) {
						$jobsid_arr_[]=$value['id'];
					}
					$jobsid_str=implode(',', $jobsid_arr_);
					$db->query("update ".table('jobs_contact')." set telephone='$setsqlarr[telephone]',email='$setsqlarr[email]',contact='$setsqlarr[contact]' where pid in ($jobsid_str)");
				}
				unset($setsqlarr);
				write_memberslog($_SESSION['uid'],$_SESSION['utype'],8001,$_SESSION['username'],"企業資料変更");
				showmsg("変更成功",2);
			}
			else
			{
				showmsg("保存失敗！",1);
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
				// 完善企业资料 获得积分 
				$rule=get_cache('points_rule');
				if ($rule['company_profile_points']['value']>0)
				{
					$info=$db->getone("SELECT uid FROM ".table('members_handsel')." WHERE uid ='{$_SESSION['uid']}' AND htype='company_profile_points' LIMIT 1");
					if(empty($info))
					{
					$time=time();			
					$db->query("INSERT INTO ".table('members_handsel')." (uid,htype,addtime) VALUES ('{$_SESSION['uid']}', 'company_profile_points','{$time}')");
					report_deal($_SESSION['uid'],$rule['company_profile_points']['type'],$rule['company_profile_points']['value']);
					$user_points=get_user_points($_SESSION['uid']);
					$operator=$rule['company_profile_points']['type']=="1"?"+":"-";
					write_memberslog($_SESSION['uid'],1,9001,$_SESSION['username']," 企業資料補完，{$_CFG['points_byname']}({$operator}{$rule['company_profile_points']['value']})，(残る:{$user_points})",1,1016,"企業資料補完","{$operator}{$rule['company_profile_points']['value']}","{$user_points}");
					}
				}
				write_memberslog($_SESSION['uid'],$_SESSION['utype'],8001,$_SESSION['username'],"企業資料補完");
				baidu_submiturl(url_rewrite('HW_companyshow',array('id'=>$insertid)),'addcompany');
				showmsg("変更成功",2);
			}
			else
			{
				showmsg("保存失敗！",1);
			}
	}
}
elseif ($act=='company_auth')
{
	$link[0]['text'] = "企業資料補完";
	$link[0]['href'] = '?act=company_profile';
	$link[1]['text'] = "管理トップ";
	$link[1]['href'] = 'company_index.php';
	if (!$cominfo_flge) showmsg("企業資料補完後に謄本をアップロードしてください！",1,$link);
	$reason = get_user_audit_reason(intval($_SESSION['uid']));
	$smarty->assign('title','謄本 - 企業会員センター - '.$_CFG['site_name']);
	$smarty->assign('points',get_cache('points_rule'));
	$smarty->assign('reason',$reason['reason']);
	$smarty->assign('company_profile',$company_profile);
	$smarty->display('member_company/company_auth.htm');
}
//上传营业执照
elseif ($act=='company_auth_save')
{
	require_once(HIGHWAY_ROOT_PATH.'include/upload.php');
	$setsqlarr['license']=trim($_POST['license']);
	$setsqlarr['audit']=2;
	!$_FILES['certificate_img']['name']?exit('画像をアップロードしてください！'):"";
	$certificate_dir="../../data/".$_CFG['updir_certificate']."/".date("Y/m/d/");
	make_dir($certificate_dir);
	$setsqlarr['certificate_img']=_asUpFiles($certificate_dir, "certificate_img",$_CFG['certificate_max_size'],'gif/jpg/bmp/png',true);
	if ($setsqlarr['certificate_img'])
	{
		if(extension_loaded('gd')){
			include_once(HIGHWAY_ROOT_PATH.'include/watermark.php');
			$font_dir=HIGHWAY_ROOT_PATH."data/contactimgfont/cn.ttc";
			if(file_exists($font_dir)){
				$tpl=new watermark;
				$tpl->img($certificate_dir.$setsqlarr['certificate_img'],gbk_to_utf8($_CFG['site_name']),$font_dir,15,0);
			}
		}
		$setsqlarr['certificate_img']=date("Y/m/d/").$setsqlarr['certificate_img'];
		$auth=$company_profile;
		@unlink("../../data/".$_CFG['updir_certificate']."/".$auth['certificate_img']);
		$wheresql="uid='".$_SESSION['uid']."'";
		write_memberslog($_SESSION['uid'],1,8002,$_SESSION['username'],"謄本をアップロードする");
		$db->updatetable(table('jobs'),array('company_audit'=>$setsqlarr['audit']),$wheresql);
		$db->updatetable(table('jobs_tmp'),array('company_audit'=>$setsqlarr['audit']),$wheresql);
		if(!$db->updatetable(table('company_profile'),$setsqlarr,$wheresql))
		{
			exit("-6");
		}
		else
		{
			$data['isok'] = 1;
			$json_encode = json_encode($data);
			exit($json_encode);
		}
	}
	else
	{
	exit("-6");
	}
}
elseif ($act=='company_logo')
{
	$link[0]['text'] = "企業資料補完";
	$link[0]['href'] = '?act=company_profile';
	$link[1]['text'] = "会員中心首页";
	$link[1]['href'] = 'company_index.php';
	if (empty($company_profile['companyname'])) showmsg("企業資料入力完了後、企業LOGOをアップロードしてください！",1,$link);
	$smarty->assign('title','企業LOGO - 企業会員センター - '.$_CFG['site_name']);
	$smarty->assign('company_profile',$company_profile);
	$smarty->assign('rand',rand(1,100));
	$smarty->display('member_company/company_logo.htm');
}
elseif ($act=='company_logo_save')
{
	require_once(HIGHWAY_ROOT_PATH.'include/upload.php');
	!$_FILES['logo']['name']?showmsg('画像をアップロードしてください！',1):"";
	$uplogo_dir="../../data/logo/".date("Y/m/d/");
	make_dir($uplogo_dir);
	$setsqlarr['logo']=_asUpFiles($uplogo_dir, "logo",$_CFG['logo_max_size'],'gif/jpg/bmp/png',$_SESSION['uid']);
	if ($setsqlarr['logo'])
	{
	$setsqlarr['logo']=date("Y/m/d/").$setsqlarr['logo'];
	$logo_src="../../data/logo/".$setsqlarr['logo'];
	$thumb_dir=$uplogo_dir;
	makethumb($logo_src,$thumb_dir,300,110);//生成缩略图
	$wheresql="uid='".$_SESSION['uid']."'";
			if ($db->updatetable(table('company_profile'),$setsqlarr,$wheresql))
			{
			$link[0]['text'] = "LOGO閲覧";
			$link[0]['href'] = '?act=company_logo';
			write_memberslog($_SESSION['uid'],1,8003,$_SESSION['username'],"企業LOGOをアップロードしました");
			// 上传logo 获得积分 
			$rule=get_cache('points_rule');
			if ($rule['company_logo_points']['value']>0)
			{
				$info=$db->getone("SELECT uid FROM ".table('members_handsel')." WHERE uid ='{$_SESSION['uid']}' AND htype='company_logo_points' LIMIT 1");
				if(empty($info))
				{
				$time=time();			
				$db->query("INSERT INTO ".table('members_handsel')." (uid,htype,addtime) VALUES ('{$_SESSION['uid']}', 'company_logo_points','{$time}')");
				report_deal($_SESSION['uid'],$rule['company_logo_points']['type'],$rule['company_logo_points']['value']);
				$user_points=get_user_points($_SESSION['uid']);
				$operator=$rule['company_logo_points']['type']=="1"?"+":"-";
				write_memberslog($_SESSION['uid'],1,9001,$_SESSION['username']," 企業logoアップロード，{$_CFG['points_byname']}({$operator}{$rule['company_logo_points']['value']})，(残る:{$user_points})",1,1016,"企業logoアップロード","{$operator}{$rule['company_logo_points']['value']}","{$user_points}");
				}
			}
			showmsg('アップロード成功！',2,$link);
			}
			else
			{
			showmsg('保存失敗！',1);
			}
	}
	else
	{
	showmsg('保存失敗！',1);
	}
}
elseif ($act=='company_logo_del')
{
	$uplogo_dir="../../data/logo/";
	$auth=$company_profile;//获取原始图片
	@unlink($uplogo_dir.$auth['logo']);//先删除原始图片
	$setsqlarr['logo']="";
	$wheresql="uid='".$_SESSION['uid']."'";
		if ($db->updatetable(table('company_profile'),$setsqlarr,$wheresql))
		{
		write_memberslog($_SESSION['uid'],1,8004,$_SESSION['username'],"企業LOGO削除済み");
		showmsg('削除成功！',2);
		}
		else
		{
		showmsg('削除失敗！',1);
		}
}
 elseif ($act=='company_map')
{
	$link[0]['text'] = "企業資料を入力してください";
	$link[0]['href'] = '?act=company_profile';
	if (empty($company_profile['companyname'])) showmsg("请完善您の企業資料再設定電子地図！",1,$link);
	if ($company_profile['map_open']=="1")//假如已经开通
	{
	header("Location: ?act=company_map_set");
	}
	else
	{
		if($_CFG['operation_mode']=='1'){
			$smarty->assign('operation_mode',1);
			$points=get_cache('points_rule');//获取积分消费规则
			$smarty->assign('points',$points['company_map']['value']);
		}elseif($_CFG['operation_mode']=='2'){
			$smarty->assign('operation_mode',2);
			$setmeal=get_user_setmeal($_SESSION['uid']);
			$smarty->assign('map_open',$setmeal['map_open']);
		}elseif($_CFG['operation_mode']=='3'){
			$setmeal=get_user_setmeal($_SESSION['uid']);
			if ($setmeal['endtime']<time() && $setmeal['endtime']<>'0'){
				if($_CFG['setmeal_to_points']==1){
					$smarty->assign('operation_mode',1);
					$points=get_cache('points_rule');//获取积分消费规则
					$smarty->assign('points',$points['company_map']['value']);
				}else{
					$smarty->assign('operation_mode',2);
					$setmeal=get_user_setmeal($_SESSION['uid']);
					$smarty->assign('map_open',$setmeal['map_open']);
				}
			}else{
				$smarty->assign('operation_mode',2);
				$setmeal=get_user_setmeal($_SESSION['uid']);
				$smarty->assign('map_open',$setmeal['map_open']);
			}
		}
		$smarty->assign('title','電子地図有効 - 企業会員センター - '.$_CFG['site_name']);
		$smarty->display('member_company/company_map_open.htm');
	}
}
elseif ($act=='company_map_open')
{
	$link[0]['text'] = "企業資料を入力してください";
	$link[0]['href'] = '?act=company_profile';
	if (empty($company_profile['companyname'])) showmsg("请完善您の企業資料再設定電子地図！",1);
	if ($company_profile['map_open']=="1")//假如已经开通
	{
	header("Location: ?act=company_map_set");
	}else{
		if($_CFG['operation_mode']=='1'){
			$operation_mode = 1;
		}elseif($_CFG['operation_mode']=='2'){
			$operation_mode = 2;
		}elseif($_CFG['operation_mode']=='3'){
			$setmeal=get_user_setmeal($_SESSION['uid']);
			if ($setmeal['endtime']<time() && $setmeal['endtime']<>'0'){
				if($_CFG['setmeal_to_points']==1){
					$operation_mode = 1;
				}else{
					$operation_mode = 2;
				}
			}else{
				$operation_mode = 2;
			}
		}
	 	if($operation_mode=='1'){
			$points=get_cache('points_rule');
			$user_points=get_user_points($_SESSION['uid']);
			if ($points['company_map']['type']=="2" && $points['company_map']['value']>$user_points)
			{
			showmsg("貴方の".$_CFG['points_byname']."ポイント足りない，振込後に実行してください！",0);
			}
		}elseif($operation_mode=='2'){
			$setmeal=get_user_setmeal($_SESSION['uid']);
			if ($setmeal['endtime']<time() &&  $setmeal['endtime']<>'0'){
				showmsg("サービスコース期限切れた，再度サービス申し込みください！",0);
			}elseif($setmeal['map_open']=='0'){
				showmsg("サービスコース：{$setmeal['setmeal_name']} 電子地図のご利用ができません，サービスコースをアップグレードしてください！",0);
			}
		}
		
		$wheresql="uid='".$_SESSION['uid']."'";
		$setsqlarr['map_open']=1;
		if ($db->updatetable(table('company_profile'),$setsqlarr,$wheresql))
		{
			//发送邮件
			$mailconfig=get_cache('mailconfig');
			if ($mailconfig['set_addmap']=="1" && $user['email_audit']=="1")
			{
			dfopen($_CFG['site_domain'].$_CFG['site_dir']."plus/asyn_mail.php?uid=".$_SESSION['uid']."&key=".asyn_userkey($_SESSION['uid'])."&act=set_addmap");
			}
			//sms
			$sms=get_cache('sms_config');
			if ($sms['open']=="1" && $sms['set_addmap']=="1"  && $user['mobile_audit']=="1")
			{
				dfopen($_CFG['site_domain'].$_CFG['site_dir']."plus/asyn_sms.php?uid=".$_SESSION['uid']."&key=".asyn_userkey($_SESSION['uid'])."&act=set_addmap");
			}			
			write_memberslog($_SESSION['uid'],1,8005,$_SESSION['username'],"電子地図を有効にしました");
			if($operation_mode=='1' || $operation_mode=='3'){
				if ($points['company_map']['value']>0)
				{
				report_deal($_SESSION['uid'],$points['company_map']['type'],$points['company_map']['value']);
				$user_points=get_user_points($_SESSION['uid']);
				$operator=$points['company_map']['type']=="1"?"+":"-";
				write_memberslog($_SESSION['uid'],1,9001,$_SESSION['username'],"電子地図({$operator}{$points['company_map']['value']})を有効にしました，(残る:{$user_points})",1,1008,"電子地図","{$operator}{$points['company_map']['value']}","{$user_points}");
				}
			}elseif($operation_mode=='2'){
				write_memberslog($_SESSION['uid'],1,9002,$_SESSION['username'],"サービスコース利用するため電子地図を有効にする",2,1008,"電子地図","0","");
			}
			header("Location: ?act=company_map_set");
		}
		else
		{
		showmsg('Active失敗！',1);
		}
	}
	
}
 
elseif ($act=='company_map_set')
{
	$smarty->assign('title','電子地図設定 - 企業会員センター - '.$_CFG['site_name']);
	$smarty->assign('company_profile',$company_profile);
	$smarty->display('member_company/company_map_set.htm');
}
elseif ($act=='company_map_set_save')
{
	$setsqlarr['map_x']=trim($_POST['x'])?trim($_POST['x']):showmsg('地図でポジションを探して，マイポジションを保存ボタンをクリックする！',1);
	$setsqlarr['map_y']=trim($_POST['y'])?trim($_POST['y']):showmsg('地図でポジションを探して，マイポジションを保存ボタンをクリックする！',1);
	$setsqlarr['map_zoom']=trim($_POST['zoom']);
	$wheresql=" uid='{$_SESSION['uid']}'";
	write_memberslog($_SESSION['uid'],1,8006,$_SESSION['username'],"電子地図座標設定済み");
	if ($db->updatetable(table('company_profile'),$setsqlarr,$wheresql))
	{
		$jobsql['map_x']=$setsqlarr['map_x'];
		$jobsql['map_y']=$setsqlarr['map_y'];
		$db->updatetable(table('jobs'),$jobsql,$wheresql);
		$db->updatetable(table('jobs_tmp'),$jobsql,$wheresql);
		unset($setsqlarr['map_zoom']);
		//
		$db->updatetable(table('jobs_search_rtime'),$jobsql,$wheresql);
		$db->updatetable(table('jobs_search_key'),$jobsql,$wheresql);
		showmsg('保存成功',2);
	}
	else
	{
	showmsg('保存失敗',1);
	}
}
unset($smarty);
?>
