﻿<?php
define('IN_HIGHWAY', true);
require_once(dirname(__FILE__).'/../data/config.php');
require_once(dirname(__FILE__).'/include/admin_common.inc.php');
$act = !empty($_GET['act']) ? trim($_GET['act']) : 'email_set';
check_permissions($_SESSION['admin_purview'],"site_mail");
$smarty->assign('pageheader',"メール設定");
if($act == 'email_set')
{
	get_token();
	$mailconfig=get_cache('mailconfig');
	$mailconfig['smtpservers']=explode('|-_-|',$mailconfig['smtpservers']);
	$mailconfig['smtpusername']=explode('|-_-|',$mailconfig['smtpusername']);
	$mailconfig['smtppassword']=explode('|-_-|',$mailconfig['smtppassword']);
	$mailconfig['smtpfrom']=explode('|-_-|',$mailconfig['smtpfrom']);
	$mailconfig['smtpport']=explode('|-_-|',$mailconfig['smtpport']);
	for ($i=0; $i<count($mailconfig['smtpservers']); $i++)
	{
	$mailconfigli[]=array('smtpservers'=>$mailconfig['smtpservers'][$i],'smtpusername'=>$mailconfig['smtpusername'][$i],'smtppassword'=>$mailconfig['smtppassword'][$i],'smtpfrom'=>$mailconfig['smtpfrom'][$i],'smtpport'=>$mailconfig['smtpport'][$i]);
	}
	$smarty->assign('mailconfig',$mailconfig);
	$smarty->assign('mailconfigli',$mailconfigli);
	$smarty->assign('navlabel','set');
	$smarty->display('mail/admin_mail_set.htm');
}
elseif($act == 'email_set_save')
{
	check_token();
	header("Cache-control: private");
	if (intval($_POST['method'])=="1")
	{
		for ($i=0; $i<count($_POST['smtpservers']); $i++)
		{
			 if (empty($_POST['smtpservers'][$i]) || empty($_POST['smtpusername'][$i]) || empty($_POST['smtppassword'][$i]) || empty($_POST['smtpfrom'][$i]) || empty($_POST['smtpport'][$i]))
			{
			adminmsg('資料を追加してください!',1);
			}
		}
		$_POST['smtpservers']=implode('|-_-|',$_POST['smtpservers']);
		$_POST['smtpusername']=implode('|-_-|',$_POST['smtpusername']);
		$_POST['smtppassword']=implode('|-_-|',$_POST['smtppassword']);
		$_POST['smtpfrom']=implode('|-_-|',$_POST['smtpfrom']);
		$_POST['smtpport']=implode('|-_-|',$_POST['smtpport']);
	}
	foreach($_POST as $k => $v){
	!$db->query("UPDATE ".table('mailconfig')." SET value='$v' WHERE name='$k'")?adminmsg('設定更新失敗', 1):"";
	}
	refresh_cache('mailconfig');
	write_log("メール配置設定", $_SESSION['admin_name'],3);
	adminmsg("保存成功！",2);
}
if($act == 'testing')
{
	get_token();
	$smarty->assign('navlabel','testing');
	$smarty->display('mail/admin_mail_testing.htm');
}
elseif($act == 'email_testing')
{
	check_token();
	$mailconfig=get_cache('mailconfig');
	$txt="こんにちは！メールサービス器設定のテストメールです。このメール受信したら，メールサービス設定が正いです！メール送信の操作ができました！";
	$check_smtp=trim($_POST['check_smtp'])?trim($_POST['check_smtp']):adminmsg('宛先アドレスを入力してください', 1);
	if (!preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/",$check_smtp))adminmsg('emailフォーマットエラー！',1);
	if (smtp_mail($check_smtp,"海威人材テストメール",$txt))
	{
	write_log("テストメール送信成功！", $_SESSION['admin_name'],3);
	adminmsg('テストメール送信成功！',2);
	}
	else
	{
	adminmsg('テストメール送信失敗！',1);
	}
}
elseif($act == 'email_set_templates')
{
	get_token();
	$smarty->assign('navlabel','templates');
	$smarty->assign('mailconfig',get_cache('mailconfig'));
	$smarty->display('mail/admin_mail_templates.htm');
}
elseif($act == 'rule')
{
	get_token();
	$smarty->assign('navlabel','rule');
	$smarty->assign('mailconfig',get_cache('mailconfig'));
	$smarty->display('mail/admin_mail_rule.htm');
}
elseif($act == 'email_rule_save')
{
	check_token();
	foreach($_POST as $k => $v)
	{
	!$db->query("UPDATE ".table('mailconfig')." SET value='$v' WHERE name='$k'")?adminmsg('設定更新失敗', 1):"";
	}
	refresh_cache('mailconfig');
	write_log("メール送信ルール設定", $_SESSION['admin_name'],3);
	adminmsg("保存成功！",2);
}
elseif($act == 'mail_templates_edit')
{
	get_token();
	$templates_name=trim($_GET['templates_name']);
	$label=array();
	$label[]=array('{sitename}','ウェブ名');
	$label[]=array('{sitedomain}','ドメイン');
	//生成标签
	if ($templates_name=='set_reg')
	{
	$label[]=array('{username}','ユーザ名');
	$label[]=array('{password}','パスワード');
	$label[]=array('{utype}','会員タイプ');
	}
	elseif ($templates_name=='set_applyjobs')
	{
	$label[]=array('{personalfullname}','申込者');
	$label[]=array('{jobsname}','申し込み職位名称');
	}
	elseif ($templates_name=='set_invite')
	{
	$label[]=array('{companyname}','誘い元(会社名称)');
	}
	elseif ($templates_name=='set_order')
	{
	$label[]=array('{paymenttpye}','支払い方式');
	$label[]=array('{amount}','金額');
	$label[]=array('{oid}','オーダー番号');
	}
	elseif ($templates_name=='set_editpwd')
	{
	$label[]=array('{newpassword}','新パスワード');
	}
	elseif ($templates_name=='set_jobsallow' || $templates_name=='set_jobsnotallow')
	{
	$label[]=array('{jobsname}','職位名称');
	}
	//-end
	if ($templates_name)
	{
		$sql = "select * from ".table('mail_templates')." where name='".$templates_name."'";
	$info=$db->getone($sql);
		$sql = "select * from ".table('mail_templates')." where name='".$templates_name."_title'";
	$title=$db->getone($sql);
	}
	$info['thisname']=trim($_GET['thisname']);
	$smarty->assign('info',$info);
	$smarty->assign('title',$title);
 	$smarty->assign('label',$label);
	$smarty->assign('navlabel','templates');
	$smarty->display('mail/admin_mail_templates_edit.htm');
}
elseif($act == 'templates_save')
{
	check_token();
	$templates_value=trim($_POST['templates_value']);
	$templates_name=trim($_POST['templates_name']);
	$title=trim($_POST['title']);
	!$db->query("UPDATE ".table('mail_templates')." SET value='".$templates_value."' WHERE name='".$templates_name."'")?adminmsg('设置失敗', 1):"";
	!$db->query("UPDATE ".table('mail_templates')." SET value='".$title."' WHERE name='".$templates_name."_title'")?adminmsg('设置失敗', 1):"";
	$link[0]['text'] = "前頁に戻る";
	$link[0]['href'] ="?act=email_set_templates";
	refresh_cache('mail_templates');
	write_log("変更メール送信テンプレート", $_SESSION['admin_name'],3);
	adminmsg("保存成功！",2,$link);
}
 elseif($act == 'send')
{
	get_token();
	$smarty->assign('pageheader',"メールセールス");
	
	require_once(dirname(__FILE__).'/include/admin_mailqueue_fun.php');
	require_once(HIGHWAY_ROOT_PATH.'include/page.class.php');
	$uid=intval($_GET['uid']);
	$email=trim($_GET['email']);
	
	$wheresql=' WHERE m_uid='.$uid.' ORDER BY m_id DESC ';
	$total_sql="SELECT COUNT(*) AS num FROM ".table('mailqueue').$wheresql;
	$perpage=10;
	$page = new page(array('total'=>$db->get_total($total_sql), 'perpage'=>$perpage));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$maillog = get_mailqueue($offset,$perpage,$wheresql);
	
	$url=trim($_REQUEST['url']);
	if (empty($url))
	{
	$url="?act=send&email={$email}&uid={$uid}";
	}
	$smarty->assign('url',$url);
	$smarty->assign('maillog',$maillog);
	$smarty->assign('page',$page->show(3));
	$smarty->display('mail/admin_mail_send.htm');
}
elseif($act == 'email_send')
{
	check_token();
	$uid=intval($_POST['uid']);
	$url=trim($_REQUEST['url']);
	if (!$uid)
	{
	adminmsg('ユーザUIDエラー！',0);
	}
	$setsqlarr['m_mail']=trim($_POST['email'])?trim($_POST['email']):adminmsg('メールアドレスを入力してください！',1);
	if (!preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/",$setsqlarr['m_mail'])) 
    {
	adminmsg('メールフォーマットエラー！',1);
    }
	$setsqlarr['m_subject']=trim($_POST['subject'])?trim($_POST['subject']):adminmsg('タイトルを入力してください！',1);	
	$setsqlarr['m_body']=trim($_POST['body'])?trim($_POST['body']):adminmsg('メール内容が必須！',1);
	$setsqlarr['m_addtime']=time();
	$setsqlarr['m_uid']=$uid;
	if(smtp_mail($setsqlarr['m_mail'],$setsqlarr['m_subject'],$setsqlarr['m_body'])){
		$setsqlarr['m_sendtime']=time();
		$setsqlarr['m_type']=1;//发送成功
		$db->inserttable(table('mailqueue'),$setsqlarr);
		unset($setsqlarr);
		$link[0]['text'] = "前頁に戻る";
		$link[0]['href'] = "{$url}";
		adminmsg("送信成功！",2,$link);
	}
	else
	{
		$setsqlarr['m_sendtime']=time();
		$setsqlarr['m_type']=2;//发送失败
		$db->inserttable(table('mailqueue'),$setsqlarr);
		unset($setsqlarr);
		$link[0]['text'] = "前頁に戻る";
		$link[0]['href'] = "{$url}";
		adminmsg("送信失敗，エラー未知！",0,$link);
	}
}
elseif ($act=='again_send')
{
	$id=intval($_GET['id']);
	if (empty($id))
	{
	adminmsg("送信の项目を選択してください！",1);
	}
	$result = $db->getone("SELECT * FROM ".table('mailqueue')." WHERE  m_id = {$id} limit 1");
	$wheresql=" m_id={$id} ";
	if(smtp_mail($result['m_mail'],$result['m_subject'],$result['m_body'])){
		$setsqlarr['m_sendtime']=time();
		$setsqlarr['m_type']=1;//发送成功
		!$db->updatetable(table('mailqueue'),$setsqlarr,$wheresql);
		adminmsg('送信成功',2);
	}else{
		$setsqlarr['m_sendtime']=time();
		$setsqlarr['m_type']=2;
		!$db->updatetable(table('mailqueue'),$setsqlarr,$wheresql);
		adminmsg('送信失敗',0);
	}
		
}
elseif ($act=='del')
{
	$id=$_POST['id'];
	if (empty($id))
	{
	adminmsg("項目を選択してください！",1);
	}
	if(!is_array($id)) $id=array($id);
	$sqlin=implode(",",$id);
	if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
	{
	$db->query("Delete from ".table('mailqueue')." WHERE m_id IN ({$sqlin}) ");
	adminmsg("削除成功",2);
	}
}
// 邮件日志
elseif($act == "log")
{
	get_token();
	require_once(HIGHWAY_ROOT_PATH.'include/page.class.php');
	$key=isset($_GET['key'])?trim($_GET['key']):"";
	$key_type=isset($_GET['key_type'])?intval($_GET['key_type']):"";
	if (!empty($key) && $key_type>0)
	{
		
		if     ($key_type===1)$wheresql=" WHERE subject like '%{$key}%'";
		if     ($key_type===2)$wheresql=" WHERE send_to = '{$key}'";
		if     ($key_type===3)$wheresql=" WHERE send_from = '{$key}'";
		$oederbysql="";
	}
	$_GET['state']<>''? $wheresqlarr['state']=intval($_GET['state']):'';
	if (!empty($wheresqlarr)) $wheresql=wheresql($wheresqlarr);
	$total_sql="SELECT COUNT(*) AS num FROM ".table('sys_email_log').$wheresql;
	$page = new page(array('total'=>$db->get_total($total_sql), 'perpage'=>$perpage));
	$currenpage=$page->nowindex;
	$offset=($currenpage-1)*$perpage;
	$list = get_mail_log($offset,$perpage,$wheresql.$oderbysql);

	$smarty->assign('list',$list);
	$smarty->assign('page',$page->show(3));
	$smarty->assign('navlabel','log');
	$smarty->display('mail/admin_mail_log.htm');
}
?>
