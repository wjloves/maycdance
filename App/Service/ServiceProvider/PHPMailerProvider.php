<?php
namespace App\Service\ServiceProvider;

use App\Service\Helper\PHPMailer;
use Illuminate\Support\ServiceProvider;

class PHPMailerProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('mail' , function($app) {
            return new PHPMailer(true);
        });
    }

    public function boot()
    {

        $mail = $this->app->make('mail');

        $config = $this->app->config['config.mail'];
        $mail->CharSet    = $config['charset'];                 //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置为 UTF-8
        $config['isSMTP'] && $mail->IsSMTP();                            // 设定使用SMTP服务
        $mail->SMTPAuth   = $config['SMTPAuth'];                   // 启用 SMTP 验证功能
        $mail->SMTPSecure = $config['SMTPSecure'];                  // SMTP 安全协议
        $mail->Host       = $config['host'];       // SMTP 服务器
        $mail->Port       =  $config['port'];                   // SMTP服务器的端口号
        $mail->Username   = $config['username'];   // SMTP服务器用户名
        $mail->Password   = $config['password'];         // SMTP服务器密码
        $mail->SetFrom($config['senderAddress'] );    // 设置发件人地址和名称

        $mail->AltBody    = "为了查看该邮件，请切换到支持 HTML 的邮件客户端";     // 可选项，向下兼容考虑
        //    $mail->AddReplyTo("邮件回复人地址","邮件回复人名称");      // 设置邮件回复人地址和名称
        //   $mail->Subject    = '';                     // 设置邮件标题
   //     $mail->MsgHTML('');                         // 设置邮件内容
 //       $mail->AddAddress('收件人地址', "收件人名称");
        //$mail->AddAttachment("images/phpmailer.gif"); // 附件
//        $mail->Send()

    }
}