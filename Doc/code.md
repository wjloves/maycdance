###错误代码 和 错误说明

    100	invalid_request_scheme 错误的请求协议
    101	invalid_request_method 错误的请求方法
    102	access_token_is_missing 未找到 access_token
    103	invalid_access_token access_token 不存在或已被用户删除，或者用户修改了密码
    104	invalid_apikey  client_id不存在或已删除
    105	invalid_oauth_code 授权码错误或者过期
    106	invalid_access_token access_token 不合法
    107 resource_not_exists  获取资源失败
    108	invalid_refresh_token refresh_token 不合法
    109	invalid_credencial2 apikey 未申请此权限
    111	rate_limit_exceeded1 用户访问速度限制
    112	rate_limit_exceeded2 IP 访问速度限制
    113	required_parameter_is_missing 缺少参数
    114	unsupported_grant_type 错误的 grant_type
    115	unsupported_response_type 错误的 response_type
    116	client_secret_mismatch client_secret不匹配
    117	redirect_uri_mismatch redirect_uri不匹配
    118	invalid_authorization_code authorization_code 不存在或已过期
    119	invalid_refresh_token refresh_token 不存在或已过期
    120	username_password_mismatch 用户名密码不匹配
    123	access_token_has_expired_since_password_changed 因用户修改密码而导致 access_token 过期
    124	access_token_has_not_expired access_token 未过期
    125	invalid_request_scope 访问的 scope 不合法，开发者不用太关注，一般不会出现该错误
    126	invalid_request_source 访问来源不合法
    127	thirdparty_login_auth_faied 第三方授权错误
    129 get_data_error  获取数据库中的数据出错
    130 Illegal string has input   用户名和密码以及email的字符检查不通过
    128	user_locked 用户被锁定
    999	unknown 未知错误

###HTTP状态码	说明

    200	表明 API 的请求正常
    400	表明 API 的请求出错，具体原因参考上面列出的错误码
