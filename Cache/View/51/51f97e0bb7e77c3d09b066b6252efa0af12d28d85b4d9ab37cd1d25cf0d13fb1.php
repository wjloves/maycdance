<?php

/* Front/login.html.twig */
class __TwigTemplate_b644bdb54b8a65d736b005529595c2a76bd7ec30c7172ce5bf6ec47793eb186d extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("layout.html.twig", "Front/login.html.twig", 1);
        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 2
    public function block_title($context, array $blocks = array())
    {
        echo "杏娱";
    }

    // line 3
    public function block_content($context, array $blocks = array())
    {
        // line 4
        echo "<div class=\"main\">
    <form method=\"post\" name=\"login_form\" action=\"/login\" autocomplete=\"off\">
        <div class=\"content\">
            <div class=\"cover\"></div>
            <div class=\"panel\">
                <div class=\"head\"></div>
                <div class=\"title\">杏娱通行证登录</div>
                <div class=\"seperator1\"></div><div class=\"seperator2\"></div><div class=\"seperator1\"></div>
                <div class=\"username\">
                    <div class=\"background\">
                        <div class=\"picture\"></div>
                        <div class=\"text\"><input type=\"text\" tabindex=\"1\" type=\"text\" maxlength=\"20\" name=\"login[username]\" id=\"js-username\" placeholder=\"用户名\" /></div>
                    </div>
                </div>
                <div class=\"password\">
                    <div class=\"background\">
                        <div class=\"picture\"></div>
                        <div class=\"text\"><input type=\"password\" tabindex=\"2\" type=\"password\" maxlength=\"20\" name=\"login[passwd]\" id=\"js-password\" value=\"\"/></div>
                    </div>
                </div>
                <div class=\"verifier\" style=\"display:none\">
                    <div class=\"text\"><input name=\"validate\" type=\"text\" id=\"vdcode\" /></div>
                    <div class=\"image\"><img id=\"vdimgck\" src=\"index.php/validate_image\" alt=\"看不清？点击更换\" width=\"97\" height=\"39\" align=\"absmiddle\" style=\"cursor:pointer\" onclick=\"this.src=this.src+'?'\" /></div>
                </div>
                <div class=\"remember\">
                    <div class=\"left\"><div class=\"check agreed\"></div>记住用户名</div>
                    <div class=\"right\"><a href=\"/?ct=forget_password\">忘记密码？</a></div>
                </div>
                <div class=\"login\">
                    <input name=\"hiddenCode\" value=\"1\" type=\"hidden\"/>
                    <img id=\"login_btn\" src=\"./images/v1/long_login_btn.png\" alt=\"long_login\" />
                    <button class=\"btn btn-large btn-red\">登录</button>
                </div>
                <div class=\"register\">
                    还没有账户？<a href=\"/reg?goto=>\">点此注册</a>
                </div>
            </div>
        </div>
    </form>
</div>
";
    }

    public function getTemplateName()
    {
        return "Front/login.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  38 => 4,  35 => 3,  29 => 2,  11 => 1,);
    }
}
/* {% extends "layout.html.twig" %}*/
/* {% block title %}杏娱{% endblock %}*/
/* {% block content %}*/
/* <div class="main">*/
/*     <form method="post" name="login_form" action="/login" autocomplete="off">*/
/*         <div class="content">*/
/*             <div class="cover"></div>*/
/*             <div class="panel">*/
/*                 <div class="head"></div>*/
/*                 <div class="title">杏娱通行证登录</div>*/
/*                 <div class="seperator1"></div><div class="seperator2"></div><div class="seperator1"></div>*/
/*                 <div class="username">*/
/*                     <div class="background">*/
/*                         <div class="picture"></div>*/
/*                         <div class="text"><input type="text" tabindex="1" type="text" maxlength="20" name="login[username]" id="js-username" placeholder="用户名" /></div>*/
/*                     </div>*/
/*                 </div>*/
/*                 <div class="password">*/
/*                     <div class="background">*/
/*                         <div class="picture"></div>*/
/*                         <div class="text"><input type="password" tabindex="2" type="password" maxlength="20" name="login[passwd]" id="js-password" value=""/></div>*/
/*                     </div>*/
/*                 </div>*/
/*                 <div class="verifier" style="display:none">*/
/*                     <div class="text"><input name="validate" type="text" id="vdcode" /></div>*/
/*                     <div class="image"><img id="vdimgck" src="index.php/validate_image" alt="看不清？点击更换" width="97" height="39" align="absmiddle" style="cursor:pointer" onclick="this.src=this.src+'?'" /></div>*/
/*                 </div>*/
/*                 <div class="remember">*/
/*                     <div class="left"><div class="check agreed"></div>记住用户名</div>*/
/*                     <div class="right"><a href="/?ct=forget_password">忘记密码？</a></div>*/
/*                 </div>*/
/*                 <div class="login">*/
/*                     <input name="hiddenCode" value="1" type="hidden"/>*/
/*                     <img id="login_btn" src="./images/v1/long_login_btn.png" alt="long_login" />*/
/*                     <button class="btn btn-large btn-red">登录</button>*/
/*                 </div>*/
/*                 <div class="register">*/
/*                     还没有账户？<a href="/reg?goto=>">点此注册</a>*/
/*                 </div>*/
/*             </div>*/
/*         </div>*/
/*     </form>*/
/* </div>*/
/* {% endblock %}*/
/* */
