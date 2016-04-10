<?php
/**
 * This file is a part of the CIDRAM package, and can be downloaded for free
 * from {@link https://github.com/Maikuolan/CIDRAM/ GitHub}.
 *
 * CIDRAM COPYRIGHT 2016 and beyond by Caleb Mazalevskis (Maikuolan).
 *
 * License: GNU/GPLv2
 * @see LICENSE.txt
 *
 * This file: Chinese (simplified) language data (last modified: 2016.04.03).
 */

/** Prevents execution from outside of CIDRAM. */
if (!defined('CIDRAM')) {
    die('[CIDRAM] This should not be accessed directly.');
}

$CIDRAM['lang']['click_here'] = '点击这里';
$CIDRAM['lang']['denied'] = '拒绝访问！';
$CIDRAM['lang']['Error_WriteCache'] = '无法写入缓存！请检查您的CHMOD文件的权限！';
$CIDRAM['lang']['field_datetime'] = '日期/时间：';
$CIDRAM['lang']['field_id'] = 'ID：';
$CIDRAM['lang']['field_ipaddr'] = 'IP地址：';
$CIDRAM['lang']['field_query'] = '网页查询：';
$CIDRAM['lang']['field_referrer'] = '引荐：';
$CIDRAM['lang']['field_scriptversion'] = '脚本版本：';
$CIDRAM['lang']['field_sigcount'] = '签名计数：';
$CIDRAM['lang']['field_sigref'] = '签名参考：';
$CIDRAM['lang']['field_whyreason'] = '为何受阻：';
$CIDRAM['lang']['field_ua'] = '用户代理：';
$CIDRAM['lang']['field_rURI'] = '重建URI：';
$CIDRAM['lang']['generated_by'] = '所产生通过';
$CIDRAM['lang']['preamble'] = '-- 结束序言。添加您的问题或意见该行之后。 --';
$CIDRAM['lang']['ReasonMessage_BadIP'] = '您的访问这个页面被拒绝因为您试图访问该页面使用一个无效的IP地址。';
$CIDRAM['lang']['ReasonMessage_Bogon'] = '您的访问这个页面被拒绝因为您的IP地址被识别作为火星IP地址，和来自这些IP连接不是由网站所有者允许。';
$CIDRAM['lang']['ReasonMessage_Cloud'] = '您的访问这个页面被拒绝因为您的IP地址被识别为属于云服务，和来自这些IP连接不是由网站所有者允许。';
$CIDRAM['lang']['ReasonMessage_Generic'] = '您的访问这个页面被拒绝因为您的IP地址属于一个网络在黑名单中所列使用本网站。';
$CIDRAM['lang']['ReasonMessage_Spam'] = '您的访问这个页面被拒绝因为您的IP地址属于一个网络被认为是高风险的垃圾邮件。';
$CIDRAM['lang']['Short_BadIP'] = '无效的IP！';
$CIDRAM['lang']['Short_Bogon'] = '火星IP';
$CIDRAM['lang']['Short_Cloud'] = '云服务';
$CIDRAM['lang']['Short_Generic'] = '通用';
$CIDRAM['lang']['Short_Spam'] = '垃圾邮件的风险';
$CIDRAM['lang']['Support_Email'] = '如果您认为这是错误的，或寻求援助，{ClickHereLink}发送电子邮件支持票本网站的网站管理员（请不要改变序言或主题行）。';

$CIDRAM['lang']['CLI_H'] = "
 CIDRAM CLI模式辅助。

 用法：
 /path/to/php/php.exe /path/to/cidram/loader.php -键 （输入）

 键：
    -h  显示此帮助信息。
    -c  检查如果一个IP地址被阻止由CIDRAM签名文件。
    -g  生成CIDR从一个IP地址。

 输入： 可以是任何有效的地址（IPv4/IPv6）。

 例子：
        -c  192.168.0.0/16
        -c  127.0.0.1/32
        -c  2001:db8::/32
        -c  2002::1/128

";

$CIDRAM['lang']['CLI_Bad_IP'] = ' 指定的IP地址，“{IP}”，不是有效地址（IPv4/IPv6）！';
$CIDRAM['lang']['CLI_IP_Blocked'] = ' 指定的IP地址，“{IP}”，是阻塞由一个或多签名文件。';
$CIDRAM['lang']['CLI_IP_Not_Blocked'] = ' 指定的IP地址，“{IP}”，不是阻塞由任何签名文件。';