﻿<?php
define('IN_HIGHWAY', true);
require_once(dirname(__FILE__).'/company_common.php');
$smarty->assign('leftmenu',"jobs");
if ($act=='jobs')
{
	$addjobs_save_succeed=intval($_GET['addjobs_save_succeed']);
	$jobtype=intval($_GET['jobtype']);
	$wheresql=" WHERE uid='{$_SESSION['uid']}' ";
	$orderby=" order by refreshtime desc";
	switch($jobtype){
		case 1:
			$tabletype="all";
			/* 全部职位 状态 筛选*/
			$state=intval($_GET["state"]);
			if($state==1)
			{	
				$tabletype="jobs";
			}
			elseif($state==2)
			{
				$tabletype="jobs_tmp";
				$wheresql.=" AND audit=2 ";
			}
			elseif($state==3)
			{
				$tabletype="jobs_tmp";
				$wheresql.=" AND audit=3 ";
			}
			elseif($state==4)
			{
				$tabletype="jobs_tmp";
				$wheresql.=" AND (display=2 or deadline<".time()." or setmeal_deadline<".time().") ";
			}
			$orderby=" order by display asc,audit asc";
			break;
			
		case 2:
			$tabletype="jobs_tmp";
			$wheresql.=" AND audit=2 ";
			break;
		case 3:
			$tabletype="jobs_tmp";
			/* 未显示 状态 筛选*/
			$state=intval($_GET["state"]);
			if($state==0)
			{
				$wheresql.=" AND (audit=3 or display=2 or deadline<".time()." or setmeal_deadline<".time().")";
			}
			elseif($state==1)
			{
				$wheresql.=" AND audit=3 ";
			}
			else
			{
				$wheresql.=" AND (display=2 or deadline<".time()." or setmeal_deadline<".time().") ";
			}
			break;
		default:
			$tabletype="jobs";
			break;
	}
	$generalize=trim($_GET['generalize']);
	$generalize_arr = array("stick","highlight","emergency","recommend");
	if(in_array($generalize,$generalize_arr))
	{
		$wheresql.=" AND $generalize<>'' ";
	}
	require_once(HIGHWAY_ROOT_PATH.'include/page.class.php');
	$perpage=10;
	if ($tabletype=="all")
	{
	$total_sql="SELECT COUNT(*) AS num FROM ".table('jobs').$wheresql." UNION ALL  SELECT COUNT(*) AS num FROM ".table('jobs_tmp').$wheresql;
	}
	else
	{
	$total_sql="SELECT COUNT(*) AS num FROM ".table($tabletype).$wheresql;
	}
	$total_val=$db->get_total($total_sql);
	$page = new page(array('total'=>$total_val, 'perpage'=>$perpage,'getarray'=>$_GET));
	$offset=($page->nowindex-1)*$perpage;
	$smarty->assign('title','職位管理 - 企業会員センター - '.$_CFG['site_name']);
	$smarty->assign('act',$act);
	$smarty->assign('audit',$audit);
	if ($tabletype=="all")
	{
	$sql="SELECT * FROM ".table('jobs').$wheresql." UNION ALL SELECT * FROM ".table('jobs_tmp').$wheresql.$orderby;
	}
	else
	{
	$sql="SELECT * FROM ".table($tabletype).$wheresql.$orderby;
	}
	$total[0]=$db->get_total("SELECT COUNT(*) AS num FROM ".table('jobs')." WHERE uid='{$_SESSION['uid']}' and audit=1 and display=1 ");
	$total[1]=$db->get_total("SELECT COUNT(*) AS num FROM ".table('jobs_tmp')." WHERE uid='{$_SESSION['uid']}' AND audit=2 ");
	$total[2]=$total[0]+$total[1];
	//统计每个分类中的职位数
	$jobs_total[0]=$db->get_total("SELECT COUNT(*) AS num FROM ".table('jobs')." WHERE uid='{$_SESSION['uid']}' ");
	$jobs_total[1]=$db->get_total("SELECT COUNT(*) AS num FROM ".table('jobs')." WHERE uid='{$_SESSION['uid']}'  UNION ALL  SELECT COUNT(*) AS num FROM ".table('jobs_tmp')." WHERE uid='{$_SESSION['uid']}' ");
	$jobs_total[2]=$db->get_total("SELECT COUNT(*) AS num FROM ".table('jobs_tmp')." WHERE uid='{$_SESSION['uid']}'  AND audit=2 ");
	$jobs_total[3]=$db->get_total("SELECT COUNT(*) AS num FROM ".table('jobs_tmp')." WHERE uid='{$_SESSION['uid']}'  AND (audit=3 or display=2 or deadline<".time()." or setmeal_deadline<".time().")");
	$smarty->assign('total',$total);
	$smarty->assign('jobs_total',$jobs_total);
	$setmeal=get_user_setmeal($_SESSION['uid']);
	$smarty->assign('setmeal',$setmeal);
	$smarty->assign('jobs',get_jobs($offset,$perpage,$sql,true));
	if($total_val>$perpage)
	{
		$smarty->assign('page',$page->show(3));
	}
	// 发布成功标记
	$addjobs_save_succeed=intval($_GET['addjobs_save_succeed']);
	$jobs_one=get_jobs_one($addjobs_save_succeed);
	$smarty->assign('jobs_one',$jobs_one);
	$smarty->assign('points_rule',get_cache('points_rule'));
	$smarty->assign('user_points',get_user_points($_SESSION['uid']));
	$smarty->display('member_company/company_jobs.htm');
}
elseif ($act=='addjobs')
{
		$smarty->assign('user',$user);
		if ($cominfo_flge)
		{
			$_SESSION['addrand']=rand(1000,5000);
			$smarty->assign('addrand',$_SESSION['addrand']);
			$smarty->assign('title','職位配布 - 企業会員センター - '.$_CFG['site_name']);
			$smarty->assign('company_profile',$company_profile);
			if ($_CFG['operation_mode']=="3")
			{
				$setmeal=get_user_setmeal($_SESSION['uid']);
				if (($setmeal['endtime']>time() || $setmeal['endtime']=="0") &&  $setmeal['jobs_ordinary']>0)
				{
				$smarty->assign('setmeal',$setmeal);
				$add_mode = 2;
				$smarty->assign('add_mode',2);
				}
				elseif($_CFG['setmeal_to_points']=="1")
				{
				$smarty->assign('points_total',get_user_points($_SESSION['uid']));
				$smarty->assign('points',get_cache('points_rule'));
				$add_mode = 1;
				$smarty->assign('add_mode',1);
				}
				else
				{
				$smarty->assign('setmeal',$setmeal);
				$add_mode = 2;
				$smarty->assign('add_mode',2);
				}
				
			}
			elseif ($_CFG['operation_mode']=="2")
			{
				$setmeal=get_user_setmeal($_SESSION['uid']);
				$smarty->assign('setmeal',$setmeal);
				$add_mode = 2;
				$smarty->assign('add_mode',2);
			}
			elseif ($_CFG['operation_mode']=="1")
			{
				$smarty->assign('points_total',get_user_points($_SESSION['uid']));
				$smarty->assign('points',get_cache('points_rule'));
				$add_mode = 1;
				$smarty->assign('add_mode',1);
			}
			/**
			 * 3.6优化start
			 */
			if ($add_mode=='1')
			{
				$points_rule=get_cache('points_rule');
				$user_points=get_user_points($_SESSION['uid']);
				if ($points_rule['jobs_add']['type']=="2" && $points_rule['jobs_add']['value']>0)
				{
					$total=$points_rule['jobs_add']['value'];
					if ($total>$user_points)
					{
						$link[0]['text'] = "即時振込";
						$link[0]['href'] = 'company_service.php?act=order_add';
						$link[1]['text'] = "会員中心首页";
						$link[1]['href'] = 'company_index.php?act=';
						showmsg("貴方の".$_CFG['points_byname']."ポイント不足，振込してください！",0,$link);
					}
				}
			}
			elseif ($add_mode=='2')
			{
				$link[0]['text'] = "サービスを有効にする";
				$link[0]['href'] = 'company_service.php?act=setmeal_list';
				$link[1]['text'] = "会員中心首页";
				$link[1]['href'] = 'company_index.php?act=';
				$setmeal=get_user_setmeal($_SESSION['uid']);
				if ($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
				{					
					showmsg("サービス期限切れた，再申し込みしてください",1,$link);
				}
				$jobs_num= $db->get_total("select count(*) num from ".table("jobs")." where uid=$_SESSION[uid] and audit=1 and display=1 ");
				$jobs_tmp_num= $db->get_total("select count(*) num from ".table("jobs_tmp")." where uid=$_SESSION[uid] and audit<>3 and display=1 ");
				$com_jobs_num=$jobs_num+$jobs_tmp_num;
				if ($com_jobs_num>=$setmeal['jobs_ordinary'])
				{
					showmsg("現在表示されたの職位最大制限を超えました，サービスコースをアップグレードしてください！",1,$link);
				}
			}
			/**
			 * 3.6优化end
			 */

			$captcha=get_cache('captcha');
			$smarty->assign('verify_addjob',$captcha['verify_addjob']);
			$smarty->display('member_company/company_addjobs.htm');
		}
		else
		{
		$link[0]['text'] = "企業資料補完";
		$link[0]['href'] = 'company_info.php?act=company_profile';
		showmsg("募集効果を出るため，貴方の企業資料を補完してください！",1,$link);
		}
}
elseif ($act=='addjobs_save')
{
	$captcha=get_cache('captcha');
	$postcaptcha = trim($_POST['postcaptcha']);
	if($captcha['verify_addjob']=='1' && empty($postcaptcha))
	{
		showmsg("検証コードを入力してください",1);
 	}
	if ($captcha['verify_addjob']=='1' && strcasecmp($_SESSION['imageCaptcha_content'],$postcaptcha)!=0)
	{
		showmsg("確認コードエラー",1);
	}
	$add_mode=trim($_POST['add_mode']);
	if ($add_mode=='1')
	{
		$points_rule=get_cache('points_rule');
		$user_points=get_user_points($_SESSION['uid']);
		if ($points_rule['jobs_add']['type']=="2" && $points_rule['jobs_add']['value']>0)
		{
			$total=$points_rule['jobs_add']['value'];
			if ($total>$user_points)
			{
				$link[0]['text'] = "即時振込";
				$link[0]['href'] = 'company_service.php?act=order_add';
				$link[1]['text'] = "会員中心首页";
				$link[1]['href'] = 'company_index.php?act=';
				showmsg("貴方の".$_CFG['points_byname']."ポイント不足，振込してください！",0,$link);
			}
		}
		if($_CFG['operation_mode']=="1")
		{
			$setsqlarr['setmeal_deadline']=0;
		}
		elseif($_CFG['operation_mode']=="3")
		{
			$setmeal=get_user_setmeal($_SESSION['uid']);
			if(empty($setmeal))
			{
				$setsqlarr['setmeal_deadline']=0;
			}
			else
			{
				$setsqlarr['setmeal_deadline']=0;
				$setsqlarr['setmeal_id']=$setmeal['setmeal_id'];
				$setsqlarr['setmeal_name']=$setmeal['setmeal_name'];
			}
		}
	}
	elseif ($add_mode=='2')
	{
		$link[0]['text'] = "サービスを有効にする";
		$link[0]['href'] = 'company_service.php?act=setmeal_list';
		$link[1]['text'] = "会員中心首页";
		$link[1]['href'] = 'company_index.php?act=';
		$setmeal=get_user_setmeal($_SESSION['uid']);
		if ($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
		{					
			showmsg("サービス期限切れた，再申し込みしてください",1,$link);
		}
		$jobs_num= $db->get_total("select count(*) num from ".table("jobs")." where uid=$_SESSION[uid] and audit=1 and display=1 ");
		$jobs_tmp_num= $db->get_total("select count(*) num from ".table("jobs_tmp")." where uid=$_SESSION[uid] and audit<>3 and display=1 ");
		$com_jobs_num=$jobs_num+$jobs_tmp_num;
		if ($com_jobs_num>=$setmeal['jobs_ordinary'])
		{
			showmsg("現在表示されたの職位最大制限を超えました，サービスコースをアップグレードしてください！",1,$link);
		}
		$setsqlarr['setmeal_deadline']=$setmeal['endtime'];
		$setsqlarr['setmeal_id']=$setmeal['setmeal_id'];
		$setsqlarr['setmeal_name']=$setmeal['setmeal_name'];
	}
	
	$addrand=intval($_POST['addrand']);
	if($_SESSION['addrand']==$addrand){
	unset($_SESSION['addrand']);
	$setsqlarr['add_mode']=intval($add_mode);
	$setsqlarr['uid']=intval($_SESSION['uid']);
	$setsqlarr['companyname']=$company_profile['companyname'];
	$setsqlarr['company_id']=$company_profile['id'];
	$setsqlarr['company_addtime']=$company_profile['addtime'];
	$setsqlarr['company_audit']=$company_profile['audit'];
	$setsqlarr['jobs_name']=!empty($_POST['jobs_name'])?trim($_POST['jobs_name']):showmsg('職位を選択してください！',1);
	check_word($_CFG['filter'],$_POST['jobs_name'])?showmsg($_CFG['filter_tips'],0):'';
	$setsqlarr['nature']=intval($_POST['nature']);
	$setsqlarr['nature_cn']=trim($_POST['nature_cn']);
	$setsqlarr['topclass']=intval($_POST['topclass']);
	$setsqlarr['category']=!empty($_POST['category'])?intval($_POST['category']):showmsg('職業種類を選択してください！',1);
	$setsqlarr['subclass']=intval($_POST['subclass']);
	$setsqlarr['category_cn']=trim($_POST['category_cn']);
	$setsqlarr['amount']=intval($_POST['amount']);
	$setsqlarr['district']=!empty($_POST['district'])?intval($_POST['district']):showmsg('仕事地区を選択してください！',1);
	$setsqlarr['sdistrict']=intval($_POST['sdistrict']);
	$setsqlarr['district_cn']=trim($_POST['district_cn']);
	$setsqlarr['wage']=intval($_POST['wage'])?intval($_POST['wage']):showmsg('給料選択してください！',1);		
	$setsqlarr['wage_cn']=trim($_POST['wage_cn']);
	$setsqlarr['negotiable']=intval($_POST['negotiable']);
	$setsqlarr['tag']=trim($_POST['tag']);
	$setsqlarr['tag_cn']=trim($_POST['tag_cn']);
	$setsqlarr['sex']=intval($_POST['sex']);
	$setsqlarr['sex_cn']=trim($_POST['sex_cn']);
	$setsqlarr['education']=intval($_POST['education'])?intval($_POST['education']):showmsg('学歴要求を入力してください！',1);		
	$setsqlarr['education_cn']=trim($_POST['education_cn']);
	$setsqlarr['experience']=intval($_POST['experience'])?intval($_POST['experience']):showmsg('仕事経験を選択してください！',1);		
	$setsqlarr['experience_cn']=trim($_POST['experience_cn']);
	$setsqlarr['graduate']=intval($_POST['graduate']);
	$setsqlarr['age']=trim($_POST['minage'])."-".trim($_POST['maxage']);
	$setsqlarr['contents']=!empty($_POST['contents'])?trim($_POST['contents']):showmsg('職位説明を入力してください！',1);
	check_word($_CFG['filter'],$_POST['contents'])?showmsg($_CFG['filter_tips'],0):'';
	$setsqlarr['trade']=$company_profile['trade'];
	$setsqlarr['trade_cn']=$company_profile['trade_cn'];
	$setsqlarr['scale']=$company_profile['scale'];
	$setsqlarr['scale_cn']=$company_profile['scale_cn'];
	$setsqlarr['street']=$company_profile['street'];
	$setsqlarr['street_cn']=$company_profile['street_cn'];
	$setsqlarr['addtime']=$timestamp;
	$setsqlarr['deadline']=strtotime("".intval($_CFG['company_add_days'])." day");
	$setsqlarr['refreshtime']=$timestamp;
	$setsqlarr['key']=$setsqlarr['jobs_name'].$company_profile['companyname'].$setsqlarr['category_cn'].$setsqlarr['district_cn'].$setsqlarr['contents'];
	require_once(HIGHWAY_ROOT_PATH.'include/splitword.class.php');
	$sp = new SPWord();
	$setsqlarr['key']="{$setsqlarr['jobs_name']} {$company_profile['companyname']} ".$sp->extracttag($setsqlarr['key']);
	$setsqlarr['key']=$sp->pad($setsqlarr['key']);
	$setsqlarr['tpl']=$company_profile['tpl'];
	$setsqlarr['map_x']=$company_profile['map_x'];
	$setsqlarr['map_y']=$company_profile['map_y'];
	if ($company_profile['audit']=="1")
	{
	$setsqlarr['audit']=intval($_CFG['audit_verifycom_addjob']);
	}
	else
	{
	$setsqlarr['audit']=intval($_CFG['audit_unexaminedcom_addjob']);
	} 
	$setsqlarr_contact['contact']=!empty($_POST['contact'])?trim($_POST['contact']):showmsg('連絡先を入力してください！',1);
	check_word($_CFG['filter'],$_POST['contact'])?showmsg($_CFG['filter_tips'],0):'';
	$setsqlarr_contact['telephone']=!empty($_POST['telephone'])?trim($_POST['telephone']):showmsg('連絡電話入力してください！',1);
	check_word($_CFG['filter'],$_POST['telephone'])?showmsg($_CFG['filter_tips'],0):'';
	$setsqlarr_contact['address']=!empty($_POST['address'])?trim($_POST['address']):showmsg('連絡先を入力してください！',1);
	check_word($_CFG['filter'],$_POST['address'])?showmsg($_CFG['filter_tips'],0):'';
	$setsqlarr_contact['email']=!empty($_POST['email'])?trim($_POST['email']):showmsg('メールを入力してください！',1);
	check_word($_CFG['filter'],$_POST['email'])?showmsg($_CFG['filter_tips'],0):'';
	$setsqlarr_contact['notify']=intval($_POST['notify']);//邮件提醒
	$setsqlarr_contact['notify_mobile']=intval($_POST['notify_mobile']);//手机提醒
	$setsqlarr_contact['contact_show']=intval($_POST['contact_show']);
	$setsqlarr_contact['email_show']=intval($_POST['email_show']);
	$setsqlarr_contact['telephone_show']=intval($_POST['telephone_show']);
	$setsqlarr_contact['address_show']=intval($_POST['address_show']);
	
	//添加职位信息
	$pid=$db->inserttable(table('jobs'),$setsqlarr,true);
	if(empty($pid)){
		showmsg("追加失敗！",0);
	}
	//添加联系方式
	$setsqlarr_contact['pid']=$pid;
	!$db->inserttable(table('jobs_contact'),$setsqlarr_contact)?showmsg("追加失敗！",0):'';
	if ($add_mode=='1')
	{
		if ($points_rule['jobs_add']['value']>0)
		{
		report_deal($_SESSION['uid'],$points_rule['jobs_add']['type'],$points_rule['jobs_add']['value']);
		$user_points=get_user_points($_SESSION['uid']);
		$operator=$points_rule['jobs_add']['type']=="1"?"+":"-";
		write_memberslog($_SESSION['uid'],1,9001,$_SESSION['username'],"職位を配布しました：<strong>{$setsqlarr['jobs_name']}</strong>，({$operator}{$points_rule['jobs_add']['value']})，(残る:{$user_points})",1,1001,"職位配布","{$operator}{$points_rule['jobs_add']['value']}","{$user_points}");
		}
	}
	elseif ($add_mode=='2')
	{
		if($setsqlarr['audit']!="3")
		{
			// action_user_setmeal($_SESSION['uid'],"jobs_ordinary");
			$setmeal=get_user_setmeal($_SESSION['uid']);
			$com_jobs= $db->getone("select count(*) num from ".table("jobs")." where uid=$_SESSION[uid] and (audit=1 or audit=2) and display=1 ");
			$num=$setmeal['jobs_ordinary']-$com_jobs['num'];
			write_memberslog($_SESSION['uid'],1,9002,$_SESSION['username'],"普通職位配布:<strong>{$_POST['jobs_name']}</strong>，配布普通職位可能件数:<strong>{$num}</strong>件",2,1001,"職位配布","1","{$setmeal['jobs_ordinary']}");
		}
		
	}
	$searchtab['id']=$pid;
	$searchtab['uid']=$setsqlarr['uid'];
	$searchtab['recommend']=$setsqlarr['recommend'];
	$searchtab['emergency']=$setsqlarr['emergency'];
	$searchtab['nature']=$setsqlarr['nature'];
	$searchtab['sex']=$setsqlarr['sex'];
	$searchtab['topclass']=$setsqlarr['topclass'];
	$searchtab['category']=$setsqlarr['category'];
	$searchtab['subclass']=$setsqlarr['subclass'];
	$searchtab['trade']=$setsqlarr['trade'];
	$searchtab['district']=$setsqlarr['district'];
	$searchtab['sdistrict']=$setsqlarr['sdistrict'];	
	$searchtab['street']=$company_profile['street'];
	$searchtab['education']=$setsqlarr['education'];
	$searchtab['experience']=$setsqlarr['experience'];
	$searchtab['wage']=$setsqlarr['wage'];
	$searchtab['refreshtime']=$setsqlarr['refreshtime'];
	$searchtab['scale']=$setsqlarr['scale'];
	$searchtab['graduate']=$setsqlarr['graduate'];	
	//
	$db->inserttable(table('jobs_search_wage'),$searchtab);
	$db->inserttable(table('jobs_search_scale'),$searchtab);
	//
	$searchtab['map_x']=$setsqlarr['map_x'];
	$searchtab['map_y']=$setsqlarr['map_y'];
	$db->inserttable(table('jobs_search_rtime'),$searchtab);
	unset($searchtab['map_x'],$searchtab['map_y']);
	//
	$searchtab['stick']=$setsqlarr['stick'];
	$db->inserttable(table('jobs_search_stickrtime'),$searchtab);
	unset($searchtab['stick']);
	//
	$searchtab['click']=$setsqlarr['click'];
	$db->inserttable(table('jobs_search_hot'),$searchtab);
	unset($searchtab['click']);
	//
	$searchtab['key']=$setsqlarr['key'];
	$searchtab['likekey']=$setsqlarr['jobs_name'].','.$setsqlarr['companyname'];
	$searchtab['map_x']=$setsqlarr['map_x'];
	$searchtab['map_y']=$setsqlarr['map_y'];
	$db->inserttable(table('jobs_search_key'),$searchtab);
	unset($searchtab);
	add_jobs_tag($pid,$_SESSION['uid'],$_POST['tag'])?"":showmsg('保存失敗！',0);
	distribution_jobs($pid,$_SESSION['uid']);
	write_memberslog($_SESSION['uid'],1,2001,$_SESSION['username'],"職位配布：{$setsqlarr['jobs_name']}");
	baidu_submiturl(url_rewrite('HW_jobsshow',array('id'=>$pid)),'addjob');
	}
	header("location:?act=jobs&addjobs_save_succeed=".$pid);
}
elseif ($act=='jobs_perform')
{
	global $_CFG;
	$yid =!empty($_POST['y_id'])?$_POST['y_id']:$_GET['y_id'];
    $jobs_num=count($yid);
	if (empty($yid))
	{
	showmsg("選択された職位がなし！",1);
	}
	
	$refresh=!empty($_POST['refresh'])?$_POST['refresh']:$_GET['refresh'];
	$delete=!empty($_POST['delete'])?$_POST['delete']:$_GET['delete']; 
    if ($refresh)
	{
		$mode = 0;
		if($jobs_num==1){
			if(is_array($yid)){
				$yid = $yid[0];
			}
			$jobs_info = get_jobs_one($yid,$_SESSION['uid']);
			if($jobs_info['deadline']<time()){
				showmsg("この職位期限切れた！",1);
			}
		}
		//积分模式
		if($_CFG['operation_mode']=='1')
		{
			$mode = 1;
			//限制刷新时间
			//最近一次的刷新时间
			$refrestime=get_last_refresh_date($_SESSION['uid'],"1001",1);
			$duringtime=time()-$refrestime['max(addtime)'];
			$space = $_CFG['com_pointsmode_refresh_space']*60;
			$refresh_time = get_today_refresh_times($_SESSION['uid'],"1001",1);
			if($_CFG['com_pointsmode_refresh_time']!=0&&($refresh_time['count(*)']>=$_CFG['com_pointsmode_refresh_time']))
			{
			showmsg("毎日最大更新件数".$_CFG['com_pointsmode_refresh_time']."回,今日最大更新回数を超えた！",2);	
			}
			elseif($duringtime<=$space){
			showmsg($_CFG['com_pointsmode_refresh_space']."分以内、職位再度検索不可！",2);
			}
			else 
			{
				$points_rule=get_cache('points_rule');
				if($points_rule['jobs_refresh']['value']>0)
				{
					$user_points=get_user_points($_SESSION['uid']);
					$total_point=$jobs_num*$points_rule['jobs_refresh']['value'];
					if ($total_point>$user_points && $points_rule['jobs_refresh']['type']=="2")
					{
							$link[0]['text'] = "前頁に戻る";
							$link[0]['href'] = 'javascript:history.go(-1)';
							$link[1]['text'] = "即時振込";
							$link[1]['href'] = 'company_service.php?act=order_add';
					showmsg("貴方様の".$_CFG['points_byname']."ポイント足りない，振込してください！",0,$link);
					}
					//加/减 积分
					report_deal($_SESSION['uid'],$points_rule['jobs_refresh']['type'],$total_point);
					$user_points=get_user_points($_SESSION['uid']);
					$operator=$points_rule['jobs_refresh']['type']=="1"?"+":"-";
					write_memberslog($_SESSION['uid'],1,9001,$_SESSION['username'],"{$jobs_num}件職位更新済み，({$operator}{$total_point})，(残る:{$user_points})",1,1003,"職位更新","{$operator}{$total_point}","{$user_points}");
				}
			}
		}	
		//套餐模式
		elseif($_CFG['operation_mode']=='2') 
		{
			$mode = 2;
			//限制刷新时间
			$link[0]['text'] = "サービスを有効にする";
			$link[0]['href'] = 'company_service.php?act=setmeal_list';
			$link[1]['text'] = "会員中心首页";
			$link[1]['href'] = 'company_index.php?act=';
			$setmeal=get_user_setmeal($_SESSION['uid']);
			if (empty($setmeal))
			{					
				showmsg("サービス有効にしていません，有効にしてくださ",1,$link);
			}
			elseif ($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
			{					
				showmsg("サービス期限切れた，再申し込みしてください",1,$link);
			}
			else
			{
				//最近一次的刷新时间
				$refrestime=get_last_refresh_date($_SESSION['uid'],"1001",2);
				$duringtime=time()-$refrestime['max(addtime)'];
				$space = $setmeal['refresh_jobs_space']*60;
				$refresh_time = get_today_refresh_times($_SESSION['uid'],"1001",2);
				if($setmeal['refresh_jobs_time']!=0&&($refresh_time['count(*)']>=$setmeal['refresh_jobs_time']))
				{
				showmsg("毎日最大更新件数".$setmeal['refresh_jobs_time']."回,今日最大更新回数を超えた！",2);
				}
				elseif($duringtime<=$space){
				showmsg($setmeal['refresh_jobs_space']."分以内、職位再度検索不可！",2);	
				}
			}
		}
		//混合模式
		elseif($_CFG['operation_mode']=='3') 
		{
			$setmeal=get_user_setmeal($_SESSION['uid']);
			//该会员套餐过期 (套餐过期后就用积分来刷)
			if($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
			{
				//后台开通  服务超限时启用积分消费
				if($_CFG['setmeal_to_points']=='1')
				{
					$mode = 1;
					//用积分来刷新职位的话->按照积分模式限制->先看它是否超过次数限制和时间间隔
					$refrestime=get_last_refresh_date($_SESSION['uid'],"1001",1);
					$duringtime=time()-$refrestime['max(addtime)'];
					$space = $setmeal['refresh_jobs_space']*60;
					$refresh_time = get_today_refresh_times($_SESSION['uid'],"1001",1);
					if($_CFG['com_pointsmode_refresh_time']!=0&&($refresh_time['count(*)']>=$_CFG['com_pointsmode_refresh_time']))
					{
						$link[0]['text'] = "前頁に戻る";
						$link[0]['href'] = 'javascript:history.go(-1)';
						$link[1]['text'] = "費用延期";
						$link[1]['href'] = 'company_service.php?act=setmeal_list';
						showmsg("コース期限切れた，職位更新はポイントが必要です，一日ポイントを使って職位更新最大件数".$_CFG['com_pointsmode_refresh_time']."次,今日更新回数制限を超えました，費用延期コースを申し込みしてください！",2,$link);	
					}
					elseif($duringtime<=$space)
					{
						$link[0]['text'] = "前頁に戻る";
						$link[0]['href'] = 'javascript:history.go(-1)';
						$link[1]['text'] = "費用延期";
						$link[1]['href'] = 'company_service.php?act=setmeal_list';
						showmsg("コース起源きれた，職位更新ポイントが必要です，しかしポイント足りない".$_CFG['com_pointsmode_refresh_space']."分以内重複更新できません！",2,$link);
					}
					else
					{
						$points_rule=get_cache('points_rule');
						if($points_rule['jobs_refresh']['value']>0)
						{
							$user_points=get_user_points($_SESSION['uid']);
							$total_point=$jobs_num*$points_rule['jobs_refresh']['value'];
							if ($total_point>$user_points && $points_rule['jobs_refresh']['type']=="2")
							{
								$link[0]['text'] = "前頁に戻る";
								$link[0]['href'] = 'javascript:history.go(-1)';
								$link[1]['text'] = "費用延期";
								$link[1]['href'] = 'company_service.php?act=setmeal_list';
								$link[2]['text'] = "即時振込";
								$link[2]['href'] = 'company_service.php?act=order_add';
								showmsg("コース期限切れた，職位更新がポイントが必要。しかし貴方の".$_CFG['points_byname']."ポイント不足，振込してまたは費用延期コースを申し込み！",0,$link);
							}
							//加/减 积分
							report_deal($_SESSION['uid'],$points_rule['jobs_refresh']['type'],$total_point);
							$user_points=get_user_points($_SESSION['uid']);
							$operator=$points_rule['jobs_refresh']['type']=="1"?"+":"-";
							write_memberslog($_SESSION['uid'],1,9001,$_SESSION['username'],"{$jobs_num}件職位更新済み，({$operator}{$total_point})，(残る:{$user_points})",1,1003,"職位更新","{$operator}{$total_point}","{$user_points}");
						}
					}
				}
				//后台没有开通  服务超限时启用积分消费
				else
				{
					$link[0]['text'] = "サービスを有効にする";
					$link[0]['href'] = 'company_service.php?act=setmeal_list';
					$link[1]['text'] = "会員中心首页";
					$link[1]['href'] = 'company_index.php?act=';
					showmsg("サービス期限切れた，再申し込みしてください",1,$link);
				}
			}
			//该会员套餐未过期 
			else
			{
				$mode = 2;
				$points_rule=get_cache('points_rule');
				$user_points=get_user_points($_SESSION['uid']);
				//获取当天刷新的职位数(在套餐模式下刷新的)
				$refresh_time = get_today_refresh_times($_SESSION['uid'],"1001",2);
				//当天剩余刷新职位数(在套餐模式下刷新的)
				$surplus_time =  $setmeal['refresh_jobs_time'] - $refresh_time['count(*)'];
				//刷新职位数 大于 剩余刷新职位数 (超了)
				if($setmeal['refresh_jobs_time']!=0&&($jobs_num>$surplus_time))
				{
					//后台开通  服务超限时启用积分消费
					if($_CFG['setmeal_to_points']=='1')
					{
						//判断当天积分刷新职位数 是否 超过次数和间隔限制
						$refrestime=get_last_refresh_date($_SESSION['uid'],"1001",1);
						$duringtime=time()-$refrestime['max(addtime)'];
						$space = $_CFG['com_pointsmode_refresh_space']*60;
						$refresh_time = get_today_refresh_times($_SESSION['uid'],"1001",1);
						if($_CFG['com_pointsmode_refresh_time']!=0&&($refresh_time['count(*)']>=$_CFG['com_pointsmode_refresh_time']))
						{
						showmsg("職位更新数コース回数制限を超えた，職位更新ポイントが必要です，毎日更新可能な数は".$_CFG['com_pointsmode_refresh_time']."回,今日最大更新回数を超えた！",2);	
						}
						elseif($duringtime<=$space)
						{
						showmsg("更新職位数はコース回数制限を超えた，職位更新ポイントが必要，かつ".$_CFG['com_pointsmode_refresh_space']."分以内、職位再度検索不可！",2);
						}
						else
						{
							if($points_rule['jobs_refresh']['value']>0)
							{
								//超出的职位若想刷新 所需的积分
								$beyond = $jobs_num - $surplus_time;
								$surplus_total_point=$beyond*$points_rule['jobs_refresh']['value'];
								//会员积分不足以满足 所需积分
								if ($surplus_total_point>$user_points && $points_rule['jobs_refresh']['type']=="2")
								{
									$link[0]['text'] = "前頁に戻る";
									$link[0]['href'] = 'javascript:history.go(-1)';
									$link[1]['text'] = "費用延期";
									$link[1]['href'] = 'company_service.php?act=setmeal_list';
									$link[2]['text'] = "即時振込";
									$link[2]['href'] = 'company_service.php?act=order_add';
									showmsg("職位更新数はコース回数制限を超えた，超過の回数はポイント必要です，しかし貴方の".$_CFG['points_byname']."ポイント足りない，振込してください！",0,$link);
								}
								//判断超出的职位数是否 大于 积分限制次数
								if($beyond > $_CFG['com_pointsmode_refresh_time'] && $_CFG['com_pointsmode_refresh_time']!=0)
								{
									showmsg("更新職位数はコース回数制限を超えました，ポイントが必要です，ポイントが足りません".$_CFG['points_byname']."制限回数，職位再選択してください！",0);
								}
								for ($i=0; $i < $surplus_time; $i++) 
								{ 
									refresh_jobs($yid[$i],$_SESSION['uid']);
									write_memberslog($_SESSION['uid'],1,2004,$_SESSION['username'],"職位更新");
									write_refresh_log($_SESSION['uid'],2,1001);
								}
								for ($i=$surplus_time; $i < $jobs_num; $i++) 
								{ 
									refresh_jobs($yid[$i],$_SESSION['uid']);
									write_memberslog($_SESSION['uid'],1,2004,$_SESSION['username'],"職位更新");
									write_refresh_log($_SESSION['uid'],1,1001);
								}
								//更新会员积分
								//加/减 积分
								report_deal($_SESSION['uid'],$points_rule['jobs_refresh']['type'],$surplus_total_point);
								$user_points=get_user_points($_SESSION['uid']);
								$operator=$points_rule['jobs_refresh']['type']=="1"?"+":"-";
								write_memberslog($_SESSION['uid'],1,9001,$_SESSION['username'],"{$jobs_num}件職位更新済み，({$operator}{$total_point})，(残る:{$user_points})",1,1003,"職位更新","{$operator}{$total_point}","{$user_points}");
								showmsg("職位更新成功！",2);
							}
						}
					}
					//后台没有开通  服务超限时启用积分消费
					else
					{
						$link[0]['text'] = "前頁に戻る";
						$link[0]['href'] = 'javascript:history.go(-1)';
						$link[1]['text'] = "費用延期";
						$link[1]['href'] = 'company_service.php?act=setmeal_list';
						showmsg("新職位の数はコースの設定を超過しました ! ",1,$link);
					}
				}
				//刷新职位数 小于 剩余刷新职位数 (没超)
				else
				{
					//最近一次的刷新时间
					$refrestime=get_last_refresh_date($_SESSION['uid'],"1001",2);
					$duringtime=time()-$refrestime['max(addtime)'];
					$space = $setmeal['refresh_jobs_space']*60;
					$refresh_time = get_today_refresh_times($_SESSION['uid'],"1001",2);
					if($setmeal['refresh_jobs_time']!=0&&($refresh_time['count(*)']>=$setmeal['refresh_jobs_time']))
					{
					showmsg("毎日最大更新件数".$setmeal['refresh_jobs_time']."回,今日最大更新回数を超えた！",2);
					}
					elseif($duringtime<=$space)
					{
					showmsg($setmeal['refresh_jobs_space']."分以内、職位再度検索不可！",2);	
					}
				}
			}
		}
		
		refresh_jobs($yid,$_SESSION['uid']);
		write_memberslog($_SESSION['uid'],1,2004,$_SESSION['username'],"職位更新");
		for ($i=0; $i < $jobs_num; $i++) { 
			write_refresh_log($_SESSION['uid'],$mode,1001);
		}
		showmsg("職位更新成功！",2);
	}
	elseif ($delete)
	{
		if($n=del_jobs($yid,$_SESSION['uid']))
		{
			showmsg("削除成功！削除行数 {$n} ",2);
		}
		else
		{
			showmsg("削除失敗！",2);
		}
	} 
	elseif (!empty($_REQUEST['display1']))
	{
		if($_CFG['operation_mode']=='1'){
			activate_jobs($yid,1,$_SESSION['uid']);
			showmsg("設定成功！",2);
		}else{
			$jobs_num= $db->get_total("select count(*) num from ".table("jobs")." where uid=$_SESSION[uid] and audit=1 and display=1 ");
			$jobs_tmp_num= $db->get_total("select count(*) num from ".table("jobs_tmp")." where uid=$_SESSION[uid] and audit<>3 and display=1 ");
			$com_jobs_num=$jobs_num+$jobs_tmp_num;
			$setmeal= get_user_setmeal($_SESSION['uid']);
			if ($com_jobs_num>=$setmeal['jobs_ordinary'])
			{
				showmsg("表示された職位は設定制限を超えました，サービスコースをアップグレードして，又は募集しない職位を閉じる！",1);
			}else
			{
				activate_jobs($yid,1,$_SESSION['uid']);
				showmsg("設定成功！",2);
			}
		}
	}
	elseif (!empty($_REQUEST['display2']))
	{
	activate_jobs($yid,2,$_SESSION['uid']);
	showmsg("設定成功！",2);
	}
}
//混合模式下  :  判断刷新职位是否需要消耗积分
elseif ($act=='ajax_mode_points')
{
	//要刷新的职位数
	$length = intval($_GET['length']);
	$points_rule=get_cache('points_rule');
	$setmeal=get_user_setmeal($_SESSION['uid']);
	//该会员套餐过期 (套餐过期后就用积分来操作)
	if($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
	{
		if($_CFG['setmeal_to_points']=='1' && $points_rule['jobs_refresh']['value']>"0")
		{
			exit('ok');
		}
	}
	//获取当天刷新的职位数(在套餐模式下刷新的)
	$refresh_time = get_today_refresh_times($_SESSION['uid'],"1001",2);
	//当天剩余刷新职位数(在套餐模式下刷新的)
	$surplus_time =  $setmeal['refresh_jobs_time'] - $refresh_time['count(*)'];
	//刷新职位数 大于 剩余刷新职位数 (超了)
	if($setmeal['refresh_jobs_time']!=0 && ($length>$surplus_time))
	{
		if($_CFG['setmeal_to_points']=='1' && $points_rule['jobs_refresh']['value']>"0")
		{
			exit('ok');
		}
	}
	exit('no');
}
elseif ($act=='editjobs')
{
	$jobs=get_jobs_one(intval($_GET['id']),$_SESSION['uid']);
	if (empty($jobs)) showmsg("パラメータエラー！",1);
	$jobs['contents'] = htmlspecialchars_decode($jobs['contents'],ENT_QUOTES);
	if($jobs['age']){
		$jobs_age = explode("-", $jobs['age']);
		$jobs['minage'] = $jobs_age[0];
		$jobs['maxage'] = $jobs_age[1];
	}
	$smarty->assign('user',$user);
	$smarty->assign('title','職位更新 - 企業会員センター - '.$_CFG['site_name']);
	$smarty->assign('points_total',get_user_points($_SESSION['uid']));
	$smarty->assign('points',get_cache('points_rule'));
	$smarty->assign('jobs',$jobs);
	$smarty->display('member_company/company_editjobs.htm');
}
elseif ($act=='editjobs_save')
{
	$id=intval($_POST['id']);
	$add_mode=trim($_POST['add_mode']);
	if ($add_mode=='1')
	{
					$points_rule=get_cache('points_rule');
					$user_points=get_user_points($_SESSION['uid']);
					if($points_rule['jobs_edit']['type']=="2" && $points_rule['jobs_edit']['value']>0)
					{
						$total=$points_rule['jobs_edit']['value'];
						if ($total>$user_points)
						{
						$link[0]['text'] = "前頁に戻る";
						$link[0]['href'] = 'javascript:history.go(-1)';
						$link[1]['text'] = "即時振込";
						$link[1]['href'] = 'company_service.php?act=order_add';
						showmsg("貴方の".$_CFG['points_byname']."ポイント不足，振込してください！",0,$link);
						}
					}
					
	}
	elseif ($add_mode=='2')
	{
					$link[0]['text'] = "サービスを有効にする";
					$link[0]['href'] = 'company_service.php?act=setmeal_list';
					$link[1]['text'] = "会員中心首页";
					$link[1]['href'] = 'company_index.php?act=';
				$setmeal=get_user_setmeal($_SESSION['uid']);
				if ($setmeal['endtime']<time() && $setmeal['endtime']<>"0")
				{					
					showmsg("コース期限切れた，再申し込みしてください",1,$link);
				}
	}

	$setsqlarr['jobs_name']=!empty($_POST['jobs_name'])?trim($_POST['jobs_name']):showmsg('職位を選択してください！',1);
	check_word($_CFG['filter'],$_POST['jobs_name'])?showmsg($_CFG['filter_tips'],0):'';
	$setsqlarr['nature']=intval($_POST['nature']);
	$setsqlarr['nature_cn']=trim($_POST['nature_cn']);
	$setsqlarr['topclass']=trim($_POST['topclass']);
	$setsqlarr['category']=!empty($_POST['category'])?intval($_POST['category']):showmsg('職業種類を選択してください！',1);
	$setsqlarr['subclass']=trim($_POST['subclass']);
	$setsqlarr['category_cn']=trim($_POST['category_cn']);
	$setsqlarr['amount']=intval($_POST['amount']);
	$setsqlarr['district']=!empty($_POST['district'])?intval($_POST['district']):showmsg('仕事地区を選択してください！',1);
	$setsqlarr['sdistrict']=intval($_POST['sdistrict']);
	$setsqlarr['district_cn']=trim($_POST['district_cn']);
	$setsqlarr['wage']=intval($_POST['wage'])?intval($_POST['wage']):showmsg('給料選択してください！',1);		
	$setsqlarr['wage_cn']=trim($_POST['wage_cn']);
	$setsqlarr['negotiable']=intval($_POST['negotiable']);
	$setsqlarr['tag']=trim($_POST['tag']);
	$setsqlarr['tag_cn']=trim($_POST['tag_cn']);
	$setsqlarr['sex']=intval($_POST['sex']);
	$setsqlarr['sex_cn']=trim($_POST['sex_cn']);
	$setsqlarr['education']=intval($_POST['education'])?intval($_POST['education']):showmsg('学歴要求を入力してください！',1);		
	$setsqlarr['education_cn']=trim($_POST['education_cn']);
	$setsqlarr['experience']=intval($_POST['experience'])?intval($_POST['experience']):showmsg('仕事経験を選択してください！',1);		
	$setsqlarr['experience_cn']=trim($_POST['experience_cn']);
	$setsqlarr['graduate']=intval($_POST['graduate']);
	$setsqlarr['age']=trim($_POST['minage'])."-".trim($_POST['maxage']);
	$setsqlarr['contents']=!empty($_POST['contents'])?trim($_POST['contents']):showmsg('職位説明を入力してください！',1); 
	
	check_word($_CFG['filter'],$_POST['contents'])?showmsg($_CFG['filter_tips'],0):'';
	if ($add_mode=='1'){
		$setsqlarr['setmeal_deadline']=0;
		$setsqlarr['add_mode']=1;
	}elseif($add_mode=='2'){
		$setmeal=get_user_setmeal($_SESSION['uid']);
		$setsqlarr['setmeal_deadline']=$setmeal['endtime'];
		$setsqlarr['setmeal_id']=$setmeal['setmeal_id'];
		$setsqlarr['setmeal_name']=$setmeal['setmeal_name'];
		$setsqlarr['add_mode']=2;
	}
	// 修改职位 过期时间为
	$setsqlarr['deadline']=strtotime("".intval($_CFG['company_add_days'])." day");
	$setsqlarr['key']=$setsqlarr['jobs_name'].$company_profile['companyname'].$setsqlarr['category_cn'].$setsqlarr['district_cn'].$setsqlarr['contents'];
	require_once(HIGHWAY_ROOT_PATH.'include/splitword.class.php');
	$sp = new SPWord();
	$setsqlarr['key']="{$setsqlarr['jobs_name']} {$company_profile['companyname']} ".$sp->extracttag($setsqlarr['key']);
	$setsqlarr['key']=$sp->pad($setsqlarr['key']);
	if ($company_profile['audit']=="1")
	{
	$_CFG['audit_verifycom_editjob']<>"-1"?$setsqlarr['audit']=intval($_CFG['audit_verifycom_editjob']):'';
	}
	else
	{
	$_CFG['audit_unexaminedcom_editjob']<>"-1"?$setsqlarr['audit']=intval($_CFG['audit_unexaminedcom_editjob']):'';
	}
	$setsqlarr_contact['contact']=!empty($_POST['contact'])?trim($_POST['contact']):showmsg('連絡先を入力してください！',1);
	check_word($_CFG['filter'],$_POST['contact'])?showmsg($_CFG['filter_tips'],0):'';
	$setsqlarr_contact['telephone']=!empty($_POST['telephone'])?trim($_POST['telephone']):showmsg('連絡電話入力してください！',1);
	check_word($_CFG['filter'],$_POST['telephone'])?showmsg($_CFG['filter_tips'],0):'';
	$setsqlarr_contact['address']=!empty($_POST['address'])?trim($_POST['address']):showmsg('連絡先を入力してください！',1);
	check_word($_CFG['filter'],$_POST['address'])?showmsg($_CFG['filter_tips'],0):'';
	$setsqlarr_contact['email']=!empty($_POST['email'])?trim($_POST['email']):showmsg('メールを入力してください！',1);
	check_word($_CFG['filter'],$_POST['email'])?showmsg($_CFG['filter_tips'],0):'';
	$setsqlarr_contact['notify']=intval($_POST['notify']);//邮件提醒
	$setsqlarr_contact['notify_mobile']=intval($_POST['notify_mobile']);//手机提醒
	
  	$setsqlarr_contact['contact_show']=intval($_POST['contact_show']);
	$setsqlarr_contact['email_show']=intval($_POST['email_show']);
	$setsqlarr_contact['telephone_show']=intval($_POST['telephone_show']);
	$setsqlarr_contact['address_show']=intval($_POST['address_show']);
 
	if (!$db->updatetable(table('jobs'), $setsqlarr," id='{$id}' AND uid='{$_SESSION['uid']}' ")) showmsg("保存失敗！",0);
	if (!$db->updatetable(table('jobs_tmp'), $setsqlarr," id='{$id}' AND uid='{$_SESSION['uid']}' ")) showmsg("保存失敗！",0);
	if (!$db->updatetable(table('jobs_contact'), $setsqlarr_contact," pid='{$id}' ")){
		showmsg("保存失敗！",0);
	}
	if ($add_mode=='1')
	{
		if ($points_rule['jobs_edit']['value']>0)
		{
		report_deal($_SESSION['uid'],$points_rule['jobs_edit']['type'],$points_rule['jobs_edit']['value']);
		$user_points=get_user_points($_SESSION['uid']);
		$operator=$points_rule['jobs_edit']['type']=="1"?"+":"-";
		write_memberslog($_SESSION['uid'],1,9001,$_SESSION['username'],"職位変更：<strong>{$setsqlarr['jobs_name']}</strong>，({$operator}{$points_rule['jobs_edit']['value']})，(残る:{$user_points})",1,1002,"募集情報変更","{$operator}{$points_rule['jobs_edit']['value']}","{$user_points}");
		}
		if ($days>0 && $points_rule['jobs_daily']['value']>0)
		{
		$points_day=intval($_POST['days'])*$points_rule['jobs_daily']['value'];
		report_deal($_SESSION['uid'],$points_rule['jobs_daily']['type'],$points_day);
		$user_points=get_user_points($_SESSION['uid']);
		$operator=$points_rule['jobs_daily']['type']=="1"?"+":"-";
		write_memberslog($_SESSION['uid'],1,9001,$_SESSION['username'],"延長職位({$_POST['jobs_name']})有效期間は{$_POST['days']}日，({$operator}{$points_day})，(残る:{$user_points})",1,1002,"募集情報変更","{$operator}{$points_day}","{$user_points}");
		}
	}	 
	$link[0]['text'] = "職位一覧";
	$link[0]['href'] = '?act=jobs';
	$link[1]['text'] = "変更結果閲覧";
	$link[1]['href'] = "?act=editjobs&id={$id}";
	$link[2]['text'] = "会員中心首页";
	$link[2]['href'] = "company_index.php";
	//
	$searchtab['nature']=$setsqlarr['nature'];
	$searchtab['sex']=$setsqlarr['sex'];
	$searchtab['topclass']=$setsqlarr['topclass'];
	$searchtab['category']=$setsqlarr['category'];
	$searchtab['subclass']=$setsqlarr['subclass'];
	$searchtab['district']=$setsqlarr['district'];
	$searchtab['sdistrict']=$setsqlarr['sdistrict'];
	$searchtab['education']=$setsqlarr['education'];
	$searchtab['experience']=$setsqlarr['experience'];
	$searchtab['wage']=$setsqlarr['wage'];
	$searchtab['graduate']=$setsqlarr['graduate'];	
	//
	$db->updatetable(table('jobs_search_wage'),$searchtab," id='{$id}' AND uid='{$_SESSION['uid']}' ");
	$db->updatetable(table('jobs_search_rtime'),$searchtab," id='{$id}' AND uid='{$_SESSION['uid']}' ");
	$db->updatetable(table('jobs_search_stickrtime'),$searchtab," id='{$id}' AND uid='{$_SESSION['uid']}' ");
	$db->updatetable(table('jobs_search_hot'),$searchtab," id='{$id}' AND uid='{$_SESSION['uid']}' ");
	$db->updatetable(table('jobs_search_scale'),$searchtab," id='{$id}' AND uid='{$_SESSION['uid']}'");
	$searchtab['key']=$setsqlarr['key'];
	$searchtab['likekey']=$setsqlarr['jobs_name'].','.$company_profile['companyname'];
	$db->updatetable(table('jobs_search_key'),$searchtab," id='{$id}' AND uid='{$_SESSION['uid']}' ");
	unset($searchtab);
	add_jobs_tag(intval($_POST['id']),$_SESSION['uid'],$_POST['tag'])?"":showmsg('保存失敗！',0);
	distribution_jobs($id,$_SESSION['uid']);
	write_memberslog($_SESSION['uid'],$_SESSION['utype'],2002,$_SESSION['username'],"職位修正：{$setsqlarr['jobs_name']}，職位ID：{$id}");
	showmsg("変更成功！",2,$link);
}
elseif($act == "get_content_by_jobs_cat"){
	$id = intval($_GET['id']);
	if($id>0){
		$content = get_content_by_jobs_cat($id);
		if(!empty($content)){
			exit($content);
		}else{
			exit("-1");
		}
	}else{
		exit("-1");
	}
}
unset($smarty);
?>
