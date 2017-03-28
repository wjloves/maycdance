jQuery.extend(jQuery.validator.messages, {
    required: "必填字段",
    remote: "请修正该字段",
    email: "请输入正确格式的电子邮件",
    url: "请输入合法的网址",
    date: "请输入合法的日期",
    dateISO: "请输入合法的日期 (ISO).",
    number: "请输入合法的数字",
    digits: "只能输入整数",
    creditcard: "请输入合法的信用卡号",
    equalTo: "请再次输入相同的值",
    accept: "请输入拥有合法后缀名的字符串",
    maxlength: jQuery.validator.format("请输入一个 长度最多是 {0} 的字符串"),
    minlength: jQuery.validator.format("请输入一个 长度最少是 {0} 的字符串"),
    rangelength: jQuery.validator.format("请输入 一个长度介于 {0} 和 {1} 之间的字符串"),
    range: jQuery.validator.format("请输入一个介于 {0} 和 {1} 之间的值"),
    max: jQuery.validator.format("请输入一个最大为{0} 的值"),
    min: jQuery.validator.format("请输入一个最小为{0} 的值")
});


// uuid验证
jQuery.validator.addMethod("isUuid", function(value, element) {
    var z = /^[a-zA-Z0-9]{3,15}$/;
    return this.optional(element) || (z.test(value));
}, "请输3到15位的数字或字母");

// 验证金额
jQuery.validator.addMethod("isMoney", function(value, element) {
    var z = /^-?\d+\.?\d{0,2}$/;
    return this.optional(element) || (z.test(value));
}, "请输正确的金额");

// topenid验证
jQuery.validator.addMethod("isTopenid", function(value, element) {
    var z = /^(gh_)[A-Za-z0-9]+$/;
    return this.optional(element) || (z.test(value));
}, "请输入合法的OPENID,例：gh_f2bcde25dffc");

// appid验证
jQuery.validator.addMethod("isAppid", function(value, element) {
    var z = /^(wx)[A-Za-z0-9]+$/;
    return this.optional(element) || (z.test(value));
}, "请输入合法的APPID,例：wxf541e89138ada561");

// appsecret验证
jQuery.validator.addMethod("isAppsecret", function(value, element) {
    var z = /^[a-zA-Z0-9]{25,35}$/;
    return this.optional(element) || (z.test(value));
}, "请输入合法的APPSECRET,例：cb81d38b190df2d0963f6ffc96c0915a");