# 说明

文档更新于

### 接口的用户认证
在HTTP请求的Header中加入Authorization，值为Bearer TOKEN，其中TOKEN为登录或注册后返回的access_token

### 当前前台使用的token
在HTTP请求的Header中加入Authorization，
```
Bearer xxxx
```

### 参数类型的说明
- 当HTTP请求的方法是POST的时候，参数类型是form-data
- 当HTTP请求的方法是PATCH或PUT的时候，参数类型是x-www-form-urlencoded

### 错误返回的说明
失败返回的HTTP状态码是4xx和5xx，返回的内容如下：
```angular2html
{
    "message": "xx",    错误描述
    "code": "",         业务层的错误编码（不一定有）
    "errors": {         错误详情（不一定有）
        "lesson_id": [
            "lesson id 不能为空。"
        ]
    },
    "status_code: ""    对应HTTP的状态码
}
```

### 分页请求参数说明
分页数据是在返回的meta下的pagination中
```
perPage        每页记录数(默认15)
page           当前页(默认1)
```

### 分页说明
分页数据是在返回的meta下的pagination中
```
total           记录总数
count           当前页记录数
per_page        每页记录数
current_page    当前页
total_pages     总页数
link            分页链接
    previous    上一页的链接
    next        下一页的链接 
```

### 数据关联的说明
有些接口可以在url加参数include，这样可以把相关联的数据也显示出来，比如：
```angular2html
http://{{ip}}/api/me?include=role
{
    "id": 1,
    "name": "admin",
}

```
url中的include写法：

    include=user,diary          // 加入用户信息和日记信息
    include=user,diary.photos   // 加入用户信息和日记信息以及日记的图片