
### 项目介绍

基于 `Laravel` 、 `amis` 开发的后台框架, 快速且灵活~

- 基于 amis 以 json 的方式在后端构建页面，减少前端开发工作量，提升开发效率。
- 在 amis 150多个组件都不满足的情况下, 可自行开发前端。
- 框架为前后端分离 (不用再因为框架而束手束脚~)。

<br>

### 内置功能

- 基础后台功能
    - 后台用户管理
    - 角色管理
    - 权限管理
    - 菜单管理
- **代码生成器**
    - 保存生成记录
    - 导入/导出生成记录
    - 可使用命令清除生成的内容
    - 无需更改代码即可生成完整功能
- `amis` 全组件封装 150+ , 无需前端开发即可完成复杂页面
- `laravel-modules` 多模块支持
- 图形化扩展管理

<br>

### 安装

> 👉 __注意: `UfuAdmin` 是 `laravel` 的扩展包, 安装前请确保你会使用 `laravel`__

##### 1. 创建 `laravel` 项目

```php
composer create-project laravel/laravel example-app
```

##### 2. 项目配置

配置数据库信息
```dotenv
# .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ufu_admin
DB_USERNAME=root
DB_PASSWORD=
```


app.php文件配置

```shell
    'timezone' => 'Asia/Shanghai', //时区


    'locale' => 'zh_CN', //语言
```


App\Providers\AppServiceProvider.php文件配置

```shell
    public function boot(): void
    {
        \Schema::defaultStringLength(191);
    }
```

##### 3. 获取 `Ufu Admin`

```shell
composer require ufucms/ufu-admin:"dev-master"
```

##### 4. 安装

```shell
# 先发布框架资源
php artisan admin:publish


# 执行安装 (可以在执行安装命令前在 config/admin.php 中修改部分配置)
php artisan admin:install
```

##### 5. 运行项目

启动服务, 访问 `/admin` 路由即可 <br>
_初始账号密码都是 `admin`_


##### 6. 其它

重新发布所有文件, 并强制覆盖原有文件

```shell
php artisan admin:publish --force

```

重新发布配置并覆盖

```shell
php artisan admin:publish --force --config

```

重新发布资源并覆盖

```shell
php artisan admin:publish --force --assets

```

重新发布语言包并覆盖

```shell
php artisan admin:publish --force --lang

```

Nginx伪静态配置
```shell
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

```
<br>
