<?php

namespace Slowlyo\OwlAdmin\Support\Cores;

use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class Database
{
    private string|null $moduleName;

    public function __construct($moduleName = null)
    {
        $this->moduleName = $moduleName;
    }

    public static function make($moduleName = null)
    {
        return new self($moduleName);
    }

    public function tableName($name)
    {
        return $this->moduleName . $name;
    }

    public function create($tableName, $callback)
    {
        Schema::create($this->tableName($tableName), $callback);
    }

    public function dropIfExists($tableName)
    {
        Schema::dropIfExists($this->tableName($tableName));
    }

    public function initSchema()
    {
        $this->down();
        $this->up();
    }

    public function up()
    {
        $this->create('admin_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 120)->unique()->comment('用户名');
            $table->string('password', 80)->nullable()->comment('密码');
            $table->string('mobile', 15)->index()->nullable()->comment('手机号');
            $table->string('name', 30)->nullable()->comment('姓名');
            $table->tinyInteger('gender')->index()->default(0)->comment('性别：0=未知,1=男,2=女');
            $table->timestamp('birthday')->nullable()->comment('出生日期');
            $table->string('email', 100)->nullable()->comment('电子邮箱');
            $table->string('avatar', 240)->nullable()->comment('用户头像');
            $table->string('remember_token', 100)->nullable()->comment('记住我');
            $table->tinyInteger('state')->index()->default(1)->comment('状态：0=已禁用,1=正常');
            $table->json('data')->nullable()->comment('虚拟列数据存储');
            $table->timestamps();
            $table->comment('管理员表');
        });

        $this->create('admin_roles', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->string('name', 50)->unique()->comment('角色名称');
            $table->string('slug', 50)->unique()->comment('角色标识');
            $table->timestamps();
            $table->comment('角色表');
        });

        $this->create('admin_permissions', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->string('name', 50)->unique()->comment('权限名称');
            $table->string('slug', 50)->unique()->comment('权限名称');
            $table->text('http_method')->nullable()->comment('请求方法');
            $table->text('http_path')->nullable()->comment('请求路径');
            $table->integer('order')->default(0)->comment('排序');
            $table->integer('parent_id')->default(0)->comment('父级ID');
            $table->timestamps();
            $table->comment('权限表');
        });

        $this->create('admin_menus', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->integer('parent_id')->default(0)->comment('父级ID');
            $table->integer('order')->default(0)->comment('排序');
            $table->string('title', 100)->comment('菜单名称');
            $table->string('icon', 100)->nullable()->comment('菜单图标');
            $table->string('url')->nullable()->comment('菜单路由');
            $table->tinyInteger('url_type')->default(1)->comment('路由类型(1:路由,2:外链)');
            $table->tinyInteger('visible')->default(1)->comment('是否可见');
            $table->tinyInteger('is_home')->default(0)->comment('是否为首页');
            $table->string('extension')->nullable()->comment('扩展');

            $table->timestamps();
            $table->comment('菜单表');
        });

        $this->create('admin_role_users', function (Blueprint $table) {
            $table->integer('role_id')->comment('角色ID');
            $table->integer('user_id')->comment('管理员ID');
            $table->index(['role_id', 'user_id']);
            $table->timestamps();
            $table->comment('管理员角色表');
        });

        $this->create('admin_role_permissions', function (Blueprint $table) {
            $table->integer('role_id')->comment('角色ID');
            $table->integer('permission_id')->comment('权限ID');
            $table->index(['role_id', 'permission_id']);
            $table->timestamps();
            $table->comment('角色权限表');
        });

        $this->create('admin_permission_menu', function (Blueprint $table) {
            $table->integer('permission_id')->comment('权限ID');
            $table->integer('menu_id')->comment('菜单ID');
            $table->index(['permission_id', 'menu_id']);
            $table->timestamps();
            $table->comment('权限菜单表');
        });

        // 如果是模块，跳过下面的表
        if ($this->moduleName) {
            return;
        }

        $this->create('admin_code_generators', function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('ID');
            $table->string('title')->default('')->comment('名称');
            $table->string('table_name')->default('')->comment('表名');
            $table->string('primary_key')->default('id')->comment('主键名');
            $table->string('model_name')->default('')->comment('模型名');
            $table->string('controller_name')->default('')->comment('控制器名');
            $table->string('service_name')->default('')->comment('服务名');
            $table->longText('columns')->comment('字段信息');
            $table->tinyInteger('need_timestamps')->default(0)->comment('是否需要时间戳');
            $table->tinyInteger('soft_delete')->default(0)->comment('是否需要软删除');
            $table->text('needs')->nullable()->comment('需要生成的代码');
            $table->text('menu_info')->nullable()->comment('菜单信息');
            $table->text('page_info')->nullable()->comment('页面信息');
            $table->timestamps();
            $table->comment('代码生成器表');
        });

        $this->create('admin_settings', function (Blueprint $table) {
            $table->string('key', 190)->unique()->comment('配置项');
            $table->longText('values')->comment('配置值');
            $table->timestamps();
            $table->comment('配置表');
        });

        $this->create('admin_extensions', function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('ID');
            $table->string('name', 100)->unique()->comment('扩展标识');
            $table->tinyInteger('is_enabled')->default(0)->comment('是否启用：0=禁用,1=启用');
            $table->timestamps();
            $table->comment('扩展表');
        });
    }

    public function down()
    {
        $this->dropIfExists('admin_users');
        $this->dropIfExists('admin_roles');
        $this->dropIfExists('admin_permissions');
        $this->dropIfExists('admin_menus');
        $this->dropIfExists('admin_role_users');
        $this->dropIfExists('admin_role_permissions');
        $this->dropIfExists('admin_permission_menu');

        // 如果是模块，跳过下面的表
        if ($this->moduleName) {
            return;
        }

        $this->dropIfExists('admin_code_generators');
        $this->dropIfExists('admin_settings');
        $this->dropIfExists('admin_extensions');
    }

    /**
     * 填充初始数据
     *
     * @return void
     */
    public function fillInitialData()
    {
        $data = function ($data) {
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    $data[$k] = "['" . implode("','", $v) . "']";
                }
            }
            return array_merge($data, ['created_at' => now(), 'updated_at' => now()]);
        };

        $adminUser       = DB::table($this->tableName('admin_users'));
        $adminMenu       = DB::table($this->tableName('admin_menus'));
        $adminPermission = DB::table($this->tableName('admin_permissions'));
        $adminRole       = DB::table($this->tableName('admin_roles'));

        // 创建初始用户
        $adminUser->truncate();
        $adminUser->insert($data([
            'username' => 'admin',
            'password' => bcrypt('admin'),
            'name'     => 'Administrator',
        ]));

        // 创建初始角色
        $adminRole->truncate();
        $adminRole->insert($data([
            'name' => 'Administrator',
            'slug' => 'administrator',
        ]));

        // 用户 - 角色绑定
        DB::table($this->tableName('admin_role_users'))->truncate();
        DB::table($this->tableName('admin_role_users'))->insert($data([
            'role_id' => 1,
            'user_id' => 1,
        ]));

        // 创建初始权限
        $adminPermission->truncate();
        $adminPermission->insert([
            $data(['name' => '首页', 'slug' => 'home', 'http_path' => ['/home*'], "parent_id" => 0]),
            $data(['name' => '系统', 'slug' => 'system', 'http_path' => '', "parent_id" => 0]),
            $data(['name' => '管理员', 'slug' => 'admin_users', 'http_path' => ["/admin_users*"], "parent_id" => 2]),
            $data(['name' => '角色', 'slug' => 'roles', 'http_path' => ["/roles*"], "parent_id" => 2]),
            $data(['name' => '权限', 'slug' => 'permissions', 'http_path' => ["/permissions*"], "parent_id" => 2]),
            $data(['name' => '菜单', 'slug' => 'menus', 'http_path' => ["/menus*"], "parent_id" => 2]),
            $data(['name' => '设置', 'slug' => 'settings', 'http_path' => ["/settings*"], "parent_id" => 2]),
        ]);

        // 角色 - 权限绑定
        DB::table($this->tableName('admin_role_permissions'))->truncate();
        $permissionIds = DB::table($this->tableName('admin_permissions'))->orderBy('id')->pluck('id');
        foreach ($permissionIds as $id) {
            DB::table($this->tableName('admin_role_permissions'))->insert($data([
                'role_id'       => 1,
                'permission_id' => $id,
            ]));
        }

        // 创建初始菜单
        $adminMenu->truncate();
        $adminMenu->insert([
            $data([
                'parent_id' => 0,
                'title'     => 'dashboard',
                'icon'      => 'mdi:chart-line',
                'url'       => '/dashboard',
                'is_home'   => 1,
            ]),
            $data([
                'parent_id' => 0,
                'title'     => 'admin_system',
                'icon'      => 'material-symbols:settings-outline',
                'url'       => '/system',
                'is_home'   => 0,
            ]),
            $data([
                'parent_id' => 2,
                'title'     => 'admin_users',
                'icon'      => 'ph:user-gear',
                'url'       => '/system/admin_users',
                'is_home'   => 0,
            ]),
            $data([
                'parent_id' => 2,
                'title'     => 'admin_roles',
                'icon'      => 'carbon:user-role',
                'url'       => '/system/admin_roles',
                'is_home'   => 0,
            ]),
            $data([
                'parent_id' => 2,
                'title'     => 'admin_permission',
                'icon'      => 'fluent-mdl2:permissions',
                'url'       => '/system/admin_permissions',
                'is_home'   => 0,
            ]),
            $data([
                'parent_id' => 2,
                'title'     => 'admin_menu',
                'icon'      => 'ant-design:menu-unfold-outlined',
                'url'       => '/system/admin_menus',
                'is_home'   => 0,
            ]),
            $data([
                'parent_id' => 2,
                'title'     => 'admin_setting',
                'icon'      => 'akar-icons:settings-horizontal',
                'url'       => '/system/settings',
                'is_home'   => 0,
            ]),
        ]);

        // 权限 - 菜单绑定
        DB::table($this->tableName('admin_permission_menu'))->truncate();
        $menus = $adminMenu->get();
        foreach ($menus as $menu) {
            $_list   = [];
            $_list[] = $data(['permission_id' => $menu->id, 'menu_id' => $menu->id]);

            if ($menu->parent_id != 0) {
                $_list[] = $data(['permission_id' => $menu->parent_id, 'menu_id' => $menu->id]);
            }

            DB::table($this->tableName('admin_permission_menu'))->insert($_list);
        }
    }
}
