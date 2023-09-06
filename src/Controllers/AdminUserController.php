<?php

namespace Slowlyo\OwlAdmin\Controllers;

use Slowlyo\OwlAdmin\Renderers\Page;
use Slowlyo\OwlAdmin\Renderers\Form;
use Slowlyo\OwlAdmin\Renderers\Operation;
use Slowlyo\OwlAdmin\Services\AdminUserService;
use Slowlyo\OwlAdmin\Services\AdminRoleService;

/**
 * @property AdminUserService $service
 */
class AdminUserController extends AdminController
{
    protected string $serviceName = AdminUserService::class;

    public function list(): Page
    {
        $model = $this->serviceName::make()->getModel();
        $crud = $this->baseCRUD()
            ->filterTogglable(true)
            ->filter($this->baseFilter()->body(
                amisMake()->TextControl('keyword', __('admin.keyword'))
                    ->size('lg')
                    ->placeholder(__('admin.admin_user.search_username'))
            ))
            ->columns([
                amisMake()->TableColumn('id', 'ID')->sortable(),
                amisMake()->TableColumn('avatar', __('admin.admin_user.avatar'))->type('avatar')->src('${avatar}'),
                amisMake()->TableColumn('username', __('admin.username'))->copyable(),
                amisMake()->TableColumn('mobile', __('admin.admin_user.mobile'))->copyable(),
                amisMake()->TableColumn('email', __('admin.admin_user.email'))->copyable(),
                amisMake()->TableColumn('name', __('admin.admin_user.name')),
                amisMake()->TableColumn('gender', __('admin.admin_user.gender'))->type('status')->source($model::toSource('genderOpt')),
                amisMake()->TableColumn('birthday', __('admin.admin_user.birthday'))->type('date'),
                amisMake()->TableColumn('roles', __('admin.admin_user.roles'))->type('each')->items(
                    amisMake()->Tag()->label('${name}')->className('my-1')
                ),
                amisMake()->TableColumn('state', __('admin.admin_user.state'))->type('status')->source($model::toSource('stateOpt')),
                amisMake()->TableColumn('created_at', __('admin.created_at'))->type('datetime')->sortable(true),
                amisMake()->TableColumn('updated_at', __('admin.updated_at'))->type('datetime'),
                Operation::make()->label(__('admin.actions'))->buttons([
                    $this->rowEditButton(true),
                    $this->rowDeleteButton()->visibleOn('${id != 1}'),
                ]),
            ]);

        return $this->baseList($crud);
    }

    public function form(): Form
    {
        $model = $this->serviceName::make()->getModel();
        return $this->baseForm()->body([
            amisMake()->ImageControl('avatar', __('admin.admin_user.avatar'))->receiver($this->uploadImagePath()),
            amisMake()->TextControl('username', __('admin.username'))->required(),
            amisMake()->TextControl('name', __('admin.admin_user.name'))->required(),
            amisMake()->TextControl('password', __('admin.password'))->type('input-password'),
            amisMake()->TextControl('confirm_password', __('admin.confirm_password'))->type('input-password'),
            amisMake()->SelectControl('roles', __('admin.admin_user.roles'))
                ->searchable()
                ->multiple()
                ->labelField('name')
                ->valueField('id')
                ->joinValues(false)
                ->extractValue()
                ->options(AdminRoleService::make()->query()->get(['id', 'name'])),
            amisMake()->TextControl('mobile', __('admin.admin_user.mobile'))->type('input-number')->validations('isPhoneNumber'),
            amisMake()->TextControl('email', __('admin.admin_user.email'))->type('input-email')->validations('isEmail'),
            amisMake()->DateControl('birthday', __('admin.admin_user.birthday'))->format("YYYY-MM-DD"),
            amisMake()->RadiosControl('gender', __('admin.admin_user.gender'))->options($model::$genderOpt)->inline(true)->value($model::$genderDef),
            amisMake()->RadiosControl('state', __('admin.admin_user.state'))->options($model::$stateOpt)->inline(true)->value($model::$stateDef)->required(),
        ]);
    }

    public function detail(): Form
    {
        return $this->baseDetail()->body([]);
    }
}
