﻿<?php
define('IN_HIGHWAY', true);
require_once(dirname(__FILE__).'/../include/common.inc.php');
$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'app';
require_once(HIGHWAY_ROOT_PATH.'include/mysql.class.php');
$db = new mysql($dbhost,$dbuser,$dbpass,$dbname);
if((empty($_SESSION['uid']) || empty($_SESSION['username']) || empty($_SESSION['utype'])) &&  $_COOKIE['QS']['username'] && $_COOKIE['QS']['password'] && $_COOKIE['QS']['uid'])
{
	require_once(HIGHWAY_ROOT_PATH.'include/fun_user.php');
	if(check_cookie($_COOKIE['QS']['uid'],$_COOKIE['QS']['username'],$_COOKIE['QS']['password']))
	{
	update_user_info($_COOKIE['QS']['uid'],false,false);
	header("Location:".get_member_url($_SESSION['utype']));
	}
	else
	{
	unset($_SESSION['uid'],$_SESSION['username'],$_SESSION['utype'],$_SESSION['uqqid'],$_SESSION['activate_username'],$_SESSION['activate_email'],$_SESSION["openid"]);
	setcookie("QS[uid]","",time() - 3600,$HW_cookiepath, $HW_cookiedomain);
	setcookie('QS[username]',"", time() - 3600,$HW_cookiepath, $HW_cookiedomain);
	setcookie('QS[password]',"", time() - 3600,$HW_cookiepath, $HW_cookiedomain);
	setcookie("QS[utype]","",time() - 3600,$HW_cookiepath, $HW_cookiedomain);
	}
}
if ($_SESSION['uid']=='' || $_SESSION['username']=='')
{
	$captcha=get_cache('captcha');
	$smarty->assign('verify_userlogin',$captcha['verify_userlogin']);
	$smarty->display('plus/ajax_login.htm');
	exit();
}
if ($_SESSION['utype']!='1')
{
	exit('<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableall">
		    <tr>
				<td width="20" align="right"></td>
				<td>
					必须是企业会员才可以举报简历信息！
				</td>
		    </tr>
		</table>');
}
require_once(HIGHWAY_ROOT_PATH.'include/fun_company.php');
$user=get_user_info($_SESSION['uid']);
if ($user['status']=="2") 
{
	exit('<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableall">
		    <tr>
				<td width="20" align="right"></td>
				<td>
					您的账号处于暂停状态，请联系管理员设为正常后进行操作！
				</td>
		    </tr>
		</table>');
}
if ($act=="report")
{		
		$id=isset($_GET['resume_id'])?$_GET['resume_id']:exit("id 失った");
		$resume=get_resume_basic($id);
		if (empty($resume))
		{
			exit('<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableall">
			    <tr>
					<td width="20" align="right"></td>
					<td>
						举报信息失败！
					</td>
			    </tr>
			</table>');
		} 
		
		if (check_resume_report($_SESSION['uid'],intval($_GET['resume_id'])))
		{
			exit('<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableall">
			    <tr>
					<td width="20" align="right"></td>
					<td>
						您已经举报过此简历！
					</td>
			    </tr>
			</table>');
		}
?>
<script type="text/javascript">
$(".but80").hover(function(){$(this).addClass("but80_hover")},function(){$(this).removeClass("but80_hover")});
//计算今天申请数量

//验证
$("#ajax_report").click(function() {
	var content=$("#content").val();
	if (content=="")
	{
	alert("説明を入力してください");
	}
	else
	{
		$(".report-dialog").hide();
		$("#waiting").show();
		
		$.post("<?php echo $_CFG['site_dir'] ?>user/user_report_resume.php", { "resume_id": $("#resume_id").val(),"full_name": $("#full_name").val(),"content": $("#content").val(),"report_type":$('input[name="report_type"]:checked').val(),"resume_addtime":$("#resume_addtime").val(),"act":"app_save"},

	 	function (data,textStatus)
	 	 {
			if (data=="ok")
			{
				$(".report-dialog").hide();
				$("#waiting").hide();
				$("#app_ok").show();
			}
			else
			{
				$(".report-dialog").show();
				$("#waiting").hide();
				$("#app_ok").hide();
				$("#error_msg").html("報告失敗！"+data);
				$("#error").show();
			}
	 	 });
	}
});
</script>
<div class="report-dialog">
	<input type="hidden" id="resume_id" value="<?php echo intval($_GET['resume_id']);?>">
	<input type="hidden" id="full_name" value="<?php echo trim($_GET['full_name']);?>">
	<input type="hidden" id="resume_addtime" value="<?php echo trim($_GET['resume_addtime']);?>">
	<div class="report-item clearfix">
		<div class="report-type f-left">举报原因：</div>
		<div class="report-content f-left">
			<label><input type="radio" name="report_type"  class="radio" value="1" checked="checked"/>信息虚假<span>（乱写、乱填等无意义内容）</span></label>
			<label><input type="radio" name="report_type"  class="radio" value="2" />电话不通<span>（电话多次未通）</span></label>
			<label><input type="radio" name="report_type"  class="radio" value="3" />其它原因<span>（如中介等）</span></label>
		</div>
	</div>
	<div class="report-item clearfix">
		<div class="report-type f-left">相关描述：</div>
		<div class="report-content f-left">
			<textarea name="content" id="content" cols="30" rows="10"></textarea>
		</div>
	</div>
	<span class="r-all-row">一经核实，我们会立即... </span>
	<div class="report-item clearfix">
		<div class="report-type f-left">&nbsp;</div>
		<div class="report-content f-left">
			<p class="del-info">删除信息，为民除害 </p>
			<p class="del-info">站内信通知您 </p>
		</div>
	</div>
	<div class="center-btn-box">
		<input type="button" value="報告" class="btn-65-30blue btn-big-font " id="ajax_report"/><input type="button" value="取消" class="btn-65-30grey btn-big-font DialogClose" />
	</div>
	<p class="jubao-tip" style="padding-left: 10px;">温馨提示：找份工作不容易，请您如实举报哦！</p>
</div>

<table width="100%" border="0" cellspacing="5" cellpadding="0" id="waiting"  style="display:none">
  <tr>
    <td align="center" height="60"><img src="<?php echo  $_CFG['site_template']?>images/30.gif"  border="0"/></td>
  </tr>
  <tr>
    <td align="center" >请稍后...</td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableall" id="app_ok" style="display:none">
    <tr>
		<td width="140" align="right"><img height="100" src="<?php echo  $_CFG['site_template']?>images/big-yes.png" /></td>
		<td>
			<strong style="font-size:14px ; color:#0066CC;margin-left:20px">举报成功，管理员会认真处理!</strong>
		</td>
    </tr>
</table>
<table width="100%" border="0" cellspacing="5" cellpadding="0" id="error"  style="display:none">
  <tr>
    <td align="center" id="error_msg"></td>
  </tr>
</table>
<?php
}
elseif ($act=="app_save")
{
	$setsqlarr['content']=trim($_POST['content'])?trim($_POST['content']):exit("関連説明を入力してください！");
	$setsqlarr['resume_id']=$_POST['resume_id']?intval($_POST['resume_id']):exit("履歴書idが見つかりません！");
	$setsqlarr['resume_addtime']=intval($_POST['resume_addtime']);
	$setsqlarr['uid']=intval($_SESSION['uid']);
	$setsqlarr['addtime']=time();
	$setsqlarr['report_type']=intval($_POST['report_type']); // 投诉类型
	$setsqlarr['content']=iconv("utf-8", "gbk", $setsqlarr['content']);
	$resume=get_resume_basic($setsqlarr['resume_id']);
	if (empty($resume))
	{
	exit("履歴書失った");
	}
	else
	{
		if ($resume['display_name']=="2")
		{
			$setsqlarr['title']="N".str_pad($resume['id'],7,"0",STR_PAD_LEFT);
		}
		elseif($resume['display_name']=="3")
		{
			if($resume['sex']==1)
			{
				$setsqlarr['title']=cut_str($resume['fullname'],1,0,"男");
			}
			elseif($resume['sex'] == 2)
			{
				$setsqlarr['title']=cut_str($resume['fullname'],1,0,"女");
			}
		}
		else
		{
			$setsqlarr['title']=$resume['fullname'];
		}
		$insert_id = $db->inserttable(table('report_resume'),$setsqlarr,1);
	}
	if($insert_id)
	{
	exit("ok");
	}
}

?>
